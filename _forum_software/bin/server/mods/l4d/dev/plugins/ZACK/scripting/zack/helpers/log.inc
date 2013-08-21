/*
 * ============================================================================
 *
 *  ZACK
 *
 *  File:			log.sp
 *  Type:			Helper
 *  Description:	Handles loging to ZACK's own log file.
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
 *                     Public API
 * ==================================================
 */

/**
 * Logs a message to plugin private log.
 *
 * @param format		String format.
 * @param ...			Format arguments.
 * @noreturn
 */
stock LogPluginMessage(const String:format[], any:...)
{
	decl String:file[1024];
	if (!CreateLogFile(file, sizeof(file))) 
	{
		LogError("Failed to create plugin log! Path: \"%s\"", file);
		return;
	}

	decl String:buffer[2048];
	VFormat(buffer, sizeof(buffer), format, 2);

	LogToFile(file, buffer);
}

/*
 * ==================================================
 *                    Private API
 * ==================================================
 */

/**
 * Creates file path to plugin log file.
 *
 * @param file			String to store file path in.
 * @param fileSize		Size of file path.
 * @return				True if file found or created, false if could not be created.
 */
static bool:CreateLogFile(String:file[], fileSize)
{
	decl String:time[64];
	FormatTime(time, sizeof(time), "%Y%m%d");
	BuildPath(Path_SM, file, fileSize, "logs/%s_L%s.log", PLUGIN_SHORTNAME, time);

	new Handle:log = OpenFile(file, "r");
	if (log == INVALID_HANDLE) // If file couldn't be open for reading
	{
		log = OpenFile(file, "w");
		if (log == INVALID_HANDLE) return false; // If file couldn't be written to
	}
	CloseHandle(log);
	return true;
}