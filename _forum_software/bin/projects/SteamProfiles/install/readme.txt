HOW TO INSTALL
--------------

	1. Double click the "install.bat" file. Take note of what is displayed; it should show a bunch of files being copied, and report a successful installation at the end. SteamProfiles.ini should then open for editing. If you have Steam installed, skip to step 3.
	2. To install Steam, go to http://store.steampowered.com/about/ and download the executable. Follow the instructions.
	3. To set up your INI file which contains your Steam profiles, you will need to change "SteamProfiles.ini", which will be located in "C:\Program Files\SteamProfiles\SteamProfiles.ini" (or whichever drive you are currently working on).
	   Double click this file, or open it in notepad. The first line you may need to edit, will be "File = C:\Program Files\Steam\steam.exe". This is where Steam installs by default, but if you have installed somewhere else, you should edit this line to point to where it is installed.
	   The next, is "SteamID = STEAM_". You will need to replace "STEAM_" with your steamid. You can find this out many different ways, the easiest probably method is probably to copy your profile url/friendid into this website http://www.noobsticks.com/home.php?pageid=lookup, and it will report your steamid. You may leave this blank to not have information from your profile displayed.
	   The Xfire module is currently disabled, and enabling it will crash the program. This is a problem with Xfire's API, and cannot be resolved on my end. So for now, you should ignore this part of the file.
	   Finally, to store your profiles, navigate to the line "[SteamProfiles]". Under that, you can list your Steam credentials in the format "x = username password", where x is an incremented integer (starting from 1), and your username and password follow, seperated by a space.
	   For example:
			1 = firstaccount firstpassword
			2 = secondaccount secondpassword
			...and so on.
	   Optionally, a comment may be appended (for differentiating accounts, or wanting something to remember about them). The syntax for this, is the same, but appended with a comment in quotes. For example:
			1 = firstaccount firstpassword "this is a comment, it may contain spaces if it's in quotes"
			2 = secondaccount secondpassword anothercommentbutnoquotes
			...and so on.
	
	To uninstall:
	   From the source you used to install, find "uninstall.bat" in the same directory as "install.bat". Simply run this program, and (hopefully) see a report of a successful uninstallation.