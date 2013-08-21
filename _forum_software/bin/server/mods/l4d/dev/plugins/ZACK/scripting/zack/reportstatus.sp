/*
 * ============================================================================
 *
 *  File:			reportstatus.sp
 *  Type:			Module
 *  Description:	Allow clients to get a report status or admins to get a
 *					detailed report.
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
static	const	Float:	COOLDOWN_TIME = 0.5;

static					g_iCvarSvCheats = 0;
static					g_iCvarSvPure = 0;
static					g_iCvarSvConsistency = 0;

static			bool:	g_bUpdateNetSettingsDelayed = false;

static			Handle:	g_hCvarMinRate = INVALID_HANDLE;
static					g_iMinRate = 0;
static			Handle:	g_hCvarMaxRate = INVALID_HANDLE;
static					g_iMaxRate = 0;
static			Handle:	g_hCvarMinUpdateRate = INVALID_HANDLE;
static					g_iMinUpdateRate = 0;
static			Handle:	g_hCvarMaxUpdateRate = INVALID_HANDLE;
static					g_iMaxUpdateRate = 0;
static			Handle:	g_hCvarMinCmdRate = INVALID_HANDLE;
static					g_iMinCmdRate = 0;
static			Handle:	g_hCvarMaxCmdRate = INVALID_HANDLE;
static					g_iMaxCmdRate = 0;
static			Handle:	g_hCvarMinInterpRatio = INVALID_HANDLE;
static			Float:	g_fMinInterpRatio = 0.0;
static			Handle:	g_hCvarMaxInterpRatio = INVALID_HANDLE;
static			Float:	g_fMaxInterpRatio = 0.0;

/*
 * ==================================================
 *                     Forwards
 * ==================================================
 */

/**
 * Plugin is starting.
 *
 * @noreturn
 */
public _ReportStatus_OnPluginStart()
{
	/* COMMAND */

	decl String:buffer[128];
	Format(buffer, sizeof(buffer), "%s_status", PLUGIN_CMD_PREFIX);
	RegConsoleCmd(buffer, _RS_ReportStatus_Command, "Prints information about server settings", FCVAR_PLUGIN);

	/* SERVER SETTINGS */

	new Handle:cvar = FindConVar("sv_cheats");
	if (cvar == INVALID_HANDLE) SetFailState("Unable to find sv_cheats convar");
	g_iCvarSvCheats = GetConVarInt(cvar);
	HookConVarChange(cvar, _RS_SvCheats_ConVarChange);

	cvar = FindConVar("sv_consistency");
	if (cvar == INVALID_HANDLE) SetFailState("Unable to find sv_consistency convar");
	g_iCvarSvConsistency = GetConVarInt(cvar);
	HookConVarChange(cvar, _RS_SvConsistency_ConVarChange);

	/* Oh you Valve, make pure look like a convar when its really a command.
	 * Which means we can't get its initial value. */
	AddCommandListener(_RS_PureListener, "sv_pure");

	/* NET SETTINGS */

	cvar = FindConVar("sv_minrate");
	if (cvar == INVALID_HANDLE) SetFailState("Unable to find sv_minrate convar");
	g_iMinRate = GetConVarInt(cvar);
	HookConVarChange(cvar, _RS_NetSetting_ConVarChange);
	g_hCvarMinRate = cvar;

	cvar = FindConVar("sv_maxrate");
	if (cvar == INVALID_HANDLE) SetFailState("Unable to find sv_maxrate convar");
	g_iMaxRate = GetConVarInt(cvar);
	HookConVarChange(cvar, _RS_NetSetting_ConVarChange);
	g_hCvarMaxRate = cvar;

	cvar = FindConVar("sv_minupdaterate");
	if (cvar == INVALID_HANDLE) SetFailState("Unable to find sv_minupdaterate convar");
	g_iMinUpdateRate = GetConVarInt(cvar);
	HookConVarChange(cvar, _RS_NetSetting_ConVarChange);
	g_hCvarMinUpdateRate = cvar;

	cvar = FindConVar("sv_maxupdaterate");
	if (cvar == INVALID_HANDLE) SetFailState("Unable to find sv_maxupdaterate convar");
	g_iMaxUpdateRate = GetConVarInt(cvar);
	HookConVarChange(cvar, _RS_NetSetting_ConVarChange);
	g_hCvarMaxUpdateRate = cvar;

	cvar = FindConVar("sv_mincmdrate");
	if (cvar == INVALID_HANDLE) SetFailState("Unable to find sv_mincmdrate convar");
	g_iMinCmdRate = GetConVarInt(cvar);
	HookConVarChange(cvar, _RS_NetSetting_ConVarChange);
	g_hCvarMinCmdRate = cvar;

	cvar = FindConVar("sv_maxcmdrate");
	if (cvar == INVALID_HANDLE) SetFailState("Unable to find sv_maxcmdrate convar");
	g_iMaxCmdRate = GetConVarInt(cvar);
	HookConVarChange(cvar, _RS_NetSetting_ConVarChange);
	g_hCvarMaxCmdRate = cvar;

	cvar = FindConVar("sv_client_min_interp_ratio");
	if (cvar == INVALID_HANDLE) SetFailState("Unable to find sv_client_min_interp_ratio convar");
	g_fMinInterpRatio = GetConVarFloat(cvar);
	HookConVarChange(cvar, _RS_NetSetting_ConVarChange);
	g_hCvarMinInterpRatio = cvar;

	cvar = FindConVar("sv_client_max_interp_ratio");
	if (cvar == INVALID_HANDLE) SetFailState("Unable to find sv_client_max_interp_ratio convar");
	g_fMaxInterpRatio = GetConVarFloat(cvar);
	HookConVarChange(cvar, _RS_NetSetting_ConVarChange);
	g_hCvarMaxInterpRatio = cvar;

	HookGlobalForward(FWD_ON_CLIENT_PUT_IN_SERVER, _RS_OnClientPutInServer);
}

