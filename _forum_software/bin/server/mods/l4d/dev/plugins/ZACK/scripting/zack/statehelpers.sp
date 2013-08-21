/*
 * ============================================================================
 *
 *  File:			statehelpers.sp
 *  Type:			Module
 *  Description:	Forward events to state helpers.
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
 *                     Includes
 * ==================================================
 */

#include "statehelpers/forwardmanager.sp"
#include "statehelpers/pluginstate.sp"
#include "statehelpers/clientindex.sp"
#include "statehelpers/authcheck.sp"

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
public _StateHelpers_OnPluginStart()
{
	_ForwardManager_OnPluginStart();
	_PluginState_OnPluginStart();
	_ClientIndex_OnPluginStart();
	_AuthCheck_OnPluginStart();
}