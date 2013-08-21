#include <iostream>

class steam
{
	private:
		int
			iServer, 
			iAuthID;
		__int64
			iSteamID;
	
	public:
		__int64 GetFriendID(const char *pszAuthID)
		{
			char szAuthID[64];
			strcpy_s(szAuthID, 63, pszAuthID);

			char *szTmp = strtok(szAuthID, ":");
			while(szTmp = strtok(NULL, ":"))
			{
				char *szTmp2 = strtok(NULL, ":");
				if(szTmp2)
				{
					iServer = atoi(szTmp);
					iAuthID = atoi(szTmp2);
				}
			}

			__int64 i64friendID = static_cast<__int64>(iAuthID * 2 + 76561197960265728 + iServer);

			return i64friendID;
		}
		int GetSteamID(__int64 friendID)
		{
			iSteamID = (friendID - (76561197960265728 + ((friendID & 1) == 0) ? 0 : 1)) / 2;

			return static_cast<int>(iSteamID);
		}
};

int main(int argc, char *argv[])
{
	if(argc == 2)
	{
		const char *SteamID = argv[1];

		steam Steam;

		if(atoi(SteamID) == 0)
			std::cout << Steam.GetFriendID(SteamID) << std::endl;
		else
			std::cout << Steam.GetSteamID(_atoi64(SteamID)) << std::endl;

		return 0;
	}
	else
	{
		std::cout << "Usage: steam.exe <friendid|steamid>" << std::endl;

		return -1;
	}
}