/**
 * Called on client put in server.
 *
 * @param client		Client index.
 * @noreturn
 */
public _RS_OnClientPutInServer(client)
{
	if (g_bIsFake[client]) return;
	_RS_ReportStatus_Command(client, 0); // Print to client once they have joined the server
}

/**
 * On report status client command.
 *
 * @param client		Index of the client, or 0 from the server.
 * @param args			Number of arguments that were in the argument string.
 * @return				Plugin_Handled.
 */
public Action:_RS_ReportStatus_Command(client, args)
{
	/* Prevent spammage of this command */
	if (g_bCooldown[client]) return Plugin_Handled;
	g_bCooldown[client] = true;

	new const maxLen = 2048;
	decl String:result[maxLen];

	Format(result, maxLen, "\n================================================================================\n");

	Format(result, maxLen, "%sThis server is protected by %s %s SourceMod Plugin\n\n", result, PLUGIN_FULLNAME, PLUGIN_VERSION);

	Format(result, maxLen, "%sImportant Server Cvars:\n", result);
	Format(result, maxLen, "%s sv_cheats %i\n", result, g_iCvarSvCheats);
	Format(result, maxLen, "%s sv_pure %i\n", result, g_iCvarSvPure);
	Format(result, maxLen, "%s sv_consistency %i\n", result, g_iCvarSvConsistency);
	Format(result, maxLen, "%s\n", result);

	Format(result, maxLen, "%sServer Net Settings:\n", result);
	Format(result, maxLen, "%s sv_minrate %-5i                     sv_maxrate %-5i\n", result, g_iMinRate, g_iMaxRate);
	Format(result, maxLen, "%s sv_minupdaterate %-5i               sv_maxupdaterate %-5i\n", result, g_iMinUpdateRate, g_iMaxUpdateRate);
	Format(result, maxLen, "%s sv_mincmdrate %-5i                  sv_maxcmdrate %-5i\n", result, g_iMinCmdRate, g_iMaxCmdRate);
	Format(result, maxLen, "%s sv_mininterp %-5.3f                   sv_maxinterp %-5.3f\n", result, GetMinInterpAllowed(), GetMaxInterpAllowed());
	Format(result, maxLen, "%s sv_client_min_interp_ratio %-5.3f     sv_client_max_interp_ratio %-5.3f\n", result, g_fMinInterpRatio, g_fMaxInterpRatio);
	Format(result, maxLen, "%s\n", result);

	Format(result, maxLen, "%s%s Settings:\n", result, PLUGIN_FULLNAME);
	Format(result, maxLen, "%s Enabled: %b\n", result, IsPluginEnabled());
	Format(result, maxLen, "%s Anti Wallhack: %s\n", result, (IsAntiWallHackEnabled() ? "On" : "Off"));
	Format(result, maxLen, "%s Global Banlist: %s\n", result, (IsUsingGlobalBanList() ? "Yes" : "No"));
	Format(result, maxLen, "%s\n", result);

	Format(result, maxLen, "%s================================================================================\n", result);

	if (client == CLIENT_INDEX_SERVER)
	{
		PrintToServer(result);
	}
	else
	{
		PrintToConsole(client, result);
	}

	CreateTimer(COOLDOWN_TIME, _RS_Cooldown_Timer, client);
	return Plugin_Handled;
}

/**
 * Called when the cooldown interval has elapsed.
 * 
 * @param timer			Handle to the timer object.
 * @param client		Client index.
 * @return				Plugin_Stop.
 */
public Action:_RS_Cooldown_Timer(Handle:timer, any:client)
{
	g_bCooldown[client] = false;
	return Plugin_Stop;
}

/**
 * Called on sv_cheats cvar changed.
 *
 * @param convar		Handle to the convar that was changed.
 * @param oldValue		String containing the value of the convar before it was
 *						changed.
 * @param newValue		String containing the new value of the convar.
 * @noreturn
 */
public _RS_SvCheats_ConVarChange(Handle:convar, const String:oldValue[], const String:newValue[])
{
	g_iCvarSvCheats = StringToInt(newValue);
}

/**
 * Called on sv_consistency cvar changed.
 *
 * @param convar		Handle to the convar that was changed.
 * @param oldValue		String containing the value of the convar before it was
 *						changed.
 * @param newValue		String containing the new value of the convar.
 * @noreturn
 */
public _RS_SvConsistency_ConVarChange(Handle:convar, const String:oldValue[], const String:newValue[])
{
	g_iCvarSvConsistency = StringToInt(newValue);
}

/**
 * Callback for sv_pure command.
 *
 * @param client        Client, or 0 for server.
 * @param command       Command name, lower case.
 * @param argc          Argument count.
 * @return				Plugin_Stop to stop command, Plugin_Continue allow 
 *						command.
 */
public Action:_RS_PureListener(client, const String:command[], argc)
{
	if (client != CLIENT_INDEX_SERVER || argc == 0) return Plugin_Continue;
	decl String:arg[32];
	GetCmdArg(1, arg, sizeof(arg));
	if (strlen(arg) == 0) return Plugin_Continue;
	g_iCvarSvPure = StringToInt(arg);
	return Plugin_Continue;
}

/**
 * Called on a net setting cvar changed.
 *
 * @param convar		Handle to the convar that was changed.
 * @param oldValue		String containing the value of the convar before it was changed.
 * @param newValue		String containing the new value of the convar.
 * @noreturn
 */
public _RS_NetSetting_ConVarChange(Handle:convar, const String:oldValue[], const String:newValue[])
{
	UpdateNetSettingsDelayed();
}

/**
 * Called when net setting interval has elapsed.
 * 
 * @param timer			Handle to the timer object.
 * @param client		Client index.
 * @return				Plugin_Stop.
 */
public Action:_RS_NetSetting_Timer(Handle:timer, any:client)
{
	UpdateNetSettings();
	return Plugin_Stop;
}

/*
 * ==================================================
 *                    Private API
 * ==================================================
 */

/**
 * Returns whether global ban list is enabled.
 * 
 * @return				True if using global banlist, false otherwise.
 */
static bool:IsUsingGlobalBanList()
{
#if defined USE_GLOBALBANLIST
	return true;
#else
	return false;
#endif
}

/**
 * Updates the net settings with a small delay.
 *
 * @noreturn
 */
static UpdateNetSettingsDelayed()
{
	if (g_bUpdateNetSettingsDelayed) return;
	g_bUpdateNetSettingsDelayed = true;
	CreateTimer(0.1, _RS_NetSetting_Timer);
}

/**
 * Updates the net settings. Should not be called directly. Use 
 * UpdateNetSettingsDelayed.
 *
 * @noreturn
 */
static UpdateNetSettings()
{
	g_iMinRate = GetConVarInt(g_hCvarMinRate);
	g_iMaxRate = GetConVarInt(g_hCvarMaxRate);
	g_iMinUpdateRate = GetConVarInt(g_hCvarMinUpdateRate);
	g_iMaxUpdateRate = GetConVarInt(g_hCvarMaxUpdateRate);
	g_iMinCmdRate = GetConVarInt(g_hCvarMinCmdRate);
	g_iMaxCmdRate = GetConVarInt(g_hCvarMaxCmdRate);
	g_fMinInterpRatio = GetConVarFloat(g_hCvarMinInterpRatio);
	g_fMaxInterpRatio = GetConVarFloat(g_hCvarMaxInterpRatio);
	g_bUpdateNetSettingsDelayed = false;
}