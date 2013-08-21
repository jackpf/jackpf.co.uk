/*
 * ============================================================================
 *
 *  ZACK
 *
 *  File:			macros.inc
 *  Type:			Helper
 *  Description:	Contains macros available to anywhere in the plugin.
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

#define FOR_EACH_CLIENT(%1)													\
	for (new %1 = CLIENT_INDEX_FIRST; %1 <= CLIENT_INDEX_LAST; %1++)

#define FOR_EACH_CLIENT_COND(%1,%2)											\
	for (new %1 = CLIENT_INDEX_FIRST; %1 <= CLIENT_INDEX_LAST; %1++)		\
		if(%2)

#define FOR_EACH_HUMAN(%1)													\
	FOR_EACH_CLIENT_COND(%1, (g_bIsInGame[%1] && !g_bIsFake[%1]))

#define FOR_EACH_ALIVE_HUMAN(%1)											\
	FOR_EACH_CLIENT_COND(%1, (g_bIsInGame[%1] && !g_bIsFake[%1] && GetClientHealth(%1) > 0 && IsPlayerAlive(%1)))

#define FOR_EACH_CLIENT_IN_GAME(%1)											\
	FOR_EACH_CLIENT_COND(%1, g_bIsInGame[%1])

#define FOR_EACH_ALIVE_CLIENT(%1)											\
	FOR_EACH_CLIENT_COND(%1, (g_bIsInGame[%1] && GetClientHealth(%1) > 0 && IsPlayerAlive(%1)))

#define FOR_EACH_HUMAN_ON_TEAM(%1,%2)										\
	FOR_EACH_HUMAN(%1)														\
		if(GetClientTeam(%1) == %2)

#define FOR_EACH_ALIVE_HUMAN_ON_TEAM(%1,%2)									\
	FOR_EACH_ALIVE_HUMAN(%1)												\
		if(GetClientTeam(%1) == %2)

#define FOR_EACH_CLIENT_ON_TEAM(%1,%2)										\
	FOR_EACH_CLIENT_IN_GAME(%1)												\
		if(GetClientTeam(%1) == %2)

#define FOR_EACH_ALIVE_CLIENT_ON_TEAM(%1,%2)								\
	FOR_EACH_ALIVE_CLIENT(%1)												\
		if(GetClientTeam(%1) == %2)

#define FOR_EACH_SURVIVOR_HUMAN(%1)											\
	FOR_EACH_HUMAN_ON_TEAM(%1, TEAM_SURVIVOR)

#define FOR_EACH_ALIVE_SURVIVOR_HUMAN(%1)									\
	FOR_EACH_ALIVE_HUMAN_ON_TEAM(%1, TEAM_SURVIVOR)

#define FOR_EACH_SURVIVOR(%1)												\
	FOR_EACH_CLIENT_ON_TEAM(%1, TEAM_SURVIVOR)

#define FOR_EACH_ALIVE_SURVIVOR(%1)											\
	FOR_EACH_ALIVE_CLIENT_ON_TEAM(%1, TEAM_SURVIVOR)

#define FOR_EACH_INFECTED_HUMAN(%1)											\
	FOR_EACH_HUMAN_ON_TEAM(%1, TEAM_INFECTED)

#define FOR_EACH_ALIVE_INFECTED_HUMAN(%1)									\
	FOR_EACH_ALIVE_HUMAN_ON_TEAM(%1, TEAM_INFECTED)

#define FOR_EACH_INFECTED(%1)												\
	FOR_EACH_CLIENT_ON_TEAM(%1, TEAM_INFECTED)

#define FOR_EACH_ALIVE_INFECTED(%1)											\
	FOR_EACH_ALIVE_CLIENT_ON_TEAM(%1, TEAM_INFECTED)

#define FOR_EACH_SPECTATOR(%1)												\
	FOR_EACH_HUMAN_ON_TEAM(%1, TEAM_SPECTATOR)