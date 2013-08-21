/*
 * ============================================================================
 *
 *  File:			cheatcommands.sp
 *  Type:			Module
 *  Description:	Applies cheat flags to commands read from keyfile.
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

static			bool:	g_bIsCurrentlyUsingFile = false; // Used for detecting when cheat commands file is being read from and shouldn't be updated
static	const	String:	CHEAT_COMMANDS_FILE[] 	= "cheatcommands.cfg";

static	const			CR 						= '\r';
static	const			LF 						= '\n';
static	const			TAB						= '\t';
static	const			SPACE					= ' ';
static	const			COMMENT					= '/';
static	const			SECTION					= '<';

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
public _CheatCommands_OnPluginStart()
{
	HookGlobalForward(FWD_ON_PLUGIN_ENABLED, _ChC_OnPluginEnabled);
	HookGlobalForward(FWD_ON_PLUGIN_DISABLED, _ChC_OnPluginDisabled);
}

/**
 * Called on plugin enabled.
 *
 * @noreturn
 */
public _ChC_OnPluginEnabled()
{
	HookGlobalForward(FWD_ON_MAP_START, _ChC_OnMapStart);
}

/**
 * Called on plugin disabled.
 *
 * @noreturn
 */
public _ChC_OnPluginDisabled()
{
	UnhookGlobalForward(FWD_ON_MAP_START, _ChC_OnMapStart);
}

/**
 * Called on map start.
 *
 * @noreturn
 */
public _ChC_OnMapStart()
{
	new Handle:cheatArray = ReadCheatCommandsToArray();
	new arraySize = GetArraySize(cheatArray);
	if (arraySize == 0)
	{
		CloseHandle(cheatArray);
		return;
	}

	LogPluginMessage("Applying cheat flag to commands...");
	decl String:command[128];
	new counter;
	for (new i = 0; i < arraySize; i++)
	{
		GetArrayString(cheatArray, i, command, 128);
		if (strlen(command) == 0) continue;
		if (GetCommandFlags(command) == INVALID_FCVAR_FLAGS)
		{
			LogPluginMessage("Unable to apply cheat to command \"%s\". Command not found", command);
		}
		else if (GetCommandFlags(command) & FCVAR_CHEAT)
		{
			LogPluginMessage("Command \"%s\" already have cheat flag applied", command);
		}
		else
		{
			SetCommandFlags(command, GetCommandFlags(command) & FCVAR_CHEAT);
			LogPluginMessage("Applied cheat flag to command \"%s\"", command);
			counter++;
		}
	}
	LogPluginMessage("Done! Applied cheat flag to %i commands", counter);
}

/*
 * ==================================================
 *                     Public API
 * ==================================================
 */

/**
 * Returns whether cheat commands module is currently using the cheat commands
 * file.
 * This function is for auto update, to prevent it from updating the file while
 * cheat commands are reading from it.
 *
 * @return				True if using file, false otherwise.
 */
stock bool:IsCheatCommandsUsingFile()
{
	return g_bIsCurrentlyUsingFile;
}

/*
 * ==================================================
 *                    Private API
 * ==================================================
 */

/**
 * Reads cheat commands to array.
 *
 * @return				Handle to array containing cheat commands.
 */
static Handle:ReadCheatCommandsToArray()
{
	static const MAX_CVAR_NAME_LENGTH = 128;
	new Handle:array = CreateArray(MAX_CVAR_NAME_LENGTH);

	decl String:path[PLATFORM_MAX_PATH];
	BuildPath(Path_SM, path, PLATFORM_MAX_PATH, "configs\%s\%s", PLUGIN_SHORTNAME, CHEAT_COMMANDS_FILE);
	new Handle:file = OpenFile(path, "r");
	if (file == INVALID_HANDLE) return array;
	g_bIsCurrentlyUsingFile = true;

	decl String:buffer[MAX_CVAR_NAME_LENGTH], char, bufferLen;
	while (!IsEndOfFile(file) && ReadFileLine(file, buffer, MAX_CVAR_NAME_LENGTH))
	{
		/* Check first char to skip on comments, new lines and so on */
		char = buffer[0];
		if (char == 0 ||
			char == CR ||
			char == LF ||
			char == TAB ||
			char == SPACE ||
			char == COMMENT ||
			char == SECTION)
			continue;

		/* Strip out in-line comments */
		bufferLen = strlen(buffer);
		for (new i = 1; i < bufferLen; i++)
		{
			char = buffer[i];
			if (char == 0 ||
				char == CR ||
				char == LF ||
				char == TAB ||
				char == SPACE ||
				char == COMMENT ||
				char == SECTION)
			{
				buffer[i] = 0;
				break;
			}
		}

		PushArrayString(array, buffer);
	}

	CloseHandle(file);
	g_bIsCurrentlyUsingFile = false;
	return array;
}