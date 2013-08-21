/*
 * ============================================================================
 *
 *  Rotoblin
 *
 *  File:			rotoblin.main.sp
 *  Type:			Main
 *  Description:	Contains defines, enums, etc available to anywhere in the 
 *					plugin.
 *	Credits:		Greyscale & rhelgeby for their template "project base"
 *					(http://forums.alliedmods.net/showthread.php?t=117191).
 *
 *  Copyright (C) 2010  Mr. Zero <mrzerodk@gmail.com>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ============================================================================
 */

// **********************************************
//                 Preprocessor
// **********************************************

#pragma semicolon 1

// **********************************************
//                   Reference
// **********************************************

#define SERVER_INDEX			0 // The client index of the server
#define FIRST_CLIENT			1 // First valid client index

// The team list
#define TEAM_SPECTATOR			1
#define TEAM_SURVIVOR			2
#define TEAM_INFECTED			3

#define MAX_ENTITIES			2048 // Max number of entities l4d supports

// Plugin info
#define PLUGIN_FULLNAME			"Rotoblin"							// Used when printing the plugin name anywhere
#define PLUGIN_SHORTNAME		"rotoblin"							// Shorter version of the full name, used in file paths, and other things
#define PLUGIN_AUTHOR			"Rotoblin Team"						// Author of the plugin
#define PLUGIN_DESCRIPTION		"A competitive mod for L4D"			// Description of the plugin
#define PLUGIN_VERSION			"0.7.1"								// http://wiki.eclipse.org/Version_Numbering
#define PLUGIN_URL				"http://rotoblin.googlecode.com/"	// URL associated with the project
#define PLUGIN_CVAR_PREFIX		PLUGIN_SHORTNAME					// Prefix for cvars
#define PLUGIN_CMD_PREFIX		PLUGIN_SHORTNAME					// Prefix for cmds
#define PLUGIN_TAG				"Rotoblin"							// Tag for prints and commands
#define	PLUGIN_GAMECONFIG_FILE	PLUGIN_SHORTNAME					// Name of gameconfig file

// **********************************************
//                    Includes
// **********************************************

// Globals
#include <sourcemod.inc>
#include <sdktools.inc>
#include "rotoblin.includes/left4downtown.inc"
#include "rotoblin.includes/sdkhooks.inc"
#include "rotoblin.includes/socket.inc"

// Helpers
#include "rotoblin.helpers/debug.inc"
#include "rotoblin.helpers/eventmanager.inc"
#include "rotoblin.helpers/cmdmanager.inc"
#include "rotoblin.helpers/clientindexes.inc"
#include "rotoblin.helpers/flowfunctions.inc"
#include "rotoblin.helpers/mapinfo.inc"
#include "rotoblin.helpers/tankmanager.inc"
#include "rotoblin.helpers/wrappers.inc"

// Modules
#include "rotoblin.2vs2mod.sp"
#include "rotoblin.autoupdate.sp"
#include "rotoblin.despawninfected.sp"
#include "rotoblin.ghosttank.sp"
#include "rotoblin.hdrcheck.sp"
#include "rotoblin.healthcontrol.sp"
#include "rotoblin.hordecontrol.sp"
#include "rotoblin.infectedexploitfixes.sp"
#include "rotoblin.infectedfrustration.sp"
#include "rotoblin.nopeeking.sp"
#include "rotoblin.nopropfad�ng.sp"
#include "rotoblin.pause.sp"
#include "rotoblin.ratehack.sp"
#include "rotoblin.reportstatus.sp"
#include "rotoblin.specboss.sp"
#include "rotoblin.unreservelobby.sp"
#include "rotoblin.weaponcontrol.sp"

// **********************************************
//					  Forwards
// **********************************************

public Plugin:myinfo = 
{
	name = PLUGIN_FULLNAME,
	author = PLUGIN_AUTHOR,
	description = PLUGIN_DESCRIPTION,
	version = PLUGIN_VERSION,
	url = PLUGIN_URL
}

