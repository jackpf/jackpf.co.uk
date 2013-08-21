@echo off

echo ---------------------------------------------
echo UNINSTALLING STEAMPROFILES
echo ---------------------------------------------

rmdir /s /q "\Program Files\SteamProfiles"

del "%USERPROFILE%\Desktop\SteamProfiles.lnk"

pause
