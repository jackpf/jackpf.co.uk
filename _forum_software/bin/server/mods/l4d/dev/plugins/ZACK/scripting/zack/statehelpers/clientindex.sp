/*
 * ============================================================================
 *
 *  File:			clientindex.sp
 *  Type:			State helper
 *  Description:	Keep tracks of teammembers on each team.
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
 *       Public
 * --------------------
 */

/* Team specific indexes.
 * Stores the current count of team and the client on team. */
new						SurvivorCount					= 0;
new						SurvivorIndex[MAXPLAYERS + 1]	= {-1};
new						InfectedCount					= 0;
new						InfectedIndex[MAXPLAYERS + 1]	= {-1};
new						SpectateCount					= 0;
new						SpectateIndex[MAXPLAYERS + 1] 	= {-1};

new						TeamIndex[MAXPLAYERS + 1]		= {0}; // Team index uses client as index and store the client's current team

/* Human index which hold all teams */
new						HumanCount						= 0;
new						HumanIndex[MAXPLAYERS + 1]		= {-1};

/*
 * --------------------
 *       Private
 * --------------------
 */

static	const	Float:	REBUILD_DELAY				= 0.3; // After a team change, use this delay to make sure the index updates corretly

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
public _ClientIndex_OnPluginStart()
{
	HookGlobalForward(FWD_ON_ALL_PLUGINS_LOADED, _CI_OnAllPluginsLoaded);
	HookGlobalForward(FWD_ON_MAP_START, _CI_OnMapStart);
	HookGlobalForward(FWD_ON_MAP_END, _CI_OnMapEnd);

	HookEvent("round_start", 		_CI_Event_TempStop_Event, 	EventHookMode_PostNoCopy);
	HookEvent("round_end", 			_CI_Event_TempStop_Event, 	EventHookMode_PostNoCopy);
	HookEvent("player_team", 		_CI_Event_TempStop_Event, 	EventHookMode_PostNoCopy);
	HookEvent("player_spawn", 		_CI_Event, 					EventHookMode_PostNoCopy);
	HookEvent("player_disconnect", 	_CI_Event, 					EventHookMode_PostNoCopy);
	HookEvent("player_death", 		_CI_Event, 					EventHookMode_PostNoCopy);
	HookEvent("player_bot_replace", _CI_Event, 					EventHookMode_PostNoCopy);
	HookEvent("bot_player_replace", _CI_Event, 					EventHookMode_PostNoCopy);

	if (IsGameLeft4Dead2())
	{
		HookEvent("defibrillator_used", _CI_Event, 					EventHookMode_PostNoCopy);
	}
}

/**
 * Called on all plugins loaded.
 *
 * @noreturn
 */
public _CI_OnAllPluginsLoaded()
{
	RebuildIndex();
}

/**
 * Called on map start.
 *
 * @noreturn
 */
public _CI_OnMapStart()
{
	ResetCounts();
}

/**
 * Called on map end.
 *
 * @noreturn
 */
public _CI_OnMapEnd()
{
	ResetCounts();
}

/**
 * Called when a event that invalidates the index, is fired.
 *
 * @param event			INVALID_HANDLE due to EventHookMode_PostNoCopy.
 * @param name			String containing the name of the event.
 * @param dontBroadcast	True if event was not broadcast to clients, false otherwise.
 * @noreturn
 */
public _CI_Event_TempStop_Event(Handle:event, const String:name[], bool:dontBroadcast)
{
	ResetCounts();
	CreateTimer(REBUILD_DELAY, _CI_RebuildIndex_Timer);
}

/**
 * Called when a event that invalidates the index, is fired.
 *
 * @param event			INVALID_HANDLE due to EventHookMode_PostNoCopy.
 * @param name			String containing the name of the event.
 * @param dontBroadcast	True if event was not broadcast to clients, false otherwise.
 * @noreturn
 */
public _CI_Event(Handle:event, const String:name[], bool:dontBroadcast)
{
	RebuildIndex();
}

/**
 * Called when rebuild index interval has elapsed.
 * 
 * @param timer			Handle to the timer object.
 * @return				Plugin_Stop.
 */
public Action:_CI_RebuildIndex_Timer(Handle:timer)
{
	RebuildIndex();
	return Plugin_Stop;
}

/*
 * ==================================================
 *                    Private API
 * ==================================================
 */

/**
 * Rebuilds client indexes.
 * 
 * @noreturn
 */
static RebuildIndex()
{
	ResetCounts();
	if (!IsServerProcessing()) return;

	decl team;
	FOR_EACH_CLIENT_IN_GAME(client)
	{
		if (!g_bIsFake[client])
		{
			HumanIndex[HumanCount] = client;
			HumanCount++;
		}

		team = GetClientTeam(client);
		switch (team)
		{
			case TEAM_SPECTATOR:
			{
				SpectateIndex[SpectateCount] = client;
				SpectateCount++;
				TeamIndex[client] = TEAM_SPECTATOR;
			}
			case TEAM_SURVIVOR:
			{
				if (!IsPlayerAlive(client)) continue;
				SurvivorIndex[SurvivorCount] = client;
				SurvivorCount++;
				TeamIndex[client] = TEAM_SURVIVOR;
			}
			case TEAM_INFECTED:
			{
				InfectedIndex[InfectedCount] = client;
				InfectedCount++;
				TeamIndex[client] = TEAM_INFECTED;
			}
		}
	}
}

/**
 * Reset all player counters.
 * 
 * @noreturn
 */
static ResetCounts()
{
	SurvivorCount = 0;
	InfectedCount = 0;
	SpectateCount = 0;
	HumanCount = 0;
}