/**
    txt.hpp
        color your text in Windows console mode
        colors are 0=black 1=blue 2=green and so on to 15=white
        colorattribute = foreground + background * 16
        to get red text on yellow use 4 + 14*16 = 228
        light red on yellow would be 12 + 14*16 = 236
  **/

#include <iostream>
#include <windows.h>

namespace Color
{
    class COut_Color
    {
        private:
        int _Color, _Background;
        char *_Text;
        static const int DEFAULT_COLOR = 15;

        public:
        COut_Color  &operator <<        (char *);
        void        SetColor            (int, int = 0);
    } _COut_Color;

    COut_Color &COut_Color::operator<<(char *Text)
    {
        HANDLE Console;

        Console = GetStdHandle(STD_OUTPUT_HANDLE);

        SetConsoleTextAttribute(Console, _Color + (_Background * 16));

        std::cout << Text;

        SetConsoleTextAttribute(Console, DEFAULT_COLOR);

        return *this;
    }
    void COut_Color::SetColor(int Color, int Background)
    {
        _Color      = Color;
        _Background = Background;
    }

    COut_Color COut_Color = _COut_Color;

    const int   CBLACK              = 0;
    const int   CBLUE               = 1;
    const int   CGREEN              = 2;
    const int   CTURQUOISE          = 3;
    const int   CRED                = 4;
    const int   CPURPLE             = 5;
    const int   CYELLOW             = 6;
    const int   CGRAY               = 7;
    const int   CGRAY2              = 8;
    const int   CBLUE2              = 9;
    const int   CGREEN2             = 10;
    const int   CTURQUOISE2         = 11;
    const int   CRED2               = 12;
    const int   CPINK               = 13;
    const int   CYELLOW2            = 14;
    const int   CWHITE              = 15;

    char        *CEndL              = "\n";
}
