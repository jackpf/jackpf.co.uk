/*
 * ============================================================================
 *
 *  File:			speccrashing.sp
 *  Type:			Module
 *  Description:	Blocks spectators from crashing the server by going 
 *					"outside" the void.
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
 *                    Preprocessor
 * ==================================================
 */

/* Source maps are at max +/-16384 units in each direction. Going over this
 * limit as a player or spectator lags the server. To prevent lag or 
 * somehow getting a slow respond from where abouts from the client, the
 * limit is lowered by a ~500 units. */
#define MAP_MAX_X 15750.0
#define MAP_MIN_X -15750.0
#define MAP_MAX_Y 15750.0
#define MAP_MIN_Y -15750.0
#define MAP_MAX_Z 15750.0
#define MAP_MIN_Z -15750.0

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

static	const			OBSERVE_MODE_3RD_PERSON	= 5;

static					g_iLastIndex			= 0;
static	const			MAX_CLIENTS_PER_FRAME	= 3; // How many clients to process max per frame

static			Handle:	g_hEnable_Cvar 			= INVALID_HANDLE;
static			bool:	g_bEnabled				= true;

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
public _SpecCrashing_OnPluginStart()
{
	decl String:buffer[32];
	IntToString(_:g_bEnabled, buffer, sizeof(buffer));
	g_hEnable_Cvar = CreateConVarEx("speccrash_enable", buffer, "Sets whether spec crashing module is enabled", FCVAR_PLUGIN);
	if (g_hEnable_Cvar == INVALID_HANDLE) SetFailState("Unable to create spec crashing cvar!");
	g_bEnabled = false; // Used later for retriving current state of module, therefore we set it to false here

	HookGlobalForward(FWD_ON_PLUGIN_ENABLED, _SC_OnPluginEnabled);
	HookGlobalForward(FWD_ON_PLUGIN_DISABLED, _SC_OnPluginDisabled);
}

/**
 * Called on plugin enabled.
 *
 * @noreturn
 */
public _SC_OnPluginEnabled()
{
	SetSpecCrashModuleStatus(GetConVarBool(g_hEnable_Cvar));
	HookConVarChange(g_hEnable_Cvar, _SC_Enable_ConVarChange);
}

/**
 * Called on plugin disabled.
 *
 * @noreturn
 */
public _SC_OnPluginDisabled()
{
	UnhookConVarChange(g_hEnable_Cvar, _SC_Enable_ConVarChange);
	SetSpecCrashModuleStatus(false);
}

/**
 * Called on enable cvar changed.
 *
 * @param convar		Handle to the convar that was changed.
 * @param oldValue		String containing the value of the convar before it was
 *						changed.
 * @param newValue		String containing the new value of the convar.
 * @noreturn
 */
public _SC_Enable_ConVarChange(Handle:convar, const String:oldValue[], const String:newValue[])
{
	SetSpecCrashModuleStatus(bool:StringToInt(newValue));
}

/**
 * Called on game frame.
 *
 * @noreturn
 */
public _SC_OnGameFrame()
{
	if (!IsServerProcessing() || HumanCount == 0) return;

	/* We only process a few clients on each frame to spread out the workload
	 * to prevent noticeable lag when dealing with servers over 8 clients. */
	new processedClients = 0;
	new bool:haveLooped = true;

	decl client;
	for (new index = g_iLastIndex; index < HumanCount; index++)
	{
		client = HumanIndex[index];
		if (!g_bIsInGame[client]) continue; // Client no longer in game

		if (IsClientOutOfBounds(client)) // If clients is out of bounds
		{
			TeleportClientAndLog(client); // Teleport and log client
		}

		processedClients++;
		if (processedClients >= MAX_CLIENTS_PER_FRAME)
		{
			g_iLastIndex = index + 1;
			haveLooped = false;
			break;
		}
	}

	if (haveLooped)
	{
		g_iLastIndex = 0;
	}
}

// **********************************************
//                 Private API
// **********************************************

/**
 * Sets spec crash module status.
 *
 * @param enabled		Whether spec crash module is enabled.
 * @noreturn
 */
static SetSpecCrashModuleStatus(bool:enabled)
{
	if (enabled == g_bEnabled) return; // No change in status
	g_bEnabled = enabled;

	if (enabled)
	{
		HookGlobalForward(FWD_ON_GAME_FRAME, _SC_OnGameFrame);
	}
	else
	{
		UnhookGlobalForward(FWD_ON_GAME_FRAME, _SC_OnGameFrame);
	}
}

