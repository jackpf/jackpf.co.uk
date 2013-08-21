/*
 * ============================================================================
 *
 *  File:			ghosts.sp
 *  Type:			Anti Wallhack Module
 *  Description:	Blocks transmittion of ghost infected to survivors.
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

static					g_iOffsetGhost = -1;

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
public _AWH_Ghost_OnPluginStart()
{
	g_iOffsetGhost = FindSendPropInfo("CTerrorPlayer", "m_isGhost");
	if (g_iOffsetGhost <= 0)
	{
		LogPluginMessage("ERROR: Unable to find ghost offset for anti wallhack!");
		LogPluginMessage("ERROR: Ghost Offset Return: %i", g_iOffsetGhost);
		LogPluginMessage("ERROR: Ghost anti wallhack module disabled!");
		return;
	}

	HookGlobalForward(FWD_ON_ANTI_WALLHACK_ENABLED, _AWH_G_OnAntiWallHackEnabled);
	HookGlobalForward(FWD_ON_ANTI_WALLHACK_DISABLED, _AWH_G_OnAntiWallHackDisabled);
}

/**
 * Called on anti wallhack enabled.
 *
 * @noreturn
 */
public _AWH_G_OnAntiWallHackEnabled()
{
	FOR_EACH_CLIENT_IN_GAME(client)
	{
		SDKHook(client, SDKHook_SetTransmit, _AWH_G_SetTransmit);
	}

	HookGlobalForward(FWD_ON_CLIENT_PUT_IN_SERVER, _AWH_G_OnClientPutInServer);
	HookGlobalForward(FWD_ON_CLIENT_DISCONNECT, _AWH_G_OnClientDisconnect);
}

/**
 * Called on anti wallhack disabled.
 *
 * @noreturn
 */
public _AWH_G_OnAntiWallHackDisabled()
{
	UnhookGlobalForward(FWD_ON_CLIENT_PUT_IN_SERVER, _AWH_G_OnClientPutInServer);
	UnhookGlobalForward(FWD_ON_CLIENT_DISCONNECT, _AWH_G_OnClientDisconnect);

	FOR_EACH_CLIENT_IN_GAME(client)
	{
		SDKUnhook(client, SDKHook_SetTransmit, _AWH_G_SetTransmit);
	}
}

/**
 * Called on client put in server.
 *
 * @param client		Client index.
 * @noreturn
 */
public _AWH_G_OnClientPutInServer(client)
{
	SDKHook(client, SDKHook_SetTransmit, _AWH_G_SetTransmit);
}

/**
 * Called on client disconnect.
 *
 * @param client		Client index.
 * @noreturn
 */
public _AWH_G_OnClientDisconnect(client)
{
	SDKUnhook(client, SDKHook_SetTransmit, _AWH_G_SetTransmit);
}

/**
 * Called on set transmit.
 *
 * @param entity		Entity index.
 * @param client		Client index entity is being transmitted to.
 * @return				Plugin_Handle to stop transmission, Plugin_Continue to allow transmission.
 */
public Action:_AWH_G_SetTransmit(entity, client)
{
	if (client == entity || // If client == entity
		g_bIsFake[client] || // Or client is a bot
		TeamIndex[client] != TEAM_SURVIVOR || // Or client ain't survivor
		TeamIndex[entity] != TEAM_INFECTED || // Or entity ain't infected
		!bool:GetEntData(entity, g_iOffsetGhost)) // Or entity ain't a ghost
		return Plugin_Continue; // Allow transmission

	return Plugin_Handled; // Stop transmission of ghost infected to survivor client
}