/**
    Steam.hpp
        dll header
 **/
#include <windows.h>
#include <iostream>

#ifdef BUILD_DLL
    #define DLL_EXPORT __declspec(dllexport)
#else
    #define DLL_EXPORT __declspec(dllimport)
#endif

extern "C"
{
    DLL_EXPORT __int64 GetFriendID(const char *);
    DLL_EXPORT char *GetSteamID(__int64);
}
