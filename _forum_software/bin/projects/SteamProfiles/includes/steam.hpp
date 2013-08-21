/**
    steam.hpp
	    steam lib
 **/

#ifndef STEAM_H_INCLUDED
#define STEAM_H_INCLUDED

//#include "steamcommunity.hpp"
#pragma comment(lib, "Steam.dll")
#include "processes.hpp"

namespace Steam
{
    class Steam
    {
        public: //private:
        static const int MaxProfiles = 64;                  //number of profiles to account for
        static const int MaxLoadAttempts = 10;              //number of times to attempt to load steam
        typedef char SteamProfileArray[MaxProfiles][128];   //profile array type definition
        typedef std::vector<std::string> SteamProfileArray2; //profile array 2 type definition
        char IniFile[MAX_PATH + FILENAME_MAX];              //name of the ini file to retrieve profiles from
        char SteamFile[MAX_PATH + FILENAME_MAX];            //filename of the steam executable
        SteamProfileArray SteamProfiles;                    //array of profiles
        static const int MODULE_Steam = 0;                  //steam module index
        Steam *Modules[2];                                  //array of modules

        public:
        Steam() //constructor: sets up environment variables
        {
            //find the location of SteamProfiles.ini
            _getcwd(IniFile, sizeof(IniFile));
            sprintf(IniFile, "%s\\SteamProfiles.ini", IniFile); //strcat

            //get the location of the steam executable from the ini file
            GetPrivateProfileString("Steam", "File", "", SteamFile, sizeof(SteamFile), IniFile);

            //module
            Modules[MODULE_Steam] = this;
        }
        std::vector<std::string> ParseProfile(std::string Profile)
        {
            std::vector<std::string> ArgArray;
            std::string Buffer;
            bool IsQuoted = false;

            for(unsigned int i = 0, ArgNum = 0; i <= Profile.length(); i++)
            {
                if(Profile[i] == '"')
                {
                    IsQuoted = !IsQuoted;
                    continue;
                }

                if(Profile[i] != ' ' && i != Profile.length() || IsQuoted)
                {
                    Buffer += Profile[i];
                }
                else
                {
                    ArgArray.push_back(Buffer);
                    ArgNum++;
                    Buffer = "";
                }
            }

            return ArgArray;
        }
        int GetProfiles(SteamProfileArray &Profiles, bool Format = false) //retrieves profiles from the ini file and stores them in an array
        {
            //store the profiles in an array
			int NumProfiles = 0;
			DWORD bytes = 1;
            for(int i = 1; bytes > 0; i++)
            {
                char _i[2];
                itoa(i, _i, 10);

                bytes = GetPrivateProfileString("SteamProfiles", _i, "", SteamProfiles[i], sizeof(SteamProfiles[i]), IniFile);

				if(bytes > 0)
					NumProfiles++;
			}

            //format the profiles
            for(int i = 1; i <= NumProfiles; i++)
            {
                if(strcmp(SteamProfiles[i], "") != 0)
                {
                    std::string Profile = SteamProfiles[i];
                    std::vector<std::string> Args = ParseProfile(Profile);

                    std::string Username = Args.at(0), Comment = (Args.size() >= 3) ? Args.at(2) : "";

                    //formatting
                    if(Format)
                    {
                        //if(Username.length() > 9 && Username.length() != 10)
                        //    Username = Username.substr(0, 9) + "..." + Username.substr(Username.length() - 1, 1);
                        if(Comment != "")
                            Username += " (" + Comment + ")";
                    }

                    //return profiles in SteamProfileArray arg
                    strcpy(Profiles[i], Username.c_str());
                }
                else
                    Profiles[i][0] = '\0'; //clear the array value if it doesn't exist
            }

			return NumProfiles;
        }
        int GetProfiles2(SteamProfileArray2 &Profiles)
        {
            SteamProfileArray _Profiles;
            int NumProfiles = GetProfiles(_Profiles);

            Profiles.push_back("");
            for(int i = 1; i <= NumProfiles; i++)
                Profiles.push_back(_Profiles[i]);

            return /*NumProfiles*/Profiles.size() - 1;
        }
        bool SetProfile(int ProfileIndex, char *LoginInfo)
        {
            if(ProfileIndex > 0 /*&& ProfileIndex <= MaxProfiles */&& strcmp(SteamProfiles[ProfileIndex], "") != 0)
            {
                std::string Profile = SteamProfiles[ProfileIndex];
                std::vector<std::string> Args = ParseProfile(Profile);

                std::string Username = Args.at(0), Password = Args.at(1);

                sprintf(LoginInfo, "-login %s %s", Username.c_str(), Password.c_str());

                return true;
            }
            else
                return false;
        }
        virtual bool CanRun()
        {
            struct stat FileInfo;

            return (stat(SteamFile, &FileInfo) == 0);
        }
        virtual bool IsRunning()
        {
            using namespace Process;

            //get process name from SteamFile
            std::string SteamProcess = SteamFile;
            szProcessName = SteamProcess.substr(SteamProcess.rfind("\\") + 1, SteamProcess.length()).c_str();

            return IsProcessRunning();
        }
        virtual void Run(char *LoginInfo = "") //executes the steam executable with login info from the chosen profile
        {
            //fetch additional parameters to run with
            char SteamParameters[FILENAME_MAX + 128];
            GetPrivateProfileString("Steam", "Parameters", "", SteamParameters, sizeof(SteamParameters), IniFile);
            sprintf(SteamParameters, "%s %s", SteamParameters, LoginInfo);

            //create a new steam process
            ShellExecute(0,
                         "open",            //operation to perform
                         SteamFile,         //application name
                         SteamParameters,   //additional parameters
                         0,                 //default directory
                         SW_SHOW);          //show command

        }
        virtual void Close(bool Force)
        {
            if(!Force) //shutdown gracefully
                Run("-shutdown");
            else //force shutdown
            {
                char ProcessInfo[64];
                std::string SteamProcess = SteamFile;
                SteamProcess = SteamProcess.substr(SteamProcess.rfind("\\") + 1, SteamProcess.length());
                sprintf(ProcessInfo, "taskkill -f -im %s", SteamProcess.c_str());
                FILE *Stream = popen(ProcessInfo, "r"); //system
                pclose(Stream);
            }
        }
        void ProfileInfo()
        {
            //^_^
            const string LoadingMessage = "Loading profile info... ";
            cout << LoadingMessage;

            //SteamCommunity SteamCommunity;
            HINSTANCE SteamLib = LoadLibrary("resources\\Steam.dll");
            if(SteamLib)
            {
                typedef __int64 (*GetFriendID)(const char *);
                GetFriendID _GetFriendID;
                _GetFriendID = (GetFriendID) GetProcAddress(SteamLib, "GetFriendID");

                char SteamID[64];
                GetPrivateProfileString("Steam", "SteamID", "", SteamID, sizeof(SteamID), IniFile);

                if(strcmp(SteamID, "") != 0)
                {
                    __int64 SteamFriendID = _GetFriendID(SteamID); //SteamCommunity.GetFriendID(SteamID);

                    Socket Socket;
                    Socket.ConnectInfo.Address = "steamcommunity.com";
                    Socket.ConnectInfo.Port = 80;
                    Socket::SockMessage Message;
                    string SteamProfileXML = "";

                    if(Socket.Connect(Socket.ConnectInfo))
                    {
                        char RequestStr[128];
                        sprintf(RequestStr, "GET /profiles/%I64d?xml=1 HTTP/1.0\r\nHost: %s\r\nConnection: close\r\n\r\n", SteamFriendID, Socket.ConnectInfo.Address);
                        Socket.SendData(RequestStr);
                        while(Socket.RecvData(Message, Socket.MSGLEN) > 0)
                            SteamProfileXML += Message;
                        Socket.CloseConnection();

                        TiXmlDocument Doc;
                        Doc.Parse(SteamProfileXML.substr(SteamProfileXML.find("<?xml"), SteamProfileXML.length()).c_str());
                        if(!Doc.Error())
                        {
                            TiXmlElement *DocRoot = Doc.RootElement();

                            for(unsigned int i = 1; i <= LoadingMessage.length(); i++)
                                cout << "\b";
                            //cout << "SteamID:\t\t" << SteamID << endl;
                            //cout << "FriendID:\t\t" << SteamFriendID << endl;
                            cout << "Steam name:\t\t" << DocRoot->FirstChildElement("steamID")->GetText() << endl;
                            if(strcmp(DocRoot->FirstChildElement("privacyState")->GetText(), "public") == 0)
                            {
                                cout << "Profile status:\t\tpublic" << endl;
                                cout << "Status:\t\t\t" << ((strcmp(DocRoot->FirstChildElement("onlineState")->GetText(), "online") == 0 || strcmp(DocRoot->FirstChildElement("onlineState")->GetText(), "in-game") == 0) ? "online" : "offline") << endl;
                                cout << "Hours past fortnight:\t" << DocRoot->FirstChildElement("hoursPlayed2Wk")->GetText() << endl;
                            }
                            else
                                cout << "Profile status:\t\tprivate" << endl;
                        }
                        else
                            cout << "Unable to parse XML: " << Doc.ErrorDesc() << endl;
                    }
                    else
                        cout << "Unable to create socket." << endl;
                }
                else
                    cout << "Profile info not available." << endl;
            }
            else
            {
                cout << "Error loading steam.dll." << endl;
                return;
            }
            //\^_^
        }
    };
}

#endif // STEAM_H_INCLUDED
