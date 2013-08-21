@echo off

echo ---------------------------------------------
echo INSTALLING STEAMPROFILES
echo ---------------------------------------------

mkdir "\Program Files\SteamProfiles"
mkdir "\Program Files\SteamProfiles\resources"
mkdir "\Program Files\SteamProfiles\resources\GUI"

cd ..\

copy ".\bin\Release\SteamProfiles.exe" "\Program Files\SteamProfiles\SteamProfiles.exe"
copy ".\resources\Steam\bin\Release\Steam.dll" "\Program Files\SteamProfiles\resources\Steam.dll"
copy ".\resources\SteamGUI\bin\Release\SteamGUI.dll" "\Program Files\SteamProfiles\resources\SteamGUI.dll"
copy ".\resources\GUI\SteamProfilesGUI.exe" "\Program Files\SteamProfiles\resources\GUI\SteamProfilesGUI.exe"
copy ".\resources\GUI\libgcc_s_dw2-1.dll" "\Program Files\SteamProfiles\resources\GUI\libgcc_s_dw2-1.dll"
copy ".\resources\GUI\mingwm10.dll" "\Program Files\SteamProfiles\resources\GUI\mingwm10.dll"
copy ".\resources\GUI\QtCore4.dll" "\Program Files\SteamProfiles\resources\GUI\QtCore4.dll"
copy ".\resources\GUI\QtGui4.dll" "\Program Files\SteamProfiles\resources\GUI\QtGui4.dll"
copy ".\resources\steam.bmp" "\Program Files\SteamProfiles\resources\steam.bmp"
copy ".\SteamProfiles.ini" "\Program Files\SteamProfiles\SteamProfiles.ini"
copy ".\install\SteamProfiles.lnk" "%USERPROFILE%\Desktop\SteamProfiles.lnk"

echo Successfuly installed.
echo SteamProfiles.ini will now open, located at C:\Program Files\SteamProfiles\SteamProfiles.ini. Please edit this file as instructed in the readme.
start "Notepad" "notepad.exe" "C:\Program Files\SteamProfiles\SteamProfiles.ini"
pause
