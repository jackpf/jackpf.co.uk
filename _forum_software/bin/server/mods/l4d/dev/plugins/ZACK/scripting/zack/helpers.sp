/*
 * ============================================================================
 *
 *  File:			helpers.sp
 *  Type:			Module
 *  Description:	Provides wrapper functions for modules and helper 
 *					functions.
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
 *                     Includes
 * ==================================================
 */

#include "helpers/constants.inc"
#include "helpers/enums.inc"
#include "helpers/macros.inc"
#include "helpers/log.inc"

/*
 * ==================================================
 *                     Public API
 * ==================================================
 */

/**
 * Executes cheat command on client.
 *
 * @param client		Client to execute cheat command, if not provided a 
 *						random client will be picked.
 * @param command		Cheat command.
 * @param arguments		Arguments for command.
 * @return				True if executed, false otherwise.
 */
stock bool:CheatCommand(client = 0, String:command[], String:arguments[]="")
{
	if (client < CLIENT_INDEX_FIRST || client > CLIENT_INDEX_LAST || !IsClientInGame(client))
	{
		client = GetAnyClient();
		if (client == -1) return false; // No players found to exec cheat cmd, return false
	}

	/* Apply root admin flag to user for compatiable with "Admin Cheats" plugin
	 * by devicenull */
	new userFlags = GetUserFlagBits(client);
	SetUserFlagBits(client, ADMFLAG_ROOT);

	new flags = GetCommandFlags(command);
	SetCommandFlags(command, flags & ~FCVAR_CHEAT);
	FakeClientCommand(client, "%s %s", command, arguments);
	SetCommandFlags(command, flags);
	SetUserFlagBits(client, userFlags);

	return true;
}

/**
 * Wrapper for CreateConVar. Prefixes the cvar with the plugins cvar prefix.
 *
 * @param name			Name of new convar.
 * @param defaultValue	String containing the default value of new convar.
 * @param description	Optional description of the convar.
 * @param flags			Optional bitstring of flags determining how the convar
 *						should be handled. See FCVAR_* constants for more
 *						details.
 * @param hasMin		Optional boolean that determines if the convar has a 
 *						minimum value.
 * @param min			Minimum floating point value that the convar can have 
 *						if hasMin is true.
 * @param hasMax		Optional boolean that determines if the convar has a 
 *						maximum value.
 * @param max			Maximum floating point value that the convar can have 
 *						if hasMax is true.
 * @return				A handle to the newly created convar. If the convar 
 *						already exists, a handle to it will still be returned.
 */
stock Handle:CreateConVarEx(const String:name[], const String:defaultValue[], const String:description[]="", flags=0, bool:hasMin=false, Float:min=0.0, bool:hasMax=false, Float:max=0.0)
{
	decl String:buffer[256];
	Format(buffer, sizeof(buffer), "%s_%s", PLUGIN_CVAR_PREFIX, name);
	return CreateConVar(buffer, defaultValue, description, flags, hasMin, min, hasMax, max);
}

/**
 * Wrapper for FindEntityByClassname to fall back on last valid entity.
 * Credits to exvel on AlliedModders.
 *
 * @param startEnt		The entity index after which to begin searching from. 
 *						Use -1 to start from the first entity.
 * @param classname		Classname of the entity to find.
 * @return				Entity index >= 0 if found, -1 otherwise.
 */
stock FindEntityByClassnameEx(startEnt, const String:classname[])
{
	while (startEnt > -1 && !IsValidEntity(startEnt)) startEnt--;
	return FindEntityByClassname(startEnt, classname);
}

/**
 * Returns any ingame client.
 *
 * @param filterBots	Whether or not bots are also returned.
 * @return				Client index if found, -1 otherwise.
 */
stock GetAnyClient(bool:filterBots = false)
{
	FOR_EACH_CLIENT(client)
	{
		if (IsFakeClient(client) && filterBots) continue;
		return client;
	}
	return -1;
}

/**
 * Returns any ingame alive survivor client.
 *
 * @param filterBots	Whether or not bots are also returned.
 * @return				Client index if found, -1 otherwise.
 */
stock GetAnyAliveSurvivor(bool:filterBots = false)
{
	FOR_EACH_ALIVE_SURVIVOR(client)
	{
		if (filterBots && IsFakeClient(client)) continue;
		return client;
	}
	return -1;
}

/**
 * Returns any ingame alive infected client.
 *
 * @param filterBots	Whether or not bots are also returned.
 * @return				Client index if found, -1 otherwise.
 */
stock GetAnyAliveInfected(bool:filterBots = false)
{
	FOR_EACH_ALIVE_INFECTED(client)
	{
		if (filterBots && IsFakeClient(client)) continue;
		return client;
	}
	return -1;
}

/**
 * Returns the client count put in the server.
 *
 * @param inGameOnly	Whether or not connecting players are also counted.
 * @param fliterBots	Whether or not bots are also counted.
 * @return				Client count in the server.
 */
stock GetClientCountEx(bool:inGameOnly, bool:filterBots)
{
	new clients = 0;
	FOR_EACH_CLIENT_COND(i, (IsClientConnected(i)))
	{
		if (inGameOnly && !IsClientInGame(i)) continue;
		if (filterBots && IsFakeClient(i)) continue;
		clients++;
	}
	return clients;
}

