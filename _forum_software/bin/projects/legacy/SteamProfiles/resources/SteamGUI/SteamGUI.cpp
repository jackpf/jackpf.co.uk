/**
    SteamGUI.cpp
        dll source
 **/

#include "SteamGUI.hpp"

extern "C"
{
    DLL_EXPORT int GetProfiles(Steam::Steam::SteamProfileArray &Profiles)
    {
        Steam::Steam Steam;
        return Steam.GetProfiles(Profiles);
    }
    DLL_EXPORT void RunSteamProfile(int ProfileIndex)
    {
        char SteamParameters[64];
        sprintf(SteamParameters, "-profileindex %i", ProfileIndex);

        ShellExecute(0,
                     "open",                //operation to perform
                     "SteamProfiles.exe",   //application name
                     SteamParameters,       //additional parameters
                     0,                     //default directory
                     SW_SHOW);              //show command
    }
}
