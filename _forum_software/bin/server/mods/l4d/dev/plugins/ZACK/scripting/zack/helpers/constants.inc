/*
 * ============================================================================
 *
 *  ZACK
 *
 *  File:			constants.inc
 *  Type:			Helper
 *  Description:	Contains defines available to anywhere in the plugin.
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

#define CLIENT_INDEX_SERVER	0 			// Server client index
#define CLIENT_INDEX_FIRST 	1 			// First valid client index
#define CLIENT_INDEX_LAST	MaxClients 	// Last valid client index

#define MAXENTITIES			2048 		// Max number of networked entities

#define TEAM_SPECTATOR		1			// Team index of spectators
#define TEAM_SURVIVOR		2			// Team index of survivors
#define TEAM_INFECTED		3			// Team index of infected

#define MAX_KICK_MSG_LENGTH 256			// Used for kick messages