/*
 * ============================================================================
 *
 *  File:			infectedscratch.sp
 *  Type:			Module
 *  Description:	Blocks scratching while in stumble animation for the 
 *					infected.
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

static	const	String:	MAX_STAGGER_DURATION_CVAR[]				= "z_max_stagger_duration";
static			Float:	g_fStaggerDuration						= 0.9; // Default value
static			bool:	g_bProhibitMelee[MAXPLAYERS+1]			= {false};
static			Handle:	g_hProhibitMelee_Timer[MAXPLAYERS+1]	= {INVALID_HANDLE};

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
public _InfScratch_OnPluginStart()
{
	HookGlobalForward(FWD_ON_PLUGIN_ENABLED, _IS_OnPluginEnable);
	HookGlobalForward(FWD_ON_PLUGIN_DISABLED, _IS_OnPluginDisable);
}

/**
 * Called on plugin enabled.
 *
 * @noreturn
 */
public _IS_OnPluginEnable()
{
	HookEvent("player_shoved", _IS_PlayerShoved_Event);
	HookGlobalForward(FWD_ON_PLAYER_RUN_CMD, _IS_OnPlayerRunCmd);

	g_fStaggerDuration = GetConVarFloat(FindConVar(MAX_STAGGER_DURATION_CVAR));
	HookConVarChange(FindConVar(MAX_STAGGER_DURATION_CVAR), _IS_Stagger_CvarChange);
}

/**
 * Called on plugin disabled.
 *
 * @noreturn
 */
public _IS_OnPluginDisable()
{
	UnhookEvent("player_shoved", _IS_PlayerShoved_Event);
	UnhookGlobalForward(FWD_ON_PLAYER_RUN_CMD, _IS_OnPlayerRunCmd);

	UnhookConVarChange(FindConVar(MAX_STAGGER_DURATION_CVAR), _IS_Stagger_CvarChange);
}

/**
 * Called when stagger cvar changed.
 *
 * @param convar		Handle to the convar that was changed.
 * @param oldValue		String containing the value of the convar before it was changed.
 * @param newValue		String containing the new value of the convar.
 * @noreturn
 */
public _IS_Stagger_CvarChange(Handle:convar, const String:oldValue[], const String:newValue[])
{
	g_fStaggerDuration = GetConVarFloat(FindConVar(MAX_STAGGER_DURATION_CVAR));
}

/**
 * Called when a player is shoved.
 *
 * @param event			Handle to event.
 * @param name			String containing the name of the event.
 * @param dontBroadcast	True if event was not broadcast to clients, false otherwise.
 * @noreturn
 */
public _IS_PlayerShoved_Event(Handle:event, const String:name[], bool:dontBroadcast)
{
	new client = GetClientOfUserId(GetEventInt(event, "userid"));
	if (!client || GetClientTeam(client) != TEAM_INFECTED) return;

	if (!g_bProhibitMelee[client])
	{
		g_bProhibitMelee[client] = true;
	}
	else
	{
		CloseHandle(g_hProhibitMelee_Timer[client]);
	}
	g_hProhibitMelee_Timer[client] = CreateTimer(g_fStaggerDuration, _IS_PlayerShoved_Timer, client);
}

/**
 * Called when the player shoved timer interval has elapsed.
 *
 * @param timer			Handle to the timer object.
 * @param client		Client index of shoved player.
 * @return				Plugin_Stop.
 */
public Action:_IS_PlayerShoved_Timer(Handle:timer, any:client)
{
	g_bProhibitMelee[client] = false;
	return Plugin_Stop;
}

/**
 * Called when a clients movement buttons are being processed.
 *
 * @param client		Index of the client.
 * @param buttons		Copyback buffer containing the current commands (as bitflags - see entity_prop_stocks.inc).
 * @param impulse		Copyback buffer containing the current impulse command.
 * @param vel			Players desired velocity.
 * @param angles		Players desired view angles.
 * @param weapon		Entity index of the new weapon if player switches weapon, 0 otherwise.
 * @return				Plugin_Handled to block the commands from being processed, Plugin_Continue otherwise.
 */
public Action:_IS_OnPlayerRunCmd(client, &buttons, &impulse, Float:vel[3], Float:angles[3], &weapon)
{
	if (g_bProhibitMelee[client] && buttons & IN_ATTACK2) // if melee'ing while being prohibit
	{
		buttons ^= IN_ATTACK2; // remove attack 2 from pressed buttons
	}
	return Plugin_Continue;
}