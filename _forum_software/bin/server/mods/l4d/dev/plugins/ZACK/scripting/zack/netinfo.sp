/*
 * ============================================================================
 *
 *  File:			netinfo.sp
 *  Type:			Module
 *  Description:	Prints net information about clients on command.
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

static			bool:	g_bCooldown[MAXPLAYERS + 1];
static	const	Float:	COOLDOWN_TIME = 1.0;

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
public _NetInfo_OnPluginStart()
{
	/* COMMAND */
	decl String:buffer[128];
	Format(buffer, sizeof(buffer), "%s_netinfo", PLUGIN_CMD_PREFIX);
	RegConsoleCmd(buffer, _NI_NetInfo_Command, "Prints net information about players", FCVAR_PLUGIN);
}

/**
 * On net info client command.
 *
 * @param client		Index of the client, or 0 from the server.
 * @param args			Number of arguments that were in the argument string.
 * @return				Plugin_Handled.
 */
public Action:_NI_NetInfo_Command(client, args)
{
	/* Prevent spammage of this command */
	if (g_bCooldown[client]) return Plugin_Handled;
	g_bCooldown[client] = true;

	new const maxLen = 1024;
	decl String:result[maxLen];

	Format(result, maxLen, "\nPrinting net information about players:\n\n");

	Format(result, maxLen, "%s | UID    | NAME                 | STEAMID              | PING  | RATE  | CR  | UR  | INTERP | IRATIO |\n", result);
	Format(result, maxLen, "%s |--------|----------------------|----------------------|-------|-------|-----|-----|--------|--------|", result);

	if (client == CLIENT_INDEX_SERVER)
	{
		PrintToServer(result);
	}
	else
	{
		PrintToConsole(client, result);
	}

	decl uid, String:name[20], String:auth[20], Float:ping;
	decl String:rawRate[20], String:rawCR[20], String:rawUR[20], String:rawInterp[20], String:rawIRatio[20];
	decl rate, cmdrate, updaterate, Float:interp, Float:interpRatio;
	FOR_EACH_HUMAN(i)
	{
		if (TeamIndex[i] != TEAM_SURVIVOR && TeamIndex[i] != TEAM_INFECTED) continue; // Player isn't on survivors or infected, continue

		uid = GetClientUserId(i);
		GetClientName(i, name, 20);
		GetClientAuthString(i, auth, 20);
		ping = 1000.0 * GetClientAvgLatency(i, NetFlow_Outgoing);

		rate = -1;
		if (GetClientInfo(i, "rate", rawRate, 20))
		{
			rate = StringToInt(rawRate);
		}

		cmdrate = -1;
		if (GetClientInfo(i, "cl_cmdrate",rawCR, 20))
		{
			cmdrate = StringToInt(rawCR);
		}

		updaterate = -1;
		if (GetClientInfo(i, "cl_updaterate", rawUR, 20))
		{
			updaterate = StringToInt(rawUR);
		}

		interp = -1.0;
		if (GetClientInfo(i, "cl_interp", rawInterp, 20))
		{
			interp = StringToFloat(rawInterp);
		}

		interpRatio = -1.0;
		if (GetClientInfo(i, "cl_interp_ratio", rawIRatio, 20))
		{
			interpRatio = StringToFloat(rawIRatio);
		}

		Format(result, maxLen, " | #%-5i | %20s | %20s | %5.0f | %5i | %3i | %3i | %.4f | %.4f |",
			uid,
			name,
			auth,
			ping,
			rate,
			cmdrate,
			updaterate,
			interp,
			interpRatio);

		if (client == CLIENT_INDEX_SERVER)
		{
			PrintToServer(result);
		}
		else
		{
			PrintToConsole(client, result);
		}
	}

	Format(result, maxLen, "\nLegend:\n");
	Format(result, maxLen, "%s UID     - UserID\n", result);
	Format(result, maxLen, "%s NAME    - Current name of player\n", result);
	Format(result, maxLen, "%s STEAMID - SteamID of player\n", result);
	Format(result, maxLen, "%s PING    - Average ping\n", result);
	Format(result, maxLen, "%s RATE    - Rate\n", result);
	Format(result, maxLen, "%s CR      - Command rate\n", result);
	Format(result, maxLen, "%s UR      - Upload rate\n", result);
	Format(result, maxLen, "%s INTERP  - Interp value\n", result);
	Format(result, maxLen, "%s IRATIO  - Interp ratio value\n", result);

	if (client == CLIENT_INDEX_SERVER)
	{
		PrintToServer(result);
	}
	else
	{
		PrintToConsole(client, result);
	}

	CreateTimer(COOLDOWN_TIME, _NI_Cooldown_Timer, client);
	return Plugin_Handled;
}

/**
 * Called when the cooldown interval has elapsed.
 * 
 * @param timer			Handle to the timer object.
 * @param client		Client index.
 * @return				Plugin_Stop.
 */
public Action:_NI_Cooldown_Timer(Handle:timer, any:client)
{
	g_bCooldown[client] = false;
	return Plugin_Stop;
}