/*
 * ============================================================================
 *
 *	File:			forwardmanager.sp
 *	Type:			State helper
 *	Description:	Allow modules to hook global forwards.
 *
 *	Copyright (C) 2010  Mr. Zero <mrzerodk@gmail.com>
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

enum FORWARD_TYPE
{
	Handle:FWD_ON_PLUGIN_ENABLED,
	Handle:FWD_ON_PLUGIN_DISABLED,
	Handle:FWD_ON_ALL_PLUGINS_LOADED,
	Handle:FWD_ON_GAME_FRAME,
	Handle:FWD_ON_MAP_START,
	Handle:FWD_ON_MAP_END,
	Handle:FWD_ON_ENTITY_CREATED,
	Handle:FWD_ON_ENTITY_DESTROYED,
	Handle:FWD_ON_CLIENT_CONNECT,
	Handle:FWD_ON_CLIENT_PUT_IN_SERVER,
	Handle:FWD_ON_CLIENT_AUTHORIZED,
	Handle:FWD_ON_CLIENT_DISCONNECT,
	Handle:FWD_ON_PLAYER_RUN_CMD,
	Handle:FWD_ON_ANTI_WALLHACK_ENABLED,
	Handle:FWD_ON_ANTI_WALLHACK_DISABLED
}

#define FOWARD_TOTAL 15

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

static					g_hForwards[FOWARD_TOTAL];

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
public _ForwardManager_OnPluginStart()
{
	g_hForwards[FWD_ON_PLUGIN_ENABLED] 			= CreateForward(ET_Ignore);
	g_hForwards[FWD_ON_PLUGIN_DISABLED] 		= CreateForward(ET_Ignore);
	g_hForwards[FWD_ON_ALL_PLUGINS_LOADED] 		= CreateForward(ET_Ignore);
	g_hForwards[FWD_ON_GAME_FRAME] 				= CreateForward(ET_Ignore);
	g_hForwards[FWD_ON_MAP_START] 				= CreateForward(ET_Ignore);
	g_hForwards[FWD_ON_MAP_END] 				= CreateForward(ET_Ignore);
	g_hForwards[FWD_ON_ENTITY_CREATED] 			= CreateForward(ET_Ignore, Param_Cell, Param_String);
	g_hForwards[FWD_ON_ENTITY_DESTROYED] 		= CreateForward(ET_Ignore, Param_Cell);
	g_hForwards[FWD_ON_CLIENT_CONNECT] 			= CreateForward(ET_Hook, Param_Cell, Param_String, Param_Cell);
	g_hForwards[FWD_ON_CLIENT_AUTHORIZED] 		= CreateForward(ET_Ignore, Param_Cell, Param_String);
	g_hForwards[FWD_ON_CLIENT_PUT_IN_SERVER] 	= CreateForward(ET_Ignore, Param_Cell);
	g_hForwards[FWD_ON_CLIENT_DISCONNECT] 		= CreateForward(ET_Ignore, Param_Cell);
	g_hForwards[FWD_ON_PLAYER_RUN_CMD] 			= CreateForward(ET_Hook, Param_Cell, Param_CellByRef, Param_CellByRef, Param_Array, Param_Array, Param_CellByRef);
	g_hForwards[FWD_ON_ANTI_WALLHACK_ENABLED] 	= CreateForward(ET_Ignore);
	g_hForwards[FWD_ON_ANTI_WALLHACK_DISABLED] 	= CreateForward(ET_Ignore);
}

/**
 * Called on plugin enabled.
 *
 * @noreturn
 */
public OnPluginEnabled()
{
	Call_StartForward(g_hForwards[FWD_ON_PLUGIN_ENABLED]);
	Call_Finish();
}

/**
 * Called on plugin disabled.
 *
 * @noreturn
 */
public OnPluginDisabled()
{
	Call_StartForward(g_hForwards[FWD_ON_PLUGIN_DISABLED]);
	Call_Finish();
}

/**
 * Called on all plugins loaded.
 *
 * @noreturn
 */
public OnAllPluginsLoaded()
{
	/* Account for late loading */
	decl String:auth[64];
	FOR_EACH_CLIENT(client)
	{
		if (!IsClientConnected(client)) continue;
		g_bIsConnected[client] = true;

		if (IsFakeClient(client))
		{
			g_bIsFake[client] = true;
		}

		if (IsClientInGame(client))
		{
			g_bIsInGame[client] = true;
			Call_StartForward(g_hForwards[FWD_ON_CLIENT_PUT_IN_SERVER]);
			Call_PushCell(client);
			Call_Finish();
		}

		if (IsClientAuthorized(client))
		{
			g_bIsAuth[client] = true;
			GetClientAuthString(client, auth, sizeof(auth));
			Call_StartForward(g_hForwards[FWD_ON_CLIENT_AUTHORIZED]);
			Call_PushCell(client);
			Call_PushString(auth);
			Call_Finish();
		}
	}

	Call_StartForward(g_hForwards[FWD_ON_ALL_PLUGINS_LOADED]);
	Call_Finish();
}

/**
 * Called on game frame.
 *
 * @noreturn
 */
public OnGameFrame()
{
	Call_StartForward(g_hForwards[FWD_ON_GAME_FRAME]);
	Call_Finish();
}

/**
 * Called on map start.
 *
 * @noreturn
 */
