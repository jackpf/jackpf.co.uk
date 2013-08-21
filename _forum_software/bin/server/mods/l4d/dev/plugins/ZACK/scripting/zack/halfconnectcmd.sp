/*
 * ============================================================================
 *
 *  File:			halfconnectcmd.sp
 *  Type:			Module
 *  Description:	Prevents clients from sending commands while not being 
 *					fully in game.
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
 *                     Forwards
 * ==================================================
 */

/**
 * Called on plugin start.
 *
 * @noreturn
 */
public _HalfConnectCmd_OnPluginStart()
{
	HookGlobalForward(FWD_ON_PLUGIN_ENABLED, _HCC_OnPluginEnabled);
	HookGlobalForward(FWD_ON_PLUGIN_DISABLED, _HCC_OnPluginDisabled);
}

/**
 * Called on plugin enabled.
 *
 * @noreturn
 */
public _HCC_OnPluginEnabled()
{
	AddCommandListener(_HCC_Command);
}

/**
 * Called on plugin disabled.
 *
 * @noreturn
 */
public _HCC_OnPluginDisabled()
{
	RemoveCommandListener(_HCC_Command);
}

/**
 * Callback for half connect command.
 *
 * @param client        Client, or 0 for server.
 * @param command       Command name, lower case.
 * @param argc          Argument count.
 * @return				Plugin_Stop to stop command, Plugin_Continue allow 
 *						command.
 */
public Action:_HCC_Command(client, const String:command[], argc)
{
	if (client == 0) return Plugin_Continue;

	if (!IsClientConnected(client)) return Plugin_Stop;

	if (!IsClientInGame(client))
	{
		new String:arguments[2048];
		GetCmdArgString(arguments, sizeof(arguments));
		decl String:ip[64];
		GetClientIP(client, ip, sizeof(ip));
		LogPluginMessage("Prevented a half-connected command from client <%i><%s>: \"%s %s\".", client, ip, command, arguments);
		return Plugin_Stop;
	}

	return Plugin_Continue;	
}