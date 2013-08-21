/*
 * ============================================================================
 *
 *  File:			namechange.sp
 *  Type:			Module
 *  Description:	Kick clients who keep changing name way too often.
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

static			Handle:	g_hMaxNameChanges_Cvar			= INVALID_HANDLE;
static					g_iMaxNameChanges				= 4;

static			UserMsg:g_umSayText2					= INVALID_MESSAGE_ID;

static					g_iNameChanges[MAXPLAYERS + 1]	= {0};
static			Handle:	g_hTimer[MAXPLAYERS + 1]		= {INVALID_HANDLE};
static	const	Float:	RESET_TIME						= 5.0;

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
public _NameChange_OnPluginStart()
{
	decl String:buffer[10];
	IntToString(g_iMaxNameChanges, buffer, sizeof(buffer));
	g_hMaxNameChanges_Cvar = CreateConVarEx("kick_name_changers", buffer, "How many times a player can change their name in a 5 second space before getting kicked. 0 to disable.", FCVAR_PLUGIN);
	if (g_hMaxNameChanges_Cvar == INVALID_HANDLE) SetFailState("Unable to create kick name changers cvar!");

	HookGlobalForward(FWD_ON_PLUGIN_ENABLED, _NC_OnPluginEnabled);
	HookGlobalForward(FWD_ON_PLUGIN_DISABLED, _NC_OnPluginDisabled);
}

/**
 * Called on plugin enabled.
 *
 * @noreturn
 */
public _NC_OnPluginEnabled()
{
	g_iMaxNameChanges = GetConVarInt(g_hMaxNameChanges_Cvar);
	HookConVarChange(g_hMaxNameChanges_Cvar, _NC_Enable_CvarChange);

	g_umSayText2 = GetUserMessageId("SayText2");
	if (g_umSayText2 == INVALID_MESSAGE_ID) SetFailState("Unable to find SayText2 user message id");
	HookUserMessage(g_umSayText2, _NC_SayText2_UserMessage, true);
}

/**
 * Called on plugin disabled.
 *
 * @noreturn
 */
public _NC_OnPluginDisabled()
{
	UnhookConVarChange(g_hMaxNameChanges_Cvar, _NC_Enable_CvarChange);
	UnhookUserMessage(g_umSayText2, _NC_SayText2_UserMessage, true);
}

/**
 * Called when max name changes cvar is changed.
 *
 * @param convar		Handle to the convar that was changed.
 * @param oldValue		String containing the value of the convar before it was
 *						changed.
 * @param newValue		String containing the new value of the convar.
 * @noreturn
 */
public _NC_Enable_CvarChange(Handle:convar, const String:oldValue[], const String:newValue[])
{
	g_iMaxNameChanges = GetConVarInt(convar);
}

/**
 * Called when say test2 message is hooked.
 *
 * @param msg_id		Message index.
 * @param bf			Handle to the input bit buffer of the message.
 * @param players		Array containing player indexes.
 * @param playersNum	Number of players in the array.
 * @param reliable		True if message is reliable, false otherwise.
 * @param init			True if message is an initmsg, false otherwise.
 * @return				Plugin_Handled blocks the message from being sent, and 
 *						Plugin_Continue resumes normal functionality.
 */
public Action:_NC_SayText2_UserMessage(UserMsg:msg_id, Handle:bf, const players[], playersNum, bool:reliable, bool:init)
{
	if (g_iMaxNameChanges == 0) return Plugin_Continue;

	decl String:msg[96];
	BfReadString(bf, msg, sizeof(msg));
	BfReadString(bf, msg, sizeof(msg));
	if (StrContains(msg, "Name_Change") == -1) return Plugin_Continue;

	decl String:sUserName[96], String:name[32];
	BfReadString(bf, sUserName, sizeof(sUserName));

	FOR_EACH_HUMAN(client)
	{
		GetClientName(client, name, sizeof(name));
		if (!StrEqual(name, sUserName, true)) continue;

		g_iNameChanges[client]++;
		if (g_iNameChanges[client] > g_iMaxNameChanges)
		{
			KickBadClient(client, ClientChangedNameTooOften);
		}

		if (g_hTimer[client] != INVALID_HANDLE)
		{
			CloseHandle(g_hTimer[client]);
			g_hTimer[client] = INVALID_HANDLE;
		}
		g_hTimer[client] = CreateTimer(RESET_TIME, _NC_ResetCount_Timer, client);
		break;
	}

	return Plugin_Continue;
}

/**
 * Called when reset count timer interval has elapsed.
 *
 * @param timer			Handle to the timer object.
 * @param client		Client index.
 * @return				Plugin_Handled.
 */
public Action:_NC_ResetCount_Timer(Handle:timer, any:client)
{
	g_hTimer[client] = INVALID_HANDLE;
	g_iNameChanges[client] = 0;
	return Plugin_Handled;
}