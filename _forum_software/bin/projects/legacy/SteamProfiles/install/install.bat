@echo off

echo ---------------------------------------------
echo INSTALLING STEAMPROFILES
echo ---------------------------------------------

mkdir "\Program Files\SteamProfiles"
mkdir "\Program Files\SteamProfiles\resources"

cd ..\

copy ".\bin\Release\SteamProfiles.exe" "\Program Files\SteamProfiles\SteamProfiles.exe"
copy ".\resources\Steam\bin\Release\Steam.dll" "\Program Files\SteamProfiles\resources\Steam.dll"
copy ".\resources\Steam\bin\Release\SteamGUI.dll" "\Program Files\SteamProfiles\resources\SteamGUI.dll"
copy ".\resources\steam.bmp" "\Program Files\SteamProfiles\resources\steam.bmp"
copy ".\SteamProfiles.ini" "\Program Files\SteamProfiles\SteamProfiles.ini"
copy ".\install\SteamProfiles.lnk" "%USERPROFILE%\Desktop\SteamProfiles.lnk"

pause
