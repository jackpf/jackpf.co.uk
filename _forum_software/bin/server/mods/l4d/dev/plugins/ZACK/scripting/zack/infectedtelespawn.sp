/*
 * ============================================================================
 *
 *  File:			infectedtelespawn.sp
 *  Type:			Module
 *  Description:	Prevents infected from telespawning on to the survivors.
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

static	const	Float:	TELESPAWN_CLOSE_TO_SURVIVORS	= 50.0;
static	const	Float:	TELESPAWN_SPAWN_DELAY			= 0.1;

static	const	Float:	BLOCK_USE_TIME					= 1.0;
static			bool:	g_bBlockUse[MAXPLAYERS + 1] 	= {false};
static			Handle:	g_hBlockTimer[MAXPLAYERS + 1]	= {INVALID_HANDLE};

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
public _InfTelespawn_OnPluginStart()
{
	/* Don't enable module if game isn't Left 4 Dead */
	if (!IsGameLeft4Dead()) return;

	HookGlobalForward(FWD_ON_PLUGIN_ENABLED, _IT_OnPluginEnabled);
	HookGlobalForward(FWD_ON_PLUGIN_DISABLED, _IT_OnPluginDisabled);
}

/**
 * Called on plugin enabled.
 *
 * @noreturn
 */
public _IT_OnPluginEnabled()
{
	HookEvent("player_spawn", _IT_PlayerSpawn_Event);
	HookGlobalForward(FWD_ON_PLAYER_RUN_CMD, _IT_OnPlayerRunCmd);
}

/**
 * Called on plugin disabled.
 *
 * @noreturn
 */
public _IT_OnPluginDisabled()
{
	UnhookEvent("player_spawn", _IT_PlayerSpawn_Event);
	UnhookGlobalForward(FWD_ON_PLAYER_RUN_CMD, _IT_OnPlayerRunCmd);
}

/**
 * Called when a player is spawned.
 *
 * @param event			Handle to event.
 * @param name			String containing the name of the event.
 * @param dontBroadcast	True if event was not broadcast to clients, false otherwise.
 * @noreturn
 */
public _IT_PlayerSpawn_Event(Handle:event, const String:name[], bool:dontBroadcast)
{
	new client = GetClientOfUserId(GetEventInt(event, "userid"));

	if (client < CLIENT_INDEX_FIRST ||
		client > CLIENT_INDEX_LAST ||
		!g_bIsInGame[client] ||
		g_bIsFake[client] ||
		TeamIndex[client] != TEAM_INFECTED)
		return;

	g_bBlockUse[client] = true;
	if (g_hBlockTimer[client] != INVALID_HANDLE)
	{
		CloseHandle(g_hBlockTimer[client]);
	}
	g_hBlockTimer[client] = CreateTimer(BLOCK_USE_TIME, _IT_BlockUse_Timer, client);

	CreateTimer(TELESPAWN_SPAWN_DELAY, _IT_CheckForTeleSpawn_Timer, client);
}

/**
 * Called when check for tele spawn timer interval has elapsed.
 *
 * @param timer			Handle to the timer object.
 * @param client		Client index.
 * @return				Plugin_Stop.
 */
public Action:_IT_CheckForTeleSpawn_Timer(Handle:timer, any:client)
{
	if (!IsPlayerTeleSpawned(client)) return Plugin_Stop;
	TeleportEntity(client,
		Float:{0.0, 0.0, 0.0}, // Teleport to map center
		NULL_VECTOR, 
		NULL_VECTOR);
	ForcePlayerSuicide(client);
	LogPluginMessage("Forced %L to suicide at map center for telespawning.", client);
	return Plugin_Stop;
}

/**
 * Called when block use timer interval has elapsed.
 *
 * @param timer			Handle to the timer object.
 * @param client		Client index.
 * @return				Plugin_Stop.
 */
public Action:_IT_BlockUse_Timer(Handle:timer, any:client)
{
	g_bBlockUse[client] = false;
	g_hBlockTimer[client] = INVALID_HANDLE;
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
public Action:_IT_OnPlayerRunCmd(client, &buttons, &impulse, Float:vel[3], Float:angles[3], &weapon)
{
	if (g_bBlockUse[client] && buttons & IN_USE)
	{
		buttons ^= IN_USE;
	}
	return Plugin_Continue;
}

/*
 * ==================================================
 *                    Private API
 * ==================================================
 */

/**
 * Returns whether or not an infected player is too close of a survivor to be
 * considered as "telespawning".
 *
 * @param client		Client index.
 * @return				True if within range of a survivor, false otherwise.
 */
static IsPlayerTeleSpawned(client)
{
	if (client < CLIENT_INDEX_FIRST ||
		client > CLIENT_INDEX_LAST ||
		!g_bIsInGame[client] ||
		g_bIsFake[client] ||
		TeamIndex[client] != TEAM_INFECTED ||
		!IsPlayerAlive(client))
		return false;

	decl Float:origin[3], Float:surOrigin[3];
	GetClientAbsOrigin(client, origin);

	FOR_EACH_ALIVE_SURVIVOR(survivor)
	{
		GetClientAbsOrigin(survivor, surOrigin);
		if (GetVectorDistance(surOrigin, origin) <= TELESPAWN_CLOSE_TO_SURVIVORS) return true;
	}
	return false;
}