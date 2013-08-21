/*
 * ============================================================================
 *
 *  Rotoblin
 *
 *  File:			rotoblin.nopeeking.sp
 *  Type:			Module
 *  Description:	Messes up thirdperson offset cvar on client, making the
 *					client unable to see anything but skybox when trying to.
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

// --------------------
//       Private
// --------------------

static	const	String:	THIRDPERSON_OFFSET_CVAR[]		= "c_thirdpersonshoulderoffset";
static	const	Float:	THIRDPERSON_OFFSET_MESSED_UP	= 20000.0;
static	const	Float:	CHECK_INTERVAL					= 1.0; // How often does we set the client thirdperson offset cvar

static			Handle:	g_hTimer						= INVALID_HANDLE;
static			Handle:	g_hEnable_Cvar					= INVALID_HANDLE;
static			bool:	g_bEnabled						= true;

static					g_iDebugChannel					= 0;
static	const	String:	DEBUG_CHANNEL_NAME[]			= "NoPeeking";

// **********************************************
//                   Forwards
// **********************************************

/**
 * Plugin is starting.
 *
 * @noreturn
 */
public _NoPeeking_OnPluginStart()
{
	HookPublicEvent(EVENT_ONPLUGINENABLE, _NP_OnPluginEnabled);
	HookPublicEvent(EVENT_ONPLUGINDISABLE, _NP_OnPluginDisabled);

	decl String:buffer[2];
	IntToString(int:g_bEnabled, buffer, sizeof(buffer));
	g_hEnable_Cvar = CreateConVarEx("block_thirdperson", buffer, 
		"Sets whether thirdperson shoulder mode will be blocked", 
		FCVAR_NOTIFY | FCVAR_PLUGIN);

	if (g_hEnable_Cvar == INVALID_HANDLE) ThrowError("Unable to create thirdperson block cvar!");
	AddConVarToReport(g_hEnable_Cvar); // Add to report status module

	g_iDebugChannel = DebugAddChannel(DEBUG_CHANNEL_NAME);
	DebugPrintToAllEx("Module is now setup");
}

/**
 * Plugin is now enabled.
 *
 * @noreturn
 */
public _NP_OnPluginEnabled()
{
	g_bEnabled = GetConVarBool(g_hEnable_Cvar);
	HookConVarChange(g_hEnable_Cvar, _NP_Enable_CvarChange);
	g_hTimer = CreateTimer(CHECK_INTERVAL, _NP_CheckPlayers_Timer, _, TIMER_REPEAT);
	DebugPrintToAllEx("Module is now loaded");
}

/**
 * Plugin is now disabled.
 *
 * @noreturn
 */
public _NP_OnPluginDisabled()
{
	CloseHandle(g_hTimer);
	UnhookConVarChange(g_hEnable_Cvar, _NP_Enable_CvarChange);
	DebugPrintToAllEx("Module is now unloaded");
}

/**
 * Enable cvar changed.
 *
 * @param convar		Handle to the convar that was changed.
 * @param oldValue		String containing the value of the convar before it was changed.
 * @param newValue		String containing the new value of the convar.
 * @noreturn
 */
public _NP_Enable_CvarChange(Handle:convar, const String:oldValue[], const String:newValue[])
{
	g_bEnabled = GetConVarBool(g_hEnable_Cvar);
}

/**
 * Called when timer interval has elapsed.
 * 
 * @param timer			Handle to the timer object.
 * @return				Plugin_Stop to stop the timer, any other value for
 *						default behavior.
 */
public Action:_NP_CheckPlayers_Timer(Handle:timer)
{
	if(!IsServerProcessing() || !g_bEnabled) return Plugin_Continue;

	decl client;
	if (SurvivorCount) // If any survivors
	{
		for (new i = 0; i < SurvivorCount; i++)
		{
			client = SurvivorIndex[i];
			if (IsFakeClient(client)) continue;

			// Mess up the thirdperson offset cvar, making it impossible to see anything
			ClientCommand(client, "%s %f", THIRDPERSON_OFFSET_CVAR, THIRDPERSON_OFFSET_MESSED_UP);
			DebugPrintToAllEx("Messed up client %i: \"%N\" thirdperson offset", client, client);
		}
	}

	return Plugin_Continue;
}

// **********************************************
//                 Private API
// **********************************************

/**
 * Wrapper for printing a debug message without having to define channel index
 * everytime.
 *
 * @param format		Formatting rules.
 * @param ...			Variable number of format parameters.
 * @noreturn
 */
static DebugPrintToAllEx(const String:format[], any:...)
{
	decl String:buffer[DEBUG_MESSAGE_LENGTH];
	VFormat(buffer, sizeof(buffer), format, 2);
	DebugPrintToAll(g_iDebugChannel, buffer);
}