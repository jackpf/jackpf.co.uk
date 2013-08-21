/*
 * ============================================================================
 *
 *  File:			authcheck.sp
 *  Type:			State Helper
 *  Description:	Checks whether a client has been authenticated.
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

static	const	Float:	MAX_AUTH_TIME					= 10.0; // If a client is fully ingame yet not auth'd, give this much time before kicking the client
static			Handle:	g_hAuthTimers[MAXPLAYERS + 1]	= {INVALID_HANDLE};

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
public _AuthCheck_OnPluginStart()
{
	HookGlobalForward(FWD_ON_CLIENT_AUTHORIZED, _AC_OnClientAuthorized);
	HookGlobalForward(FWD_ON_CLIENT_PUT_IN_SERVER, _AC_OnClientPutInServer);
	HookGlobalForward(FWD_ON_CLIENT_DISCONNECT, _AC_OnClientDisconnect);

	FOR_EACH_CLIENT(client)
	{
		if (IsClientInGame(client) && !IsFakeClient(client))
		{
			if (IsClientAuthorized(client))
			{
				g_bIsAuth[client] = true;
			}
			else
			{
				g_hAuthTimers[client] = CreateTimer(MAX_AUTH_TIME, _AC_CheckAuth_Timer, GetClientUserId(client));
			}
		}
		else
		{
			g_bIsAuth[client] = false;
		}
	}
}

/**
 * Called on client authorized.
 *
 * @param client		Client index.
 * @param auth			Client auth string.
 * @noreturn
 */
public _AC_OnClientAuthorized(client, const String:auth[])
{
	if (g_bIsFake[client]) return;
	g_bIsAuth[client] = true;
}

/**
 * Called on client put in server.
 *
 * @param client		Client index.
 * @noreturn
 */
public _AC_OnClientPutInServer(client)
{
	if (g_bIsFake[client]) return;
	if (!g_bIsAuth[client])
	{
		g_hAuthTimers[client] = CreateTimer(MAX_AUTH_TIME, _AC_CheckAuth_Timer, GetClientUserId(client));
	}
}

/**
 * Called on client disconnect.
 *
 * @param client		Client index.
 * @noreturn
 */
public _AC_OnClientDisconnect(client)
{
	if (g_bIsFake[client]) return;
	if (g_hAuthTimers[client] != INVALID_HANDLE)
	{
		CloseHandle(g_hAuthTimers[client]);
		g_hAuthTimers[client] = INVALID_HANDLE;
	}
	g_bIsAuth[client] = false;
}

/**
 * Called when check auth interval has elapsed.
 * 
 * @param timer			Handle to the timer object.
 * @param userid		UserID of client.
 * @return				Plugin_Stop.
 */
public Action:_AC_CheckAuth_Timer(Handle:timer, any:userid)
{
	timer = INVALID_HANDLE;
	new client = GetClientOfUserId(userid);

	if (IsPluginEnabled() &&
		client >= CLIENT_INDEX_FIRST &&
		client <= CLIENT_INDEX_LAST &&
		g_bIsInGame[client] &&
		!g_bIsFake[client] &&
		!g_bIsAuth[client])
	{
		KickBadClient(client, ClientFailedToAuth);
	}
	return Plugin_Stop;
}