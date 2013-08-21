/*
 * ============================================================================
 *
 *  File:			pluginstate.sp
 *  Type:			State helper
 *  Description:	Provides a simple on/off cvar for the plugin and forwards
 *					the event.
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

static			Handle:	g_hEnableCvar			= INVALID_HANDLE;
static			bool:	g_bIsPluginEnabled 		= false;
static			Handle: g_hFwd_OnPluginEnabled	= INVALID_HANDLE;
static			Handle: g_hFwd_OnPluginDisabled	= INVALID_HANDLE;

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
public _PluginState_OnPluginStart()
{
	g_hFwd_OnPluginEnabled = CreateGlobalForward("OnPluginEnabled", ET_Ignore);
	g_hFwd_OnPluginDisabled = CreateGlobalForward("OnPluginDisabled", ET_Ignore);

	/* Cvar creation */
	decl String:buffer[128];
	Format(buffer, sizeof(buffer), "Sets whether %s is enabled", PLUGIN_FULLNAME);
	new Handle:convar = CreateConVarEx("enable", "0", buffer, FCVAR_PLUGIN | FCVAR_NOTIFY);
	if (convar == INVALID_HANDLE) SetFailState("Unable to create main enable cvar!");
	g_hEnableCvar = convar;

	HookGlobalForward(FWD_ON_ALL_PLUGINS_LOADED, _PS_OnAllPluginsLoaded);
}

/**
 * Called on all plugins loaded.
 *
 * @noreturn
 */
public _PS_OnAllPluginsLoaded()
{
	SetPluginState(GetConVarBool(g_hEnableCvar));
	HookConVarChange(g_hEnableCvar, _PS_Enable_CvarChange);
}

/**
 * Called on enable cvar changed.
 *
 * @param convar		Handle to the convar that was changed.
 * @param oldValue		String containing the value of the convar before it was changed.
 * @param newValue		String containing the new value of the convar.
 * @noreturn
 */
public _PS_Enable_CvarChange(Handle:convar, const String:oldValue[], const String:newValue[])
{
	SetPluginState(GetConVarBool(convar));
}

/*
 * ==================================================
 *                     Public API
 * ==================================================
 */

/**
 * Returns plugin state.
 * 
 * @return				True if plugin is enabled, false otherwise.
 */
stock bool:IsPluginEnabled() return g_bIsPluginEnabled;

/*
 * ==================================================
 *                    Private API
 * ==================================================
 */

/**
 * Sets current plugin state.
 * 
 * @param enabled		Whether the plugin is enabled.
 * @noreturn
 */
static SetPluginState(bool:enabled)
{
	if (g_bIsPluginEnabled == enabled) return; // No change in plugin state, return
	g_bIsPluginEnabled = enabled;

	if (enabled)
	{
		Call_StartForward(g_hFwd_OnPluginEnabled);
	}
	else
	{
		Call_StartForward(g_hFwd_OnPluginDisabled);
	}
	Call_Finish();
}