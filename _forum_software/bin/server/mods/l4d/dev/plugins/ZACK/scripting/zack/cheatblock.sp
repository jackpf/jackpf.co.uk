/*
 * ============================================================================
 *
 *  File:			cheatblock.sp
 *  Type:			Module
 *  Description:	Blocks sv_cheats while ZACK is enabled.
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

static			Handle:	g_hCheats = INVALID_HANDLE;

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
public _CheatBlock_OnPluginStart()
{
	HookGlobalForward(FWD_ON_PLUGIN_ENABLED, _CB_OnPluginEnabled);
	HookGlobalForward(FWD_ON_PLUGIN_DISABLED, _CB_OnPluginDisabled);
}

/**
 * Called on plugin enabled.
 *
 * @noreturn
 */
public _CB_OnPluginEnabled()
{
	g_hCheats = FindConVar("sv_cheats");
	if (g_hCheats == INVALID_HANDLE) SetFailState("Unable to find sv_cheats convar!");

	SetConVarInt(g_hCheats, 0);
	HookConVarChange(g_hCheats, _CB_SvCheats_ConVarChange);
}

/**
 * Called on plugin disabled.
 *
 * @noreturn
 */
public _CB_OnPluginDisabled()
{
	UnhookConVarChange(g_hCheats, _CB_SvCheats_ConVarChange);
}

/**
 * Called on sv_cheats cvar changed.
 *
 * @param convar		Handle to the convar that was changed.
 * @param oldValue		String containing the value of the convar before it was changed.
 * @param newValue		String containing the new value of the convar.
 * @noreturn
 */
public _CB_SvCheats_ConVarChange(Handle:convar, const String:oldValue[], const String:newValue[])
{
	SetConVarInt(convar, 0);
}