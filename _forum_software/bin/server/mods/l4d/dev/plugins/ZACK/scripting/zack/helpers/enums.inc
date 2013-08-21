/*
 * ============================================================================
 *
 *  ZACK
 *
 *  File:			enums.inc
 *  Type:			Helper
 *  Description:	Contains enums available to anywhere in the plugin.
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

/* Reasons why client is bad */
enum BadClientReason
{
	/* Cvar reasons */
	CvarShouldNotExist,
	CvarShouldBeReplicated,
	CvarShouldBeWithin,
	CvarShouldNotBe,
	CvarFailedToReply,
	CvarCorrupt,

	/* Extented reasons */
	ClientFailedToAuth,
	ClientHavePlugins,
	ClientHaveIncorrectInterp,
	ClientChangedNameTooOften
}