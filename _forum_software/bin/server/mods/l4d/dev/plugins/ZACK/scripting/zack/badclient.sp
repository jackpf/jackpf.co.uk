/*
 * ============================================================================
 *
 *  File:			badclient.sp
 *  Type:			Module
 *  Description:	Handles "bad clients" once detected and forwarded from
 *					another module.
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

/* See enums in helpers for list of BadClientReason */

#define MAX_BAD_REASONS 8 // Max of reasons why a client is bad
#define MAX_BAD_REASON_LENGTH 128 // Max length of bad reason

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

static			String:	g_sReasons[MAXPLAYERS + 1][MAX_BAD_REASONS][MAX_BAD_REASON_LENGTH];
static			bool:	g_bIsSourceBansLoaded = false;

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
public _BadClient_OnPluginStart()
{
	HookGlobalForward(FWD_ON_ALL_PLUGINS_LOADED, _BC_OnAllPluginsLoaded);
	HookGlobalForward(FWD_ON_CLIENT_DISCONNECT, _BC_OnClientDisconnect);
}

/**
 * Called on all plugins loaded.
 *
 * @noreturn
 */
public _BC_OnAllPluginsLoaded()
{
	new Handle:sourcebans = FindPluginByFile("sourcebans.smx");
	if (sourcebans != INVALID_HANDLE)
	{
		g_bIsSourceBansLoaded = true;
		CloseHandle(sourcebans);
	}
	else
	{
		g_bIsSourceBansLoaded = false;
	}
}

/**
 * Called on client disconnect.
 *
 * @param client		Client index.
 * @noreturn
 */
public _BC_OnClientDisconnect(client)
{
	ClearReasons(client);
}

/*
 * ==================================================
 *                     Public API
 * ==================================================
 */

/**
 * Sets a reason why client is bad.
 *
 * @param client		Client index.
 * @param reason		A reason why client is bad.
 * @noreturn
 */
stock SetBadReason(client, const String:format[], any:...)
{
	decl String:reason[128];
	VFormat(reason, sizeof(reason), format, 3);

	for (new i = 0; i < MAX_BAD_REASONS; i++)
	{
		if (strlen(g_sReasons[client][i]) != 0) continue;
		strcopy(g_sReasons[client][i], MAX_BAD_REASON_LENGTH, reason);
		break;
	}
}

/**
 * Kicks a bad client with the reason.
 *
 * @param client		Client index to kick.
 * @param reason		The reason why client was bad.
 * @param customMsg		Custom kick message.
 * @return				True if client was kicked, false otherwise.
 */
stock bool:KickBadClient(client, BadClientReason:reason, const String:customMsg[] = "")
{
	if (IsClientInKickQueue(client)) return true;

	new bool:kickClient = false;
	switch (reason)
	{
		/* Name change kick */
		case ClientChangedNameTooOften:
		{
			LogPluginMessage("Kicked %L for changing name too often", client);

			decl String:kickMsg[MAX_KICK_MSG_LENGTH];
			Format(kickMsg, sizeof(kickMsg), "%T", "Client - Name Change Too Often", client);
			KickClient(client, kickMsg);
			kickClient = true;
		}

		/* Auth fail kick */
		case ClientFailedToAuth:
		{
			LogPluginMessage("Kicked %L for not authorize in time.", client);

			decl String:kickMsg[MAX_KICK_MSG_LENGTH];
			Format(kickMsg, sizeof(kickMsg), "%T", "Client - Failed To Auth", client);
			KickClient(client, kickMsg);
			kickClient = true;
		}

		/* Incorrect interp kick */
		case ClientHaveIncorrectInterp:
		{
			/* Retive reason arguments */
			decl String:buffer[128];
			strcopy(buffer, sizeof(buffer), g_sReasons[client][0]);
			new Float:interpValue = StringToFloat(buffer);
			strcopy(buffer, sizeof(buffer), g_sReasons[client][1]);
			new Float:interpMax = StringToFloat(buffer);
			strcopy(buffer, sizeof(buffer), g_sReasons[client][2]);
			new Float:interpMin = StringToFloat(buffer);

			LogPluginMessage("Kicked %L for using incorrect intep values. Was using a value of %f, max %f, min %f.", client, interpValue, interpMax, interpMin);

			decl String:kickMsg[MAX_KICK_MSG_LENGTH];
			Format(kickMsg, sizeof(kickMsg), "%T", "Client - Using Incorrect Interp", client, interpMax, interpMin);
			KickClient(client, kickMsg);

			decl String:msg[256], String:name[32];
			GetClientName(client, name, sizeof(name));
			FOR_EACH_HUMAN(index)
			{
				Format(msg, sizeof(msg), "[%s] %T", PLUGIN_TAG, "Server - Client Using Incorrect Interp", index, name, interpValue, interpMax, interpMin);
				PrintToChat(index, msg);
			}
			kickClient = true;
		}

		/* Failed to reply kick */
		case CvarFailedToReply:
		{
			/* Retive reason arguments */
			decl String:cvar[128];
			strcopy(cvar, sizeof(cvar), g_sReasons[client][0]);

			LogPluginMessage("Kicked %L for not replying on cvar: \"%s\"", client, cvar);

			decl String:kickMsg[MAX_KICK_MSG_LENGTH];
			Format(kickMsg, sizeof(kickMsg), "%T", "Client - Failed To Reply", client);
			KickClient(client, kickMsg);
			kickClient = true;
		}

		/* Corrupt cvar kick */
		case CvarCorrupt:
		{
			/* Retive reason arguments */
			decl String:cvar[128];
			strcopy(cvar, sizeof(cvar), g_sReasons[client][0]);
			decl String:corruptReason[128];
			strcopy(corruptReason, sizeof(corruptReason), g_sReasons[client][1]);

			LogPluginMessage("Kicked %L for corruption on cvar: \"%s\", reason: \"%s\".", client, cvar, corruptReason);

			decl String:kickMsg[MAX_KICK_MSG_LENGTH];
			Format(kickMsg, sizeof(kickMsg), "%T", "Client - Corrupt", client);
			KickClient(client, kickMsg);
			kickClient = true;
		}
	}

	ClearReasons(client);
	return kickClient;
}

/**
 * Bans a bad client with the reason.
 *
 * @param client		Client index to kick.
 * @param reason		Ban reason.
 * @return				True if client was banned, false otherwise.
 */
stock bool:BanBadClient(client, BadClientReason:reason)
{
	new bool:banClient = false;
	decl String:banReason[256];

	switch (reason)
	{
		/* Cvar should be replicated ban */
		case CvarShouldBeReplicated:
		{
			/* Retive reason arguments */
			decl String:cvar[128];
			strcopy(cvar, sizeof(cvar), g_sReasons[client][0]);
			decl String:value[128];
			strcopy(value, sizeof(value), g_sReasons[client][1]);
			decl String:valueNormal[128];
			strcopy(valueNormal, sizeof(valueNormal), g_sReasons[client][2]);

			LogPluginMessage("Banned %L for not replicating cvar: \"%s\". Should be \"%s\", replied with \"%s\"", client, cvar, valueNormal, value);

			Format(banReason, sizeof(banReason), "%s: %T", PLUGIN_TAG, "Server - Ban Reason - Cvar Violation", LANG_SERVER, cvar);

			banClient = true;
		}

		/* Cvar should be within ban */
		case CvarShouldBeWithin:
		{
			/* Retive reason arguments */
			decl String:cvar[128];
			strcopy(cvar, sizeof(cvar), g_sReasons[client][0]);
			decl String:value[128];
			strcopy(value, sizeof(value), g_sReasons[client][1]);
			decl String:valueMin[128];
			strcopy(valueMin, sizeof(valueMin), g_sReasons[client][2]);
			decl String:valueMax[128];
			strcopy(valueMax, sizeof(valueMax), g_sReasons[client][3]);

			LogPluginMessage("Banned %L for having value out of bounds on cvar: \"%s\". Should be within \"%s\" and \"%s\", replied with \"%s\"", client, cvar, valueMin, valueMax, value);

			Format(banReason, sizeof(banReason), "%s: %T", PLUGIN_TAG, "Server - Ban Reason - Cvar Violation", LANG_SERVER, cvar);

			banClient = true;
		}
	}

	ClearReasons(client);

	/* If banning client */
	if (banClient)
	{
		decl String:banMsg[MAX_KICK_MSG_LENGTH];
		Format(banMsg, sizeof(banMsg), "%T", "Client - Banned", client);

		decl String:msg[256], String:name[32];
		GetClientName(client, name, sizeof(name));
		FOR_EACH_HUMAN(index)
		{
			if (index == client) continue;
			Format(msg, sizeof(msg), "[%s] %T", PLUGIN_TAG, "Server - Banned Client", index, name);
			PrintToChat(index, msg);
		}

		if (g_bIsSourceBansLoaded)
		{
			SBBanPlayer(CLIENT_INDEX_SERVER, client, 0, banReason); // Ban client with sourcebans if present on server
		}
		else
		{
			BanClient(client, 0, BANFLAG_AUTO, banReason, banMsg, "ZACK", CLIENT_INDEX_SERVER);
		}
	}

	return banClient;
}

/*
 * ==================================================
 *                    Private API
 * ==================================================
 */

/**
 * Clear kick/ban reasons for client.
 *
 * @param client		Client index.
 * @noreturn
 */
static ClearReasons(client)
{
	for (new i = 0; i < MAX_BAD_REASONS; i++)
	{
		strcopy(g_sReasons[client][i], MAX_BAD_REASON_LENGTH, "\0");
	}
}