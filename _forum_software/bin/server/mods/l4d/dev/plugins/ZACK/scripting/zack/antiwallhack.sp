/*
 * ============================================================================
 *
 *  File:			antiwallhack.sp
 *  Type:			Module
 *  Description:	Blocks transmittion of entities to clients that shouldn't
 *					be able to see them.
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

/* Don't include module if SDKHooks is not used */
#if !defined USE_SDKHOOKS

/* Adding plugin start function here to prevent "unknown function" error upon
 * compiling */
public _AntiWallHack_OnPluginStart() return;

/* End input of file */
#endinput

#endif

/*
 * ==================================================
 *                     Includes
 * ==================================================
 */

#include "antiwallhack/ghosts.sp"

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

static			bool:	g_bEnabled 						= false;
static			Handle:	g_hEnableCvar 					= INVALID_HANDLE;

static			Handle: g_hFwd_OnAntiWallHackEnabled	= INVALID_HANDLE;
static			Handle: g_hFwd_OnAntiWallHackDisabled	= INVALID_HANDLE;

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
public _AntiWallHack_OnPluginStart()
{
	g_hFwd_OnAntiWallHackEnabled = CreateGlobalForward("OnAntiWallHackEnabled", ET_Ignore);
	g_hFwd_OnAntiWallHackDisabled = CreateGlobalForward("OnAntiWallHackDisabled", ET_Ignore);

	decl String:buffer[10];
	IntToString(_:g_bEnabled, buffer, sizeof(buffer));
	g_hEnableCvar = CreateConVarEx("antiwallhack", buffer, "Sets whether items, weapons, commons and infected players are hidden from the survivors until they are in visible area. CPU INTENSIVE!", FCVAR_PLUGIN);
	g_bEnabled = false; // Setting this to false as the script uses it later on for storing current state of module (module always starts disabled)

	HookGlobalForward(FWD_ON_PLUGIN_ENABLED, _AWH_OnPluginEnabled);
	HookGlobalForward(FWD_ON_PLUGIN_DISABLED, _AWH_OnPluginDisabled);

	_AWH_Ghost_OnPluginStart(); // Ghost module
}

/**
 * Called on plugin enabled.
 *
 * @noreturn
 */
public _AWH_OnPluginEnabled()
{
	SetAntiWallHackState(GetConVarBool(g_hEnableCvar));
	HookConVarChange(g_hEnableCvar, _AWH_Enable_CvarChanged);
}

/**
 * Called on plugin disabled.
 *
 * @noreturn
 */
public _AWH_OnPluginDisabled()
{
	UnhookConVarChange(g_hEnableCvar, _AWH_Enable_CvarChanged);
	SetAntiWallHackState(false);
}

/**
 * Called on anti wall hack cvar changed.
 *
 * @param convar		Handle to the convar that was changed.
 * @param oldValue		String containing the value of the convar before it was changed.
 * @param newValue		String containing the new value of the convar.
 * @noreturn
 */
public _AWH_Enable_CvarChanged(Handle:convar, const String:oldValue[], const String:newValue[])
{
	SetAntiWallHackState(bool:StringToInt(newValue));
}

/*
 * ==================================================
 *                     Public API
 * ==================================================
 */

/**
 * Returns anti wallhack state.
 * 
 * @return				True if anti wallhack is enabled, false otherwise.
 */
stock bool:IsAntiWallHackEnabled() return g_bEnabled;

/*
 * ==================================================
 *                    Private API
 * ==================================================
 */

/**
 * Sets current anti wallhack state.
 * 
 * @param enabled		Whether anti wallhack is enabled.
 * @noreturn
 */
static SetAntiWallHackState(bool:enabled)
{
	if (g_bEnabled == enabled) return; // No change in state
	g_bEnabled = enabled;
	if (enabled)
	{
		Call_StartForward(g_hFwd_OnAntiWallHackEnabled);
	}
	else
	{
		Call_StartForward(g_hFwd_OnAntiWallHackDisabled);
	}
	Call_Finish();
}