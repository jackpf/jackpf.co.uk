/**
    Steam.cpp
        Steam.dll source
 **/

#include "Steam.hpp"

extern "C"
{
    DLL_EXPORT __int64 GetFriendID(const char *pszAuthID)
    {
        char szAuthID[64];
        strcpy(szAuthID, pszAuthID);

        char *szTmp = strtok(szAuthID, ":");
        int iServer, iAuthID;
        while(szTmp = strtok(NULL, ":"))
        {
            char *szTmp2 = strtok(NULL, ":");
            if(szTmp2)
            {
                iServer = atoi(szTmp);
                iAuthID = atoi(szTmp2);
            }
        }

        __int64 i64friendID = iAuthID * 2 + 76561197960265728LL + iServer;

        return i64friendID;
    }
    DLL_EXPORT char *GetSteamID(__int64 friendID)
    {
        int iServer = (friendID % 2 == 0) ? 0 : 1;

        friendID = (friendID - iServer - 76561197960265728LL) / 2;

        char *_friendID = new char;
        sprintf(_friendID, "STEAM_0:%i:%I64u", iServer, friendID);
        return _friendID;
    }
}
