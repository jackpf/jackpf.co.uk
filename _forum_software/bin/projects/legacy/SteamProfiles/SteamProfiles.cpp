/**
    SteamProfiles.cpp
        main program
 **/

#include <iostream>
#include <windows.h>
#include <direct.h>
#include <sys\stat.h>
#include <conio.h>
#include "includes\sockets\socket.h"
#include "includes\xml\tinyxml.h"
#include "includes\console.hpp"
#include "includes\img.hpp"
#include "includes\txt.hpp"
#include "includes\steam.hpp"
#include "includes\xfire.hpp"

#define AUTHOR  "jackpf"
#define VERSION "0.4.5.beta"

using namespace std;

int main(int argc, char *argv[])
{
    using Steam::Steam;
    Steam Steam;
    using Xfire::Xfire;
    Xfire Xfire;

    SetConsoleTitle("Steam Profiles");

    Color::COut_Color.SetColor(Color::CRED2);
    Color::COut_Color << "STEAM PROFILES"; //cout
    Color::COut_Color.SetColor(Color::CPURPLE);
    Color::COut_Color << " version " << VERSION << " by " << AUTHOR << Color::CEndL;
    Color::COut_Color.SetColor(Color::CGRAY2);
    Color::COut_Color << "---------------------------------------------" << Color::CEndL;
    Color::COut_Color.SetColor(Color::CBLUE2);
    Color::COut_Color << "Readme: SteamProfiles.ini contains profile information." << Color::CEndL;
    Color::COut_Color.SetColor(Color::CGRAY2);
    Color::COut_Color << "---------------------------------------------" << Color::CEndL;
    DrawImg("resources\\steam.bmp", IMGRIGHT, IMGTOP);

    /*cout << */Steam.ProfileInfo();
    /*cout << */Xfire.ProfileInfo(); //Steam.Modules[Xfire::Xfire::MODULE_Xfire].ProfileInfo();

    Color::COut_Color.SetColor(Color::CGRAY2);
    Color::COut_Color << "---------------------------------------------" << Color::CEndL;

    cout << "Profiles:" << endl;

    Steam::SteamProfileArray ProfileNames;
    int NumProfiles = Steam.GetProfiles(ProfileNames);

    if(NumProfiles > 0)
    {
        for(int i = 1; i <= /*Steam::MaxProfiles*/NumProfiles; i++)
        {
            if(strcmp(ProfileNames[i], "") != 0)
                cout << "\t" << i << ")" << ProfileNames[i] << endl;
            else
                break;
        }

        LoadSteamProfile:
        cout << "Choose a profile: ";
        char ProfileIndex[2], LoginInfo[512];
        ProfileIndex[0] = static_cast<char>(getche());
        cout << endl; //cin >> ProfileIndex; //if Steam.MaxProfiles & NumProfiles > 9 ?

        //console?
        if(Console::Console::RunConsole(ProfileIndex[0]))
        {
            Console::Console Console;
            string Command;

            Color::COut_Color.SetColor(Color::CYELLOW2);
            Color::COut_Color << "Console (dev)" << Color::CEndL;
            cout << "SteamProfiles > ";
            getline(cin, Command);
            Console.Command(Command);
        }

        if(Steam.SetProfile(atoi(ProfileIndex), LoginInfo))
        {
            if(Steam.CanRun())
            {
                bool RunSteam = true;

                if(Steam.IsRunning())
                {
                    cout << "Closing process... 0%";
                    for(int i = 1; i <= 100; i++)
                    {
                        Sleep(10);
                        cout << "\b\b" << ((i > 10) ? "\b" : "") << i << "%";
                    }
                    cout << endl;

                    Steam.Close();
                }

                for(int LoadAttempt = 1; Steam.IsRunning() || LoadAttempt == 1; LoadAttempt++)
                {
                    cout << "Creating process... 0%";
                    for(int i = 1; i <= 100; i++)
                    {
                        Sleep(10);
                        cout << "\b\b" << ((i > 10) ? "\b" : "") << i << "%";
                    }
                    cout << endl;

                    if(LoadAttempt >= Steam::MaxLoadAttempts)
                    {
                        std::cout << "Unable to close and re-open steam after " << Steam::MaxLoadAttempts << " tries." << endl;
                        RunSteam = false;
                        break;
                    }
                }

                if(RunSteam)
                {
                    Steam.Run(LoginInfo);
                    cout << "Logged in successfully with username: " << ProfileNames[atoi(ProfileIndex)] << "." << endl;

                    if(Xfire.CanRun() && !Xfire.IsRunning())
                    {
                        Xfire.Run();
                        cout << "Executed." << endl;
                    }
                }
            }
            else
                cout << "Unable to find executable: " << Steam.SteamFile << "." << endl;
        }
        else
            cout << "Unable to retrieve login information for index: " << atoi(ProfileIndex) << "." << endl;
    }
    else
        cout << "Unable to load any profiles from: " << Steam.IniFile << "." << endl;

    char LoadNewSteamProfile;
    cout << "Load new profile? (Y/N): ";
    LoadNewSteamProfile = toupper(getche());
    cout << endl;
    if(LoadNewSteamProfile == 'Y')
        goto LoadSteamProfile;

    return EXIT_SUCCESS;
}