/**
 * On plugin start extended. Called by the event manager once its done setting up.
 *
 * @noreturn
 */
public OnPluginStartEx()
{
	DebugPrintToAll(DEBUG_CHANNEL_GENERAL, "[Main] Setting up...");

	decl String:buffer[128];
	Format(buffer, sizeof(buffer), "%s version", PLUGIN_FULLNAME);
	new Handle:convar = CreateConVarEx("version", PLUGIN_VERSION, buffer, FCVAR_PLUGIN | FCVAR_NOTIFY);
	SetConVarString(convar, PLUGIN_VERSION);

	if (GetMaxEntities() > MAX_ENTITIES) // Ensure that our MAX_ENTITIES const is updated
	{
		ThrowError("Max entities exceeded, %d. Plugin needs a recompile with a updated max entity const, current value %d.", GetMaxEntities(), MAX_ENTITIES);
	}

	/* Initial setup of modules after event manager is done setting up.
	 * To disable certain module, simply comment out the line. */

	_H_TankManager_OnPluginStart();
	_H_ClientIndexes_OnPluginStart();
	_H_CommandManager_OnPluginStart();

	_AutoUpdate_OnPluginStart();
	_HealthControl_OnPluginStart();
	_WeaponControl_OnPluginStart();
	_GhostTank_OnPluginStart();
	_SpectateBoss_OnPluginStart();
	_InfFrustration_OnPluginStart();
	_RateHack_OnPluginStart();
	_Pause_OnPluginStart();
	_InfExloitFixes_OnPluginStart();
	_DespawnInfected_OnPluginStart();
	_HordeControl_OnPluginStart();
	_2vs2Mod_OnPluginStart();
	_NoPeeking_OnPluginStart();
	_NoPropFading_OnPluginStart();
	_ReportStatus_OnPluginStart();
	_HDRCheck_OnPluginStart();
	_UnreserveLobby_OnPluginStart();

	// Create cvar for control plugin state
	Format(buffer, sizeof(buffer), "Sets whether %s is enabled", PLUGIN_FULLNAME);
	convar = CreateConVarEx("enable", "0", buffer, FCVAR_PLUGIN | FCVAR_NOTIFY);

	if (convar == INVALID_HANDLE) ThrowError("Unable to create main enable cvar!");
	if (GetConVarBool(convar) && !IsDedicatedServer())
	{
		SetConVarBool(convar, false);
		DebugPrintToAll(DEBUG_CHANNEL_GENERAL, "[Main] Unable to enable rotoblin, running on a listen server!");
	}
	else
	{
		SetPluginState(GetConVarBool(convar));
	}

	HookConVarChange(convar, _Main_Enable_CvarChange);
	DebugPrintToAll(DEBUG_CHANNEL_GENERAL, "[Main] Done setting up!");
}

/**
 * Enable cvar changed.
 *
 * @param convar		Handle to the convar that was changed.
 * @param oldValue		String containing the value of the convar before it was changed.
 * @param newValue		String containing the new value of the convar.
 * @noreturn
 */
public _Main_Enable_CvarChange(Handle:convar, const String:oldValue[], const String:newValue[])
{
	DebugPrintToAll(DEBUG_CHANNEL_GENERAL, "[Main] Enable cvar was changed. Old value %s, new value %s", oldValue, newValue);

	if (GetConVarBool(convar) && !IsDedicatedServer())
	{
		SetConVarBool(convar, false);
		DebugPrintToAll(DEBUG_CHANNEL_GENERAL, "[Main] Unable to enable rotoblin, running on a listen server!");
		PrintToChatAll("[%s] Unable to enable %s! %s only support dedicated servers", PLUGIN_TAG, PLUGIN_FULLNAME, PLUGIN_FULLNAME);
		return;
	}

	SetPluginState(bool:StringToInt(newValue));
}