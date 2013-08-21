@echo off

echo ---------------------------------------------
echo UNINSTALLING STEAMPROFILES
echo ---------------------------------------------

rmdir /s /q "\Program Files\SteamProfiles"
echo 	1 file(s) removed.

del "%USERPROFILE%\Desktop\SteamProfiles.lnk"

echo 	1 folder(s) removed.

echo Succesfully uninstalled.
pause