public OnMapStart()
{
	Call_StartForward(g_hForwards[FWD_ON_MAP_START]);
	Call_Finish();
}

/**
 * Called on map end.
 *
 * @noreturn
 */
public OnMapEnd()
{
	Call_StartForward(g_hForwards[FWD_ON_MAP_END]);
	Call_Finish();
}

/**
 * Called when an entity is created.
 *
 * @param entity		Entity index.
 * @param classname		Classname.
 * @noreturn
 */
public OnEntityCreated(entity, const String:classname[])
{
	if (entity >= CLIENT_INDEX_FIRST && entity <= MAXENTITIES && IsValidEntity(entity))
	{
		Call_StartForward(g_hForwards[FWD_ON_ENTITY_CREATED]);
		Call_PushCell(entity);
		Call_PushString(classname);
		Call_Finish();
	}
}

/**
 * Called when an entity is destroyed.
 *
 * @param entity		Entity index.
 * @noreturn
 */
public OnEntityDestroyed(entity)
{
	if (entity >= CLIENT_INDEX_FIRST && entity <= MAXENTITIES && IsValidEntity(entity))
	{
		Call_StartForward(g_hForwards[FWD_ON_ENTITY_DESTROYED]);
		Call_PushCell(entity);
		Call_Finish();
	}
}

/**
 * Called on client connect.
 *
 * @param client		Client index.
 * @param rejectmsg		Buffer to store the rejection message when the connection is refused.
 * @param maxlen		Maximum number of characters for rejection buffer.
 * @return				True to validate client's connection, false to refuse it.
 */
public bool:OnClientConnect(client, String:rejectmsg[], maxlen)
{
	if (client == CLIENT_INDEX_SERVER) return true; // Don't forward server index

	g_bIsConnected[client] = true;
	if (IsFakeClient(client)) g_bIsFake[client] = true;

	new bool:result = true;
	Call_StartForward(g_hForwards[FWD_ON_CLIENT_CONNECT]);
	Call_PushCell(client);
	Call_Finish(_:result);
	return result;
}

/**
 * Called on client authorized.
 *
 * @param client		Client index.
 * @param auth			Client auth string.
 * @noreturn
 */
public OnClientAuthorized(client, const String:auth[])
{
	if (client == CLIENT_INDEX_SERVER) return; // Don't forward server index
	g_bIsAuth[client] = true;

	Call_StartForward(g_hForwards[FWD_ON_CLIENT_AUTHORIZED]);
	Call_PushCell(client);
	Call_PushString(auth);
	Call_Finish();
}

/**
 * Called on client put in server.
 *
 * @param client		Client index.
 * @noreturn
 */
public OnClientPutInServer(client)
{
	if (client == CLIENT_INDEX_SERVER) return; // Don't forward server index
	g_bIsInGame[client] = true;

	Call_StartForward(g_hForwards[FWD_ON_CLIENT_PUT_IN_SERVER]);
	Call_PushCell(client);
	Call_Finish();
}

/**
 * Called on client disconnect.
 *
 * @param client		Client index.
 * @noreturn
 */
public OnClientDisconnect(client)
{
	if (client == CLIENT_INDEX_SERVER) return; // Don't forward server index
	g_bIsConnected[client] = false;
	g_bIsInGame[client] = false;
	g_bIsFake[client] = false;
	g_bIsAuth[client] = false;

	Call_StartForward(g_hForwards[FWD_ON_CLIENT_DISCONNECT]);
	Call_PushCell(client);
	Call_Finish();
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
public Action:OnPlayerRunCmd(client, &buttons, &impulse, Float:vel[3], Float:angles[3], &weapon)
{
	new Action:result = Plugin_Continue;
	Call_StartForward(g_hForwards[FWD_ON_PLAYER_RUN_CMD]);
	Call_PushCell(client);
	Call_PushCellRef(buttons);
	Call_PushCellRef(impulse);
	Call_PushArray(vel, 3);
	Call_PushArray(angles, 3);
	Call_PushCellRef(weapon);
	Call_Finish(_:result);
	return result;
}

/**
 * Called on anti wallhack enabled.
 *
 * @noreturn
 */
public OnAntiWallHackEnabled()
{
	Call_StartForward(g_hForwards[FWD_ON_ANTI_WALLHACK_ENABLED]);
	Call_Finish();
}

/**
 * Called on anti wallhack disabled.
 *
 * @noreturn
 */
public OnAntiWallHackDisabled()
{
	Call_StartForward(g_hForwards[FWD_ON_ANTI_WALLHACK_DISABLED]);
	Call_Finish();
}

/*
 * ==================================================
 *                     Public API
 * ==================================================
 */

/**
 * Hooks the function to the forward of selected type.
 * 
 * @param type			The type of forward.
 * @param func			The function to add.
 * @return				True on success, false otherwise.
 */
stock bool:HookGlobalForward(FORWARD_TYPE:type, Function:func)
{
	return AddToForward(Handle:g_hForwards[type], INVALID_HANDLE, func);
}

/**
 * Unhooks the function from the forward of selected type.
 * 
 * @param type			The type of forward.
 * @param func			The function to add.
 * @return				True on success, false otherwise.
 */
stock bool:UnhookGlobalForward(FORWARD_TYPE:type, Function:func)
{
	return RemoveFromForward(Handle:g_hForwards[type], INVALID_HANDLE, func);
}