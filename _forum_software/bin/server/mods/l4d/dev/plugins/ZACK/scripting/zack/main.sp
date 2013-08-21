/*
 * ============================================================================
 *
 *  File:			main.sp
 *  Type:			Main
 *  Description:	Main script for compiling and plugin reference.
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

#pragma semicolon 1 // Require semicolon for each line
#pragma tabsize 4 // Tab size is 4 spaces

/* Alright I'm not gonna lie, the global ban list is "forced" in the same sense
 * VAC is forced. The problem is as Kigen often noticed; when people are 
 * offered the choice of whether install socket extension and the plugin, or
 * just the plugin, people would skip the socket extension just to save time.
 * While I don't mind people laziness (fuck I'm lazy as hell), I do mind that
 * people skip options that are suppose to help the community as a whole.
 * However I will not try to stop anyone that wish to disable the global ban 
 * list.
 * Comment this line below to compile with the global ban list disabled. */
#define USE_GLOBALBANLIST

/* The socket extension in ZACK is used in the auto update module which 
 * provides update information and updates ZACK's global ban list.
 * Comment this line below to compile without the socket extension. */
#define USE_SOCKET

/* The SDKHook extension is an awesome extension that I highly recommend.
 * Some modules will be disabled, such as anti wall hack.
 * Comment this line below to compile without the SDKHooks extension. */
#define USE_SDKHOOKS

#define PLUGIN_FULLNAME			"ZACK"									// Used when printing the plugin name anywhere
#define PLUGIN_SHORTNAME		"zack"									// Shorter version of the full name, used in file paths, and other things
#define PLUGIN_AUTHOR			"Mr. Zero & suprep"						// Author of the plugin
#define PLUGIN_DESCRIPTION		"A anti-cheat server plugin for L4D(2)"	// Description of the plugin
#define PLUGIN_VERSION			"0.4.5"									// http://wiki.eclipse.org/Version_Numbering
#define PLUGIN_URL				"zack.googlecode.com"					// URL associated with the project
#define PLUGIN_CVAR_PREFIX		PLUGIN_SHORTNAME						// Prefix for cvars
#define PLUGIN_CMD_PREFIX		PLUGIN_SHORTNAME						// Prefix for cmds
#define PLUGIN_TAG				"ZACK"									// Tag for prints and commands
#define PLUGIN_LIBRARY			"zack"									// Library name of plugin

/*
 * ==================================================
 *                     Variables
 * ==================================================
 */

/*
 * --------------------
 *       Public
 * --------------------
 */

/* Using global variables to check client connections instead of natives, as
 * this is cheaper to use in often repeating loops */
new				bool:	g_bIsConnected[MAXPLAYERS + 1] 	= {false};
new				bool:	g_bIsInGame[MAXPLAYERS + 1]		= {false};
new				bool:	g_bIsFake[MAXPLAYERS + 1]		= {false};
new				bool:	g_bIsAuth[MAXPLAYERS + 1]		= {false};

/*
 * --------------------
 *       Private
 * --------------------
 */

static					g_iL4DGame						= 0;

/*
 * ==================================================
 *                     Includes
 * ==================================================
 */

/*
 * --------------------
 *       Globals
 * --------------------
 */
#include <sourcemod>
#include <sdktools>
#include <sourcebans>

#define REQUIRE_EXTENSIONS

#if defined USE_SDKHOOKS
#include <sdkhooks>
#endif

#if defined USE_SOCKET
#include <socket>
#elseif defined USE_GLOBALBANLIST // If not using socket but using global ban list
#undef USE_GLOBALBANLIST // Undefine global ban list as well
#endif

#undef REQUIRE_EXTENSIONS

/*
 * --------------------
 *       Modules
 * --------------------
 */
#include "helpers.sp"
#include "statehelpers.sp"
#include "badclient.sp"
#include "anticonnectspam.sp"
#include "antiwallhack.sp"
#include "speccrashing.sp"
#include "ratehack.sp"
#include "infectedscratch.sp"
#include "disconnectmsg.sp"
#include "rconfix.sp"
#include "halfconnectcmd.sp"
#include "cheatcommands.sp"
#include "reportstatus.sp"
#include "namechange.sp"
//#include "cvarchecking.sp"
#include "cheatblock.sp"
#include "netinfo.sp"

/* L4D specific modules */
#include "infectedtelespawn.sp"
#include "infectedghostduck.sp"
#include "pumpswap.sp"

/* L4D2 specific modules */
#include "blockpistolspam.sp"

/*
 * ==================================================
 *                     Forwards
 * ==================================================
 */

/**
 * Plugin public information.
 */
