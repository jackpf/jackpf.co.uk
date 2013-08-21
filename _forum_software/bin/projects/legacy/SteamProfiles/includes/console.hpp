/**
	steam.hpp
		console lib
 **/

#include <vector>
#include <ctia.h>

namespace Console
{
    class Cmds
    {
        protected:
        void GetCmd(std::string Cmd, std::vector<std::string> Args)
        {
            if(Cmd.compare("test") == 0)
            {
                std::cout << "Test function. Arg list:" << std::endl;
                for(unsigned int i = 0; i < Args.size(); i++)
                    std::cout << "\t" << Args.at(i) << std::endl;
            }
            else if(Cmd.compare("help") == 0)
            {
                std::cout << "Functions:" << std::endl;
                std::cout << "\thelp\n\thelp" << std::endl;
            }
            else
                std::cout << "Unknown function." << std::endl;
        }
        /*template<typename T, typename T2>
        void Callback(T (*func)(T2), std::vector<std::string> Args)
        {
            (*func)(Args);
        }*/
    };
    class Console: protected Cmds
    {
        private:
        static const char CONSOLE_INPUT = 'c';
        std::string Cmd;

        public:
        static bool RunConsole(char Input)
        {
            return (Input == CONSOLE_INPUT);
        }
        void Command(std::string _Cmd)
        {
            Cmd = _Cmd;

            std::vector<std::string> Args = _ParseArgs();

            GetCmd(Args.at(0), Args);
        }
        std::vector<std::string> _ParseArgs()
        {
            std::vector<std::string> ArgArray(_CountArgs(), "");
            std::string Buffer;

            for(unsigned int i = 0, ArgNum = 0; i <= Cmd.length(); i++)
            {
                if(Cmd[i] != ' ' && i != Cmd.length())
                {
                    Buffer += Cmd[i];
                }
                else
                {
                    ArgArray[ArgNum] = Buffer + '\0';
                    ArgNum++;
                    Buffer = "";
                }
            }

            return ArgArray;
        }
        int _CountArgs()
        {
            int ArgCount = 0;
            bool ArgExists = false;

            for(unsigned int i = 0; i < Cmd.length(); i++)
                if(Cmd[i] == ' ' && Cmd[i - 1] != ' ' && ArgExists || i == Cmd.length() - 1)
                {
                    ArgCount++;
                    ArgExists = false;
                }
                else
                {
                    ArgExists = true;
                }

            return ArgCount;
        }
    };
}
