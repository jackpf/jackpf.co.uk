/*
 * ============================================================================
 *
 *  File:			ratehack.sp
 *  Type:			Module
 *  Description:	Kick clients who uses incorrect interp values to perform
 *					"rate hacking".
 *
 *  Copyright (C) 2010  Mr. Zero <mrzerodk@gmail.com>
 *  This file is part of ZACK.
 *
 *  ZACK is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  ZACK is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with ZACK.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ============================================================================
 */

/*
 * ==================================================
 *                     Variables
 * ==================================================
 */

/*
 * --------------------
 *       Private
 * --------------------
 */

static	const	String:	INTERP_CVAR[]			= "cl_interp";
static	const	Float:	INTERP_CVAR_MAX			= 0.5;
static	const	Float:	INTERP_CVAR_MIN			= 0.0;
static	const	Float:	CHECK_INTERVAL_MAX		= 1.5; // Using random intervals to make sure clients can't script against it, such as revert interp every 1 sec
static	const	Float:	CHECK_INTERVAL_MIN		= 0.3;
static	const	Float:	CHECK_INTERVAL_IDLE		= 15.0; // If no clients on the server, idle for this amount of time

static			Float:	g_fMaxInterp			= 0.5;
static			Float:	g_fMinInterp			= 0.0;
static			Handle:	g_hMaxInterp_Cvar		= INVALID_HANDLE;
static			Handle:	g_hMinInterp_Cvar		= INVALID_HANDLE;

static			bool:	g_bIsTimerActive		= false;

/*
 * ==================================================
 *                     Forwards
 * ==================================================
 */

/**
 * Called on plugin start.
 *
 * @noreturn
 */
public _RateHack_OnPluginStart()
{
	decl String:buffer[10];
	FloatToString(g_fMinInterp, buffer, sizeof(buffer));
	g_hMinInterp_Cvar = CreateConVar("sv_mininterp", buffer, 
		"Min client interp allowed on server",
		FCVAR_PLUGIN | FCVAR_NOTIFY, true, INTERP_CVAR_MIN, true, INTERP_CVAR_MAX);
	if (g_hMinInterp_Cvar == INVALID_HANDLE) SetFailState("Unable to create min interp cvar!");

	FloatToString(g_fMaxInterp, buffer, sizeof(buffer));
	g_hMaxInterp_Cvar = CreateConVar("sv_maxinterp", buffer, 
		"Max client interp allowed on server.", 
		FCVAR_PLUGIN | FCVAR_NOTIFY, true, INTERP_CVAR_MIN, true, INTERP_CVAR_MAX);
	if (g_hMaxInterp_Cvar == INVALID_HANDLE) SetFailState("Unable to create max interp cvar!");

	HookGlobalForward(FWD_ON_PLUGIN_ENABLED, _RH_OnPluginEnabled);
	HookGlobalForward(FWD_ON_PLUGIN_DISABLED, _RH_OnPluginDisabled);
}

/**
 * Called on plugin enabled.
 *
 * @noreturn
 */
public _RH_OnPluginEnabled()
{
	UpdateInterpValues();
	HookConVarChange(g_hMinInterp_Cvar, _RH_InterpValues_CvarChange);
	HookConVarChange(g_hMaxInterp_Cvar, _RH_InterpValues_CvarChange);

	if (!g_bIsTimerActive)
	{
		g_bIsTimerActive = true;
		CreateTimer(GetRandomFloat(CHECK_INTERVAL_MIN, CHECK_INTERVAL_MAX), _RH_CheckPlayers_Timer);
	}
}

/**
 * Called on plugin disabled.
 *
 * @noreturn
 */
public _RH_OnPluginDisabled()
{
	UnhookConVarChange(g_hMinInterp_Cvar, _RH_InterpValues_CvarChange);
	UnhookConVarChange(g_hMaxInterp_Cvar, _RH_InterpValues_CvarChange);
}

/**
 * Called when interp values is changed.
 *
 * @param convar		Handle to the convar that was changed.
 * @param oldValue		String containing the value of the convar before it was changed.
 * @param newValue		String containing the new value of the convar.
 * @noreturn
 */
public _RH_InterpValues_CvarChange(Handle:convar, const String:oldValue[], const String:newValue[])
{
	UpdateInterpValues();
}

/**
 * Called when check players interval has elapsed.
 * 
 * @param timer			Handle to the timer object.
 * @return				Plugin_Stop.
 */
public Action:_RH_CheckPlayers_Timer(Handle:timer)
{
	if (!IsPluginEnabled()) 
	{
		g_bIsTimerActive = false;
		return Plugin_Stop;
	}

	if (!IsServerProcessing())
	{
		CreateTimer(CHECK_INTERVAL_IDLE, _RH_CheckPlayers_Timer);
		return Plugin_Stop;
	}

	new client;
	for (new i = 0; i < SurvivorCount; i++)
	{
		client = SurvivorIndex[i];
		if (g_bIsFake[client] || IsClientInKickQueue(client)) continue;
		QueryClientConVar(client, INTERP_CVAR, _RH_CheckCvar);
	}

	for (new i = 0; i < InfectedCount; i++)
	{
		client = InfectedIndex[i];
		if (g_bIsFake[client] || IsClientInKickQueue(client)) continue;
		QueryClientConVar(client, INTERP_CVAR, _RH_CheckCvar);
	}

	CreateTimer(GetRandomFloat(CHECK_INTERVAL_MIN, CHECK_INTERVAL_MAX), _RH_CheckPlayers_Timer);
	return Plugin_Stop;
}

/**
 * Called when query to retrieve a client's interp cvar has finished.
 *
 * @param cookie		Unique identifier of query.
 * @param client		Player index.
 * @param result		Result of query that tells one whether or not query was successful.
 *							See ConVarQueryResult enum for more details.
 * @param convarName	Name of client convar that was queried.
 * @param convarValue	Value of client convar that was queried if successful. This will be "" if it was not.
 * @noreturn
 */
public _RH_CheckCvar(QueryCookie:cookie, client, ConVarQueryResult:result, const String:cvarName[], const String:cvarValue[])
{
	if (client < CLIENT_INDEX_FIRST || !g_bIsInGame[client] || g_bIsFake[client] || IsClientInKickQueue(client)) return;

	if (result != ConVarQuery_Okay)
	{
		/* If the cvar was somehow not found, not valid or protected, kick client anyway.
		 * They might try to prevent the plugin to look at the cvar. For any normal reasons the cvar should not be unreadable. */
		SetBadReason(client, INTERP_CVAR);
		KickBadClient(client, CvarFailedToReply);
		return;
	}

	new Float:value = StringToFloat(cvarValue);
	if (value <= g_fMaxInterp && value >= g_fMinInterp)
	{
		return; // Interp value is alright, return
	}

	/* Bad interp value */
	decl String:buffer[128];
	FloatToString(value, buffer, sizeof(buffer));
	SetBadReason(client, buffer);
	FloatToString(g_fMaxInterp, buffer, sizeof(buffer));
	SetBadReason(client, buffer);
	FloatToString(g_fMinInterp, buffer, sizeof(buffer));
	SetBadReason(client, buffer);
	KickBadClient(client, ClientHaveIncorrectInterp);
}

/*
 * ==================================================
 *                     Public API
 * ==================================================
 */

/**
 * Returns minimum interp client value.
 *
 * @return				Minimum interp client value allowed.
 */
stock Float:GetMinInterpAllowed()
{
	return g_fMinInterp;
}

/**
 * Returns maximum interp client value.
 *
 * @return				Maximum interp client value allowed.
 */
stock Float:GetMaxInterpAllowed()
{
	return g_fMaxInterp;
}

/*
 * ==================================================
 *                    Private API
 * ==================================================
 */

/**
 * Updates the global interp values with the cvars.
 *
 * @noreturn
 */
static UpdateInterpValues()
{
	g_fMinInterp = GetConVarFloat(g_hMinInterp_Cvar);
	g_fMaxInterp = GetConVarFloat(g_hMaxInterp_Cvar);
}