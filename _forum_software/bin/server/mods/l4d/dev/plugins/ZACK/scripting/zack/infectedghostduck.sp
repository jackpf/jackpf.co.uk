/*
 * ============================================================================
 *
 *  File:			infectedghostduck.sp
 *  Type:			Module
 *  Description:	Fixes an exploit where infected ghost can spawn in courch
 *					position and still retain full speed when moving, or
 *					hunters being able to do a "silent pounce".
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

static	const	String:	CLASSNAME_TERRORPLAYER[] 				= "CTerrorPlayer";
static	const	String:	NETPROP_DUCKED[]						= "m_bDucked";
static	const	String:	NETPROP_DUCKING[]						= "m_bDucking";
static	const	String:	NETPROP_FALLVELOCITY[]					= "m_flFallVelocity";

static					g_iOffsetDucked							= -1;
static					g_iOffsetDucking						= -1;
static					g_iOffsetFallVelocity					= -1;

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
public _InfGhostDuck_OnPluginStart()
{
	/* Don't enable module if game isn't Left 4 Dead */
	if (!IsGameLeft4Dead()) return;

	g_iOffsetDucked = FindSendPropInfo(CLASSNAME_TERRORPLAYER, NETPROP_DUCKED);
	if (g_iOffsetDucked < 1) SetFailState("Unable to find ducked offset!");

	g_iOffsetDucking = FindSendPropInfo(CLASSNAME_TERRORPLAYER, NETPROP_DUCKING);
	if (g_iOffsetDucking < 1) SetFailState("Unable to find ducking offset!");

	g_iOffsetFallVelocity = FindSendPropInfo(CLASSNAME_TERRORPLAYER, NETPROP_FALLVELOCITY);
	if (g_iOffsetFallVelocity < 1) SetFailState("Unable to find fall velocity offset!");

	HookGlobalForward(FWD_ON_PLUGIN_ENABLED, _IGD_OnPluginEnable);
	HookGlobalForward(FWD_ON_PLUGIN_DISABLED, _IGD_OnPluginDisable);
}

/**
 * Called on all plugins loaded.
 *
 * @noreturn
 */
public _IGD_OnPluginEnable()
{
	HookEvent("player_spawn", _IGD_PlayerSpawn_Event);
}

/**
 * Plugin is now disabled.
 *
 * @noreturn
 */
public _IGD_OnPluginDisable()
{
	UnhookEvent("player_spawn", _IGD_PlayerSpawn_Event);
}

/**
 * Called when a player is spawned.
 *
 * @param event			Handle to event.
 * @param name			String containing the name of the event.
 * @param dontBroadcast	True if event was not broadcast to clients, false otherwise.
 * @noreturn
 */
public _IGD_PlayerSpawn_Event(Handle:event, const String:name[], bool:dontBroadcast)
{
	new client = GetClientOfUserId(GetEventInt(event, "userid"));

	if (IsPlayerGhostDucked(client))
	{
		ClientCommand(client, "+duck");
		CreateTimer(0.1, _IGD_UnduckPlayer_Timer, client);
		LogPluginMessage("Forced %L to unduck for being ghost ducked.", client);
	}
}

/**
 * Called when unduck player interval has elapsed.
 * 
 * @param timer			Handle to the timer object.
 * @param client		Client index.
 * @return				Plugin_Stop.
 */
public Action:_IGD_UnduckPlayer_Timer(Handle:timer, any:client)
{
	ClientCommand(client, "-duck");
	return Plugin_Stop;
}

/*
 * ==================================================
 *                    Private API
 * ==================================================
 */

/**
 * Returns whether or not player is ghost ducking.
 *
 * @param client		Client index.
 * @return				True if ghost ducking, false otherwise.
 */
static bool:IsPlayerGhostDucked(client)
{
	if (bool:GetEntData(client, g_iOffsetDucked, 1) &&
		!bool:GetEntData(client, g_iOffsetDucking, 1) &&
		!(GetClientButtons(client) & IN_DUCK) &&
		GetEntDataFloat(client, g_iOffsetFallVelocity) == 0.0)
		return true;
	return false;
}