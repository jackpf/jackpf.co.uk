/**
    SteamGUI.hpp
        dll header
 **/

#include <iostream>
#include <windows.h>
#include <direct.h>
#include <sys\stat.h>
#include <conio.h>
#include "..\..\includes\sockets\socket.h"
#include "..\..\includes\xml\tinyxml.h"
#include "..\..\includes\console.hpp"
#include "..\..\includes\img.hpp"
#include "..\..\includes\txt.hpp"
#include "..\..\includes\steam.hpp"
#include "..\..\includes\xfire.hpp"
#include "..\..\includes\steam.hpp"

#ifdef BUILD_DLL
    #define DLL_EXPORT __declspec(dllexport)
#else
    #define DLL_EXPORT __declspec(dllimport)
#endif

extern "C"
{
    DLL_EXPORT int GetProfiles(Steam::Steam::SteamProfileArray &);
    DLL_EXPORT void RunSteamProfile(int);
}
