/*
 * ============================================================================
 *
 *  File:			blockpistolspam.sp
 *  Type:			Module
 *  Description:	Blocks a pistol exploit which can cause the server to lag.
 *					This exploit is only for L4D2.
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

static	const	String:	WEAPON_PISTOL_NETCLASS[]		= "CPistol";
static	const	Float:	BLOCK_USE_TIME					= 0.3;
static			bool:	g_bProhibitUse[MAXPLAYERS + 1]	= {false};

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
public _BlockPistolSpam_OnPluginStart()
{
	/* Don't enable module if game isn't Left 4 Dead 2 */
	if (!IsGameLeft4Dead2()) return;

	HookGlobalForward(FWD_ON_PLUGIN_ENABLED, _BPS_OnPluginEnabled);
	HookGlobalForward(FWD_ON_PLUGIN_DISABLED, _BPS_OnPluginDisabled);
}

/**
 * Called on plugin enabled.
 *
 * @noreturn
 */
public _BPS_OnPluginEnabled()
{
	HookGlobalForward(FWD_ON_PLAYER_RUN_CMD, _BPS_OnPlayerRunCmd);
	HookEvent("player_use", _BPS_OnPlayerUse_Event, EventHookMode_Post);
}

/**
 * Called on plugin disabled.
 *
 * @noreturn
 */
public _BPS_OnPluginDisabled()
{
	UnhookGlobalForward(FWD_ON_PLAYER_RUN_CMD, _BPS_OnPlayerRunCmd);
	UnhookEvent("player_use", _BPS_OnPlayerUse_Event, EventHookMode_Post);
}

/**
 * Called when player use event is fired.
 *
 * @param event			Handle to event.
 * @param name			String containing the name of the event.
 * @param dontBroadcast	True if event was not broadcast to clients, false 
 *						otherwise.
 * @noreturn
 */
public _BPS_OnPlayerUse_Event(Handle:event, const String:name[], bool:dontBroadcast)
{
	new targetEntity = GetEventInt(event, "targetid");
	if (targetEntity < 0 || targetEntity > MAXENTITIES || !IsValidEntity(targetEntity)) return;

	decl String:buffer[32];
	GetEntityNetClass(targetEntity, buffer, 32);
	if (!StrEqual(buffer, WEAPON_PISTOL_NETCLASS)) return;

	new client = GetClientOfUserId(GetEventInt(event,"userid"));
	if (client < CLIENT_INDEX_FIRST || client > CLIENT_INDEX_LAST || !g_bIsInGame[client] || g_bIsFake[client]) return;

	g_bProhibitUse[client] = true;
	CreateTimer(BLOCK_USE_TIME, _BPS_BlockUse_Timer, client);
}

/**
 * Called when a clients movement buttons are being processed.
 *
 * @param client		Index of the client.
 * @param buttons		Copyback buffer containing the current commands (as 
 *						bitflags - see entity_prop_stocks.inc).
 * @param impulse		Copyback buffer containing the current impulse command.
 * @param vel			Players desired velocity.
 * @param angles		Players desired view angles.
 * @param weapon		Entity index of the new weapon if player switches 
 *						weapon, 0 otherwise.
 * @return				Plugin_Handled to block the commands from being 
 *						processed, Plugin_Continue otherwise.
 */
public Action:_BPS_OnPlayerRunCmd(client, &buttons, &impulse, Float:vel[3], Float:angles[3], &weapon)
{
	if (g_bProhibitUse[client] && buttons & IN_USE)
	{
		buttons ^= IN_USE;
	}
	return Plugin_Continue;
}

/**
 * Called when block use timer interval has elapsed.
 *
 * @param timer			Handle to the timer object.
 * @param client		Client index.
 * @return				Plugin_Stop.
 */
public Action:_BPS_BlockUse_Timer(Handle:timer, any:client)
{
	g_bProhibitUse[client] = false;
	return Plugin_Stop;
}