/**
 * Returns whether or not the client is out of bounds.
 *
 * @param client		Client index.
 * @return				True if out of bounds, false otherwise.
 */
static bool:IsClientOutOfBounds(client)
{
	if (!g_bIsInGame[client]) return false; // Client no longer in game
	decl Float:vec[3];
	GetClientAbsOrigin(client, vec);

	if (vec[0] < MAP_MAX_X &&
		vec[0] > MAP_MIN_X &&
		vec[1] < MAP_MAX_Y &&
		vec[1] > MAP_MIN_Y &&
		vec[2] < MAP_MAX_Z &&
		vec[2] > MAP_MIN_Z)
		return false;

	return true;
}

/**
 * Teleports the client to map center and logs.
 *
 * @param client		Client index.
 * @noreturn
 */
static TeleportClientAndLog(client)
{
	if (!g_bIsInGame[client]) return; // Client no longer in game
	switch (TeamIndex[client])
	{
		case TEAM_INFECTED:
		{
			if (IsPlayerAlive(client))
			{
				new infClient = -1;
				FOR_EACH_ALIVE_INFECTED(randomInfClient)
				{
					if (randomInfClient == client) continue;
					infClient = randomInfClient;
					break;
				}

				if (infClient != -1)
				{
					decl Float:infVec[3];
					GetClientAbsOrigin(infClient, infVec);
					TeleportEntity(client, infVec, NULL_VECTOR, Float:{0.0, 0.0, 0.0});
					LogPluginMessage("Teleported %L to a fellow infected %L, to prevent spec crash.", client, infClient);
				}
				else
				{
					LogPluginMessage("Teleported %L to map center and slayed, to prevent spec crash. No fellow infected could be found.", client);
					TeleportEntity(client, Float:{0.0, 0.0, 0.0}, NULL_VECTOR, Float:{0.0, 0.0, 0.0});
					ForcePlayerSuicide(client);
				}
			}
			else
			{
				LogPluginMessage("Teleported %L to map center, to prevent spec crash.", client);
				TeleportEntity(client, Float:{0.0, 0.0, 0.0}, NULL_VECTOR, Float:{0.0, 0.0, 0.0});
				SetClientToObserveAnySurvivor(client);
			}
		}

		case TEAM_SURVIVOR:
		{
			if (IsPlayerAlive(client))
			{
				new surClient = -1;
				FOR_EACH_ALIVE_SURVIVOR(randomSurClient)
				{
					if (randomSurClient == client) continue;
					surClient = randomSurClient;
					break;
				}

				if (surClient != -1)
				{
					decl Float:surVec[3];
					GetClientAbsOrigin(surClient, surVec);
					TeleportEntity(client, surVec, NULL_VECTOR, Float:{0.0, 0.0, 0.0});
					LogPluginMessage("Teleported %L to a fellow survivor %L, to prevent spec crash.", client, surClient);
				}
				else
				{
					LogPluginMessage("Teleported %L to map center and slayed, to prevent spec crash. No fellow survivors could be found.", client);
					TeleportEntity(client, Float:{0.0, 0.0, 0.0}, NULL_VECTOR, Float:{0.0, 0.0, 0.0});
					ForcePlayerSuicide(client);
				}
			}
			else
			{
				LogPluginMessage("Teleported %L to map center, to prevent spec crash.", client);
				TeleportEntity(client, Float:{0.0, 0.0, 0.0}, NULL_VECTOR, Float:{0.0, 0.0, 0.0});
				SetClientToObserveAnySurvivor(client);
			}
		}

		default:
		{
			LogPluginMessage("Teleported %L to map center, to prevent spec crash.", client);
			TeleportEntity(client, Float:{0.0, 0.0, 0.0}, NULL_VECTOR, Float:{0.0, 0.0, 0.0});
			if (!IsPlayerAlive(client))
			{
				SetClientToObserveAnySurvivor(client);
			}
		}
	}
}

/**
 * Sets client observe mode to 3rd person on a random alive survivor.
 *
 * @param client		Client index.
 * @noreturn
 */
static SetClientToObserveAnySurvivor(client)
{
	new surClient = GetAnyAliveSurvivor(false);
	if (surClient != -1)
	{
		SetEntPropEnt(client, Prop_Send, "m_hObserverTarget", surClient);
		SetEntProp(client, Prop_Send, "m_iObserverMode", OBSERVE_MODE_3RD_PERSON);
	}
}