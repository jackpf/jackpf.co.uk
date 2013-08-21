/**
	xfire.hpp
		xfire lib
 **/

#ifndef XFIRE_H_INCLUDED
#define XFIRE_H_INCLUDED

#include "steam.hpp"

namespace Xfire
{
    class Xfire: public Steam::Steam
    {
        public: //private:
        char XfireFile[MAX_PATH + FILENAME_MAX];            //filename of the steam executable
        static const int MODULE_Xfire = MODULE_Steam + 1;   //xfire module index

        public:
        Xfire()
        {
            //get the location of the xfire executable from the ini file
            GetPrivateProfileString("Xfire", "File", "", XfireFile, sizeof(XfireFile), IniFile);

            //module
            Modules[MODULE_Xfire] = this;
        }
        bool CanRun()
        {
            struct stat FileInfo;

            //fetch whether to run xfire or not
            char XfireRun[8];
            GetPrivateProfileString("Xfire", "Run", "", XfireRun, sizeof(XfireRun), IniFile);

            return (stat(XfireFile, &FileInfo) == 0 && strcmp(XfireRun, "true") == 0);
        }
        bool IsRunning()
        {
            using namespace Process;

            //get process name from XfireFile
            std::string XfireProcess = XfireFile;
            szProcessName = XfireProcess.substr(XfireProcess.rfind("\\") + 1, XfireProcess.length()).c_str();

            return IsProcessRunning();
        }
        void Run(char *LoginInfo = "")
        {
            //create a new xfire process
            ShellExecute(0,
                         "open",            //operation to perform
                         XfireFile,         //application name
                         LoginInfo,         //additional parameters
                         0,                 //default directory
                         SW_SHOW);          //show command
        }
        void Close()
        {
            Run("/shutdown");
        }
        void ProfileInfo()
        {
            char XfireUsername[64];
            GetPrivateProfileString("Xfire", "Username", "", XfireUsername, sizeof(XfireUsername), IniFile);

            if(CanRun() && strcmp(XfireUsername, "") != 0)
            {
                //^_^
                const string LoadingMessage = "Loading profile info... ";
                cout << LoadingMessage;

                Socket Socket;
                Socket.ConnectInfo.Address = "www.xfire.com";
                Socket.ConnectInfo.Port = 80;
                Socket::SockMessage Message;
                string XfireProfileXML = "";

                if(Socket.Connect(Socket.ConnectInfo))
                {
                    char RequestStr[128];
                    sprintf(RequestStr, "GET /xml/%s/user_gameplay/ HTTP/1.0\r\nHost: %s\r\nConnection: close\r\n\r\n", XfireUsername, Socket.ConnectInfo.Address);
                    Socket.SendData(RequestStr);
                    while(Socket.RecvData(Message, Socket.MSGLEN) > 0)
                        XfireProfileXML += Message;
                    Socket.CloseConnection();

                    TiXmlDocument Doc;
                    Doc.Parse(XfireProfileXML.substr(XfireProfileXML.find("<?xml"), XfireProfileXML.length()).c_str());
                    if(!Doc.Error())
                    {
                        TiXmlElement *DocRoot = Doc.RootElement();

                        TiXmlNode *DocAttribute = DocRoot->FirstChild();
                        int XfireHours = 0;
                        while(DocAttribute)
                        {
                            XfireHours += atoi(DocAttribute->FirstChildElement("weektime")->GetText());

                            DocAttribute = DocAttribute->NextSibling();
                        }

                        for(unsigned int i = 1; i <= LoadingMessage.length(); i++)
                            cout << "\b";

                        cout.setf(ios::fixed, ios::floatfield);
                        cout.precision(1);
                        cout << "(Xfire):\t\t" << (float) XfireHours / (60 * 60) << endl;
                    }
                    else
                        cout << "Unable to parse XML: " << Doc.ErrorDesc() << endl;
                }
                else
                    cout << "Unable to create socket." << endl;
            }
        }
    };
}

#endif // XFIRE_H_INCLUDED