/**
 * Returns entity's absolute origin.
 *
 * @param entity		Entity index.
 * @param origin		Destination vector buffer to store origin in.
 * @noreturn
 */
stock GetEntityAbsOrigin(entity, Float:origin[3])
{
	if (entity < CLIENT_INDEX_FIRST || !IsValidEntity(entity)) return;

	decl Float:mins[3], Float:maxs[3];
	GetEntPropVector(entity, Prop_Send, "m_vecOrigin", origin);
	GetEntPropVector(entity, Prop_Send, "m_vecMins", mins);
	GetEntPropVector(entity, Prop_Send, "m_vecMaxs", maxs);

	for (new i = 0; i < 3; i++)
	{
		origin[i] += (mins[i] + maxs[i]) * 0.5;
	}
}

/**
 * Retrieve the opposite team index for provided index.
 *
 * @param index			Team index.
 * @return				Team index of the opposite team, -1 otherwise.
 */
stock GetOppositeTeamIndex(index)
{
	switch(index)
	{
		case TEAM_SPECTATOR:
		{
			return TEAM_SPECTATOR;
		}

		case TEAM_SURVIVOR:
		{
			return TEAM_INFECTED;
		}

		case TEAM_INFECTED:
		{
			return TEAM_SURVIVOR;
		}
	}
	return -1;
}

/**
 * Returns players current zombie class.
 *
 * @param client		Client index.
 * @return				Zombie class, see enums.inc for list.
 */
stock ZombieClass:GetPlayerZombieClass(client)
{
	if (!g_bIsInGame[client]) return ZC_UNKNOWN;

	if (GetClientTeam(client) == TEAM_SURVIVOR)
	{
		return ZC_NOT_INFECTED;
	}

	if (GetClientTeam(client) == TEAM_INFECTED)
	{
		return ZombieClass:GetEntProp(client, Prop_Send, "m_zombieClass");
	}

	return ZC_UNKNOWN;
}

/**
 * Returns ghost state of player.
 *
 * @param client		Client index.
 * @return				True if client is ghost, false otherwise.
 */
stock bool:IsPlayerGhost(client)
{
	if (client < CLIENT_INDEX_FIRST || 
		client > CLIENT_INDEX_LAST || 
		!IsClientInGame(client) || 
		GetClientTeam(client) == TEAM_INFECTED) 
		return false;
	return bool:GetEntProp(client, Prop_Send, "m_isGhost", 1);
}

/**
 * Returns whether translation file is valid and readable.
 *
 * @param name			Name of translation file.
 * @return				True if valid, false otherwise.
 */
stock bool:IsTranslationValid(const String:name[])
{
	decl String:path[PLATFORM_MAX_PATH], Handle:file;
	BuildPath(Path_SM, path, PLATFORM_MAX_PATH, "translations/%s.txt", name);
	if (!FileExists(path, false))
	{
		return false;
	}
	else if ((file = OpenFile(path, "r")) == INVALID_HANDLE)
	{
		return false;
	}
	else
	{
		CloseHandle(file);
		return true;
	}
}

/**
 * Wrapper for RegAdminCmd. Prefixes the cmd with the plugins cmd prefix.
 *
 * @param cmd			String containing command to register.
 * @param callback		A function to use as a callback for when the command is
 *						invoked.
 * @param adminflags	Administrative flags (bitstring) to use for 
 *						permissions.
 * @param description	Optional description to use for help.
 * @param group			String containing the command group to use.  If empty, 
 *						the plugin's filename will be used instead.
 * @param flags			Optional console flags.
 * @noreturn
 */
stock RegAdminCmdEx(const String:cmd[], ConCmd:callback, adminflags, const String:description[]="", const String:group[]="", flags=0)
{
	decl String:buffer[256];
	Format(buffer, sizeof(buffer), "%s_%s", PLUGIN_CMD_PREFIX, cmd);
	RegAdminCmd(buffer, callback, adminflags, description, group, flags);
}

/**
 * Wrapper for RegConsoleCmd. Prefixes the cmd with the plugins cmd prefix.
 *
 * @param cmd			String containing command to register.
 * @param callback		A function to use as a callback for when the command is
 *						invoked.
 * @param description	Optional description to use for help.
 * @param flags			Optional console flags.
 * @noreturn
 */
stock RegConsoleCmdEx(const String:cmd[], ConCmd:callback, const String:description[]="", flags=0)
{
	decl String:buffer[256];
	Format(buffer, sizeof(buffer), "%s_%s", PLUGIN_CMD_PREFIX, cmd);
	RegConsoleCmd(buffer, callback, description, flags);
}

/**
 * Sets ghost state of player.
 *
 * @param client		Client index.
 * @param isGhost		Sets ghost status.
 * @noreturn
 */
stock SetPlayerGhostState(client, bool:isGhost)
{
	if (client < CLIENT_INDEX_FIRST || 
		client > CLIENT_INDEX_LAST || 
		!IsClientInGame(client) || 
		GetClientTeam(client) == TEAM_INFECTED)
		return;
	SetEntProp(client, Prop_Send, "m_isGhost", isGhost, 1);
}