public Plugin:myinfo = 
{
	name		= PLUGIN_FULLNAME,
	author		= PLUGIN_AUTHOR,
	description	= PLUGIN_DESCRIPTION,
	version		= PLUGIN_VERSION,
	url			= PLUGIN_URL
}

/**
 * Called on pre plugin start.
 *
 * @param myself		Handle to the plugin.
 * @param late			Whether or not the plugin was loaded "late" (after map load).
 * @param error			Error message buffer in case load failed.
 * @param err_max		Maximum number of characters for error message buffer.
 * @return				APLRes_Success for load success, APLRes_Failure or APLRes_SilentFailure otherwise.
 */
public APLRes:AskPluginLoad2(Handle:myself, bool:late, String:error[], err_max)
{
	if (LibraryExists(PLUGIN_LIBRARY))
	{
		strcopy(error, err_max, "Plugin is already loaded");
		return APLRes_SilentFailure; // Plugin is already loaded, return
	}

	if (!IsDedicatedServer())
	{
		strcopy(error, err_max, "Plugin only support dedicated servers");
		return APLRes_Failure; // Plugin does not support client listen servers, return
	}

	decl String:gameName[32];
	GetGameFolderName(gameName, sizeof(gameName));

	if (StrEqual(gameName, "left4dead", false))
	{
		g_iL4DGame = 1;
	}
	else if (StrEqual(gameName, "left4dead2", false))
	{
		g_iL4DGame = 2;
	}
	else
	{
		strcopy(error, err_max, "Plugin only support Left 4 Dead and Left 4 Dead 2");
		return APLRes_Failure; // Plugin does not support this game, return
	}

	RegPluginLibrary(PLUGIN_LIBRARY); // Add library
	return APLRes_Success; // Allow load
}

/**
 * Called on plugin start.
 *
 * @noreturn
 */
public OnPluginStart()
{
	/* Set up public cvar for tracking */
	decl String:buffer[64];
	Format(buffer, sizeof(buffer), "%s Version", PLUGIN_FULLNAME);
	new Handle:cvar = CreateConVarEx("version", PLUGIN_VERSION, buffer, FCVAR_PLUGIN | FCVAR_NOTIFY | FCVAR_DONTRECORD);
	SetConVarString(cvar, PLUGIN_VERSION);

	/* Load translations */
	if (!IsTranslationValid("core.phrases")) SetFailState("Missing core.phrases translation file!");
	LoadTranslations("core.phrases");

	if (!IsTranslationValid("common.phrases")) SetFailState("Missing common.phrases translation file!");
	LoadTranslations("common.phrases");

	decl String:file[128];
	Format(file, sizeof(file), "%s.phrases", PLUGIN_SHORTNAME);
	if (!IsTranslationValid(file)) SetFailState("Missing %s translation file!", file);
	LoadTranslations(file);

	/* Modules */
	_StateHelpers_OnPluginStart();
	_AntiConnectSpam_OnPluginStart();
	_AntiWallHack_OnPluginStart();
	_BadClient_OnPluginStart();
	_SpecCrashing_OnPluginStart();
	_RateHack_OnPluginStart();
	_InfScratch_OnPluginStart();
	_DisconnectMsg_OnPluginStart();
	_RconFix_OnPluginStart();
	_HalfConnectCmd_OnPluginStart();
	_CheatCommands_OnPluginStart();
	_ReportStatus_OnPluginStart();
	_NameChange_OnPluginStart();
	//_CvarChecking_OnPluginStart();
	_CheatBlock_OnPluginStart();
	_NetInfo_OnPluginStart();

	/* L4D specific modules */
	_InfTelespawn_OnPluginStart();
	_InfGhostDuck_OnPluginStart();
	_PumpSwap_OnPluginStart();

	/* L4D2 specific modules */
	_BlockPistolSpam_OnPluginStart();

	/* Auto exec config */
	AutoExecConfig(true, PLUGIN_SHORTNAME);
}

/**
 * Called on plugin end.
 *
 * @noreturn
 */
public OnPluginEnd()
{
	/* Modules */
	_RconFix_OnPluginEnd();
}

/*
 * ==================================================
 *                     Public API
 * ==================================================
 */

/**
 * Returns whether or not the game is Left 4 Dead.
 *
 * @return				True if game is Left 4 Dead, false otherwise.
 */
stock bool:IsGameLeft4Dead()
{
	return g_iL4DGame == 1;
}

/**
 * Returns whether or not the game is Left 4 Dead 2.
 *
 * @return				True if game is Left 4 Dead 2, false otherwise.
 */
stock bool:IsGameLeft4Dead2()
{
	return g_iL4DGame == 2;
}