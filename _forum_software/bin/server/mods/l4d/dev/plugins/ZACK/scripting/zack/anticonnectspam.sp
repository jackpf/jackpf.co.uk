/*
 * ============================================================================
 *
 *  File:			anticonnectspam.sp
 *  Type:			Module
 *  Description:	Block clients who connect spam to the server.
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

#define MAX_STORED_IPS 	100
#define IP_MAX_LENGTH 	32

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

static			Handle:	g_hConnectResetTime_Cvar 	= INVALID_HANDLE;
static					g_iConnectResetTime 		= 2;

static			String:	g_sLastKnownIPs[MAX_STORED_IPS][IP_MAX_LENGTH];

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
public _AntiConnectSpam_OnPluginStart()
{
	decl String:buffer[32];
	IntToString(g_iConnectResetTime, buffer, sizeof(buffer));
	g_hConnectResetTime_Cvar = CreateConVarEx("anticonnectspam", buffer, "How many seconds a connection from the same ip is blocked after connection (0 to disable)", FCVAR_PLUGIN);
	if (g_hConnectResetTime_Cvar == INVALID_HANDLE) SetFailState("Unable to create anti connect spam cvar!");

	HookGlobalForward(FWD_ON_PLUGIN_ENABLED, _ACS_OnPluginEnabled);
	HookGlobalForward(FWD_ON_PLUGIN_DISABLED, _ACS_OnPluginDisabled);
}

/**
 * Called on plugin enabled.
 *
 * @noreturn
 */
public _ACS_OnPluginEnabled()
{
	HookGlobalForward(FWD_ON_CLIENT_CONNECT, _ACS_OnClientConnect);

	g_iConnectResetTime = GetConVarInt(g_hConnectResetTime_Cvar);
	HookConVarChange(g_hConnectResetTime_Cvar, _ACS_ConnectResetTime_CC);
}

/**
 * Called on plugin disabled.
 *
 * @noreturn
 */
public _ACS_OnPluginDisabled()
{
	UnhookGlobalForward(FWD_ON_CLIENT_CONNECT, _ACS_OnClientConnect);

	UnhookConVarChange(g_hConnectResetTime_Cvar, _ACS_ConnectResetTime_CC);
}

/**
 * Called on connect reset time cvar changed.
 *
 * @param convar		Handle to the convar that was changed.
 * @param oldValue		String containing the value of the convar before it was
 *						changed.
 * @param newValue		String containing the new value of the convar.
 * @noreturn
 */
public _ACS_ConnectResetTime_CC(Handle:convar, const String:oldValue[], const String:newValue[])
{
	g_iConnectResetTime = StringToInt(newValue);
}

/**
 * Called on client connect.
 *
 * @param client		Client index.
 * @param rejectmsg		Buffer to store the rejection message when the connection is refused.
 * @param maxlen		Maximum number of characters for rejection buffer.
 * @return				True to validate client's connection, false to refuse it.
 */
public bool:_ACS_OnClientConnect(client, String:rejectmsg[], maxlen)
{
	if (IsFakeClient(client) || g_iConnectResetTime == 0) return true;

	decl String:ip[IP_MAX_LENGTH];
	GetClientIP(client, ip, sizeof(ip));

	for (new i = 0; i < MAX_STORED_IPS; i++)
	{
		if (!StrEqual(ip, g_sLastKnownIPs[i])) continue;
		strcopy(rejectmsg, maxlen, "Please wait a minute before retrying to connect");
		BanIdentity(ip, 1, BANFLAG_IP, "Spam Connecting");
		return false;
	}

	for (new i = 0; i < MAX_STORED_IPS; i++)
	{
		if (strlen(g_sLastKnownIPs[i]) != 0) continue;
		strcopy(g_sLastKnownIPs[i], IP_MAX_LENGTH, ip);
		CreateTimer(float(g_iConnectResetTime), _ACS_ResetIPIndex, i);
		break;
	}

	return true;
}

/**
 * Called when ip reset interval has elapsed.
 * 
 * @param timer			Handle to the timer object.
 * @param index			Index of IP to reset.
 * @return				Plugin_Stop.
 */
public Action:_ACS_ResetIPIndex(Handle:timer, any:index)
{
	g_sLastKnownIPs[index] = "\0";
	return Plugin_Stop;
}