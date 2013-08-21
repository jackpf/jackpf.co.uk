/*
 * ============================================================================
 *
 *  File:			disconnectmsg.sp
 *  Type:			Module
 *  Description:	Prevents clients from submitting bad disconnect messages.
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

static	const			MAX_DISCONNECT_MSG_LENGTH = 235;

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
public _DisconnectMsg_OnPluginStart()
{
	HookGlobalForward(FWD_ON_PLUGIN_ENABLED, _DM_OnPluginEnabled);
	HookGlobalForward(FWD_ON_PLUGIN_DISABLED, _DM_OnPluginDisabled);
}

/**
 * Called on plugin enabled.
 *
 * @noreturn
 */
public _DM_OnPluginEnabled()
{
	HookEvent("player_disconnect", _DM_PlayerDisconnect_Event, EventHookMode_Pre);
}

/**
 * Called on plugin disabled.
 *
 * @noreturn
 */
public _DM_OnPluginDisabled()
{
	UnhookEvent("player_disconnect", _DM_PlayerDisconnect_Event, EventHookMode_Pre);
}

/**
 * Called when a player disconnects.
 *
 * @param event			Handle to event.
 * @param name			String containing the name of the event.
 * @param dontBroadcast	True if event was not broadcast to clients, false otherwise.
 * @return				Plugin_Handled will block event, Plugin_Continue to allow event.
 */
public Action:_DM_PlayerDisconnect_Event(Handle:event, const String:name[], bool:dontBroadcast)
{
	new client = GetClientOfUserId(GetEventInt(event, "userid"));
	if (client == CLIENT_INDEX_SERVER || IsFakeClient(client)) return Plugin_Continue;

	decl String:reason[512], String:buffer[512];
	new msgLength;
	GetEventString(event, "reason", reason, sizeof(reason));

	GetEventString(event, "name", buffer, sizeof(buffer));
	msgLength = strlen(reason) + strlen(buffer);

	GetEventString(event, "networkid", buffer, sizeof(buffer));
	msgLength += strlen(buffer);

	new fixMsg = false;

	/* Prevent buffer overflow for disconnect message */
	if (msgLength > MAX_DISCONNECT_MSG_LENGTH)
	{
		fixMsg = true;
	}
	else
	{
		/* Prevent usage of non type able chars in disconnect message */
		msgLength = strlen(reason);
		for (new i = 0; i < msgLength; i++)
		{
			if (reason[i] >= 32 || reason[i] == '\n') continue;
			fixMsg = true;
			break;
		}
	}

	if (fixMsg)
	{
		decl String:ip[64];
		GetClientIP(client, ip, sizeof(ip));
		SetEventString(event, "reason", "Bad disconnect message");
		LogPluginMessage("%L<%s> submitted a bad disconnect message and was fixed. Length: %i, reason: \"%s\".", client, ip, msgLength, reason);
	}

	return Plugin_Continue;
}