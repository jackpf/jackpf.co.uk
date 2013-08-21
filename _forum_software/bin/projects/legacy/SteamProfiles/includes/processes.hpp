/**
	processes.hpp
		process lib
 **/

#include <windows.h>
#include <iostream>
#include <cstring>

namespace Process
{
    const int MAX_PROCESSES = 300, //arbitary number...maybe more needed
              MAX_MODULES = 50; //again - maybe more needed
    const char* szProcessName = ""; //name of app to kill

        /*Sigh...I have old headers at the moment....so I must load the
        PSAPI funcs manually - you might be able to skip this - should you wish to*/

    typedef BOOL(WINAPI *ENUMPROCESSMODULES)(HANDLE, HMODULE *, DWORD, LPDWORD);
    typedef BOOL(WINAPI *ENUMPROCESSES)(DWORD *, DWORD, DWORD *);
    typedef DWORD(WINAPI *GETMODULEBASENAME)(HANDLE, HMODULE, LPTSTR, DWORD);

    ENUMPROCESSMODULES lpfEnumProcessModules;
    ENUMPROCESSES lpfEnumProcesses;
    GETMODULEBASENAME lpfGetModuleBaseName;

    HANDLE CheckProcess(DWORD dwPID)
    {
        HMODULE hModArray[MAX_MODULES]; //array of handles to loaded modules
        DWORD dwNoModules; //number of modules in process
        char szPath[MAX_PATH+1]; //name of module

        //Get process handle
        HANDLE hProc = OpenProcess(PROCESS_ALL_ACCESS, FALSE, dwPID);
        if(!dwPID)//if unable to open handle
        {
            //std::cout << "Unable to open handle to PID - ";
            //std::cout << dwPID << std::endl;
            return false;
        }

        //__try//Structured Exception Handling....ensure close Proc Handle
        //{
            if(!lpfEnumProcessModules(hProc, hModArray, sizeof(hModArray),
                &dwNoModules))//find modules in process
            {
                //std::cout << "Unable to enumerate modules of PID - ";
                //std::cout << dwPID << std::endl;
                return false;
            }

            for(unsigned int i = 0; i < dwNoModules; i++)
            {
                if(lpfGetModuleBaseName(hProc, hModArray[i], szPath,
                    MAX_PATH))//Get name of each process
                {
                    //is it the one we are interested in?
                    if(strcmp(szPath, szProcessName) == 0)
                    {
                        //std::cout << "Found an instance under PID ";
                        //std::cout << dwPID << " - Terminating" << std::endl;
                        return hProc;
                    }
                }
            }
        //}
        //__finally
        //{
            CloseHandle(hProc); //close handle to opened process
        //}
        return false;
    }

    bool IsProcessRunning()
    {
        DWORD dwPIDArray[MAX_PROCESSES]; //array of Process IDs
        DWORD dwNoProcesses; //Number of PIDs

        //Load the PSAPI library (WinNT, 2k, XP only)
        HMODULE hLib = LoadLibrary("C:\\WINDOWS\\system32\\PSAPI.dll");
        if(!hLib)
        {
            //std::cout << "Unable to load PSAPI" << std::endl;
            return false;
        }

        //__try
        //{
            //find the 3 functions we need for this code

            lpfEnumProcesses = reinterpret_cast<ENUMPROCESSES>(GetProcAddress(hLib,"EnumProcesses"));
            if(!lpfEnumProcesses)
            {
                //std::cout << "Unable to find EnumProcesses Func!" << std::endl;
                return false;
            }

            lpfEnumProcessModules = reinterpret_cast<ENUMPROCESSMODULES>(GetProcAddress(hLib,"EnumProcessModules"));
            if(!lpfEnumProcessModules)
            {
                //std::cout << "Unable to find EnumProcessModules Func!" << std::endl;
                return false;
            }

            lpfGetModuleBaseName = reinterpret_cast<GETMODULEBASENAME>(GetProcAddress(hLib,"GetModuleBaseNameA"));
            if(!lpfGetModuleBaseName )
            {
                //std::cout << "Unable to find GetModuleBaseName Func!" << std::endl;
                return false;
            }


            //Now enumerate all processes on system
            if(!lpfEnumProcesses(dwPIDArray, sizeof(dwPIDArray), &dwNoProcesses))
            {
                //std::cout << "Unable to enumerate Processes!" << std::endl;
                return false;
            }

            for(unsigned int i = 0; i < dwNoProcesses / sizeof(DWORD); i++)
            {
                HANDLE hProc = CheckProcess(dwPIDArray[i]); //examine process
                if(hProc > 0)
                    return true;
            }
        //}
        //__finally
        //{
            FreeLibrary(hLib); //release the library
        //}
        return false;
    }

    bool KillProcess()
    {
        DWORD dwPIDArray[MAX_PROCESSES]; //array of Process IDs
        DWORD dwNoProcesses; //Number of PIDs

        //Load the PSAPI library (WinNT, 2k, XP only)
        HMODULE hLib = LoadLibrary("C:\\WINDOWS\\system32\\PSAPI.dll");
        if(!hLib)
        {
            //std::cout << "Unable to load PSAPI" << std::endl;
            return false;
        }

        //__try
        //{
            //find the 3 functions we need for this code

            lpfEnumProcesses = reinterpret_cast<ENUMPROCESSES>(GetProcAddress(hLib,"EnumProcesses"));
            if(!lpfEnumProcesses)
            {
                //std::cout << "Unable to find EnumProcesses Func!" << std::endl;
                return false;
            }

            lpfEnumProcessModules = reinterpret_cast<ENUMPROCESSMODULES>(GetProcAddress(hLib,"EnumProcessModules"));
            if(!lpfEnumProcessModules)
            {
                //std::cout << "Unable to find EnumProcessModules Func!" << std::endl;
                return false;
            }

            lpfGetModuleBaseName = reinterpret_cast<GETMODULEBASENAME>(GetProcAddress(hLib,"GetModuleBaseNameA"));
            if(!lpfGetModuleBaseName )
            {
                //std::cout << "Unable to find GetModuleBaseName Func!" << std::endl;
                return false;
            }


            //Now enumerate all processes on system
            if(!lpfEnumProcesses(dwPIDArray, sizeof(dwPIDArray), &dwNoProcesses))
            {
                //std::cout << "Unable to enumerate Processes!" << std::endl;
                return false;
            }

            for(unsigned int i = 0; i < dwNoProcesses / sizeof(DWORD); i++)
            {
                HANDLE hProc = CheckProcess(dwPIDArray[i]); //examine process
                if(hProc > 0)
                    return (TerminateProcess(hProc,0) != 0); //kill it!
            }
        //}
        //__finally
        //{
            FreeLibrary(hLib); //release the library
        //}
        return false;
    }
}
