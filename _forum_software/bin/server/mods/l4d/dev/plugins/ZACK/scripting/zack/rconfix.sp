/*
 * ============================================================================
 *
 *  File:			rconfix.sp
 *  Type:			Module
 *  Description:	Prevents server crash by clients submitting too many
 *					invalid rcon passwords.
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

static			Handle:	g_hEnable_Cvar = INVALID_HANDLE;
static			bool:	g_bEnable = true;

static			bool:	g_bIsRconFixed = false;

static					g_iMinFailTime = 0;
static					g_iMinFails = 0;
static					g_iMaxFails = 0;

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
public _RconFix_OnPluginStart()
{
	decl String:buffer[10];
	IntToString(int:g_bEnable, buffer, sizeof(buffer));
	g_hEnable_Cvar = CreateConVarEx("rconfix", buffer, "Sets whether RCON fix is enabled", FCVAR_PLUGIN);
	if (g_hEnable_Cvar == INVALID_HANDLE) SetFailState("Unable to create rcon fix cvar!");

	HookGlobalForward(FWD_ON_PLUGIN_ENABLED, _RF_OnPluginEnabled);
	HookGlobalForward(FWD_ON_PLUGIN_DISABLED, _RF_OnPluginDisabled);
}

/**
 * Called on plugin end.
 *
 * @noreturn
 */
public _RconFix_OnPluginEnd()
{
	SetRconFix(false);
}

/**
 * Called on plugin enabled.
 *
 * @noreturn
 */
public _RF_OnPluginEnabled()
{
	g_bEnable = GetConVarBool(g_hEnable_Cvar);
	HookConVarChange(g_hEnable_Cvar, _RF_Enable_CvarChange);
	SetRconFix(g_bEnable);
}

/**
 * Called on plugin disabled.
 *
 * @noreturn
 */
public _RF_OnPluginDisabled()
{
	UnhookConVarChange(g_hEnable_Cvar, _RF_Enable_CvarChange);
}

/**
 * Called on enable cvar changed.
 *
 * @param convar		Handle to the convar that was changed.
 * @param oldValue		String containing the value of the convar before it was changed.
 * @param newValue		String containing the new value of the convar.
 * @noreturn
 */
public _RF_Enable_CvarChange(Handle:convar, const String:oldValue[], const String:newValue[])
{
	g_bEnable = GetConVarBool(convar);
	SetRconFix(g_bEnable);
}

/*
 * ==================================================
 *                    Private API
 * ==================================================
 */

/**
 * Fixes rcon cvars to prevent crash.
 *
 * @param enabled		Sets whether the fix is enabled
 * @noreturn
 */
static SetRconFix(bool:enabled)
{
	if (enabled == g_bIsRconFixed) return; // No change in state
	g_bIsRconFixed = enabled;

	new Handle:convar;
	if (enabled)
	{
		convar = FindConVar("sv_rcon_minfailuretime");
		if (convar != INVALID_HANDLE)
		{
			g_iMinFailTime = GetConVarInt(convar); // Save current value
			SetConVarBounds(convar, ConVarBound_Upper, true, 1.0);
			SetConVarInt(convar, 1);
		}
		convar = FindConVar("sv_rcon_minfailures");
		if (convar != INVALID_HANDLE)
		{
			g_iMinFails = GetConVarInt(convar); // Save current value
			SetConVarBounds(convar, ConVarBound_Upper, true, 9999999.0);
			SetConVarBounds(convar, ConVarBound_Lower, true, 9999999.0);
			SetConVarInt(convar, 9999999);
		}
		convar = FindConVar("sv_rcon_maxfailures");
		if (convar != INVALID_HANDLE)
		{
			g_iMaxFails = GetConVarInt(convar); // Save current value
			SetConVarBounds(convar, ConVarBound_Upper, true, 9999999.0);
			SetConVarBounds(convar, ConVarBound_Lower, true, 9999999.0);
			SetConVarInt(convar, 9999999);
		}
		LogPluginMessage("RconFix enabled.");
	}
	else
	{
		convar = FindConVar("sv_rcon_minfailuretime");
		if (convar != INVALID_HANDLE)
		{
			SetConVarBounds(convar, ConVarBound_Upper, false);
			SetConVarInt(convar, g_iMinFailTime); // Restore old value
		}
		convar = FindConVar("sv_rcon_minfailures");
		if (convar != INVALID_HANDLE)
		{
			SetConVarBounds(convar, ConVarBound_Upper, true, 20.0);
			SetConVarBounds(convar, ConVarBound_Lower, true, 1.0);
			SetConVarInt(convar, g_iMinFails); // Restore old value
		}
		convar = FindConVar("sv_rcon_maxfailures");
		if (convar != INVALID_HANDLE)
		{
			SetConVarBounds(convar, ConVarBound_Upper, true, 20.0);
			SetConVarBounds(convar, ConVarBound_Lower, true, 1.0);
			SetConVarInt(convar, g_iMaxFails); // Restore old value
		}
		LogPluginMessage("RconFix disabled.");
	}
}