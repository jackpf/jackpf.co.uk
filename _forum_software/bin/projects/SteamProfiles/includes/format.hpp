/**
    format.hpp
		text formatting:
        color your text in Windows console mode
        colors are 0=black 1=blue 2=green and so on to 15=white
        colorattribute = foreground + background * 16
        to get red text on yellow use 4 + 14*16 = 228
        light red on yellow would be 12 + 14*16 = 236
		img formatting:
		displays images in the console
  **/

#include <iostream>
#include <windows.h>
#include <stdio.h>
#include <string.h>

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

//namespace?

const int   IMGTOP              = 0;
const int   IMGBOTTOM           = 200;
const int   IMGLEFT             = 0;
const int   IMGRIGHT            = 500;

bool        DrawImg             (char *, int = 0, int = 0);
HWND        BCX_Bitmap          (char *, HWND = 0, int = 0, int = 0, int = 0, int = 0, int = 0, int = 0, int = 0, int = 0);
HWND        GetConsoleWndHandle (void);

bool DrawImg(char *ImageFile, int XPos, int YPos)
{
    static HWND    hConWnd;

    if(hConWnd = GetConsoleWndHandle())
    {
        //select a bitmap file you have or use one of the files in the Windows folder
        //filename, handle, ID, ulcX, ulcY, width, height     0,0 auto-adjusts
        BCX_Bitmap(ImageFile, hConWnd, 123, XPos, YPos, 0, 0);

        return true;
    }
    else
        return false;
}

//draw the bitmap
HWND BCX_Bitmap(char *Text, HWND hWnd, int id, int X, int Y, int W, int H, int Res, int Style, int Exstyle)
{
    HWND A;
    HBITMAP hBitmap;

    //set default style
    if(!Style)
        Style = WS_CLIPSIBLINGS | WS_CHILD | WS_VISIBLE | SS_BITMAP | WS_TABSTOP;

    //form for the image
    A = CreateWindowEx(Exstyle, "static", NULL, Style, X, Y, 0, 0, hWnd, (HMENU) id, GetModuleHandle(0), NULL);

    //Text contains filename
    hBitmap = (HBITMAP) LoadImage(0, Text, IMAGE_BITMAP, 0, 0, LR_LOADFROMFILE | LR_CREATEDIBSECTION);

    //auto-adjust width and height
    if(W || H)
        hBitmap = (HBITMAP) CopyImage(hBitmap, IMAGE_BITMAP, W, H, LR_COPYRETURNORG);
    SendMessage(A, (UINT) STM_SETIMAGE, (WPARAM) IMAGE_BITMAP, (LPARAM) hBitmap);
    if(W || H)
        SetWindowPos(A, HWND_TOP, X, Y, W, H, SWP_DRAWFRAME);
    return A;
}


//tricking Windows just a little ...
HWND GetConsoleWndHandle(void)
{
    HWND hConWnd;
    OSVERSIONINFO os;
    char szTempTitle[64], szClassName[128], szOriginalTitle[1024];

    os.dwOSVersionInfoSize = sizeof(OSVERSIONINFO);
    GetVersionEx(&os);
    //may not work on WIN9x
    if(os.dwPlatformId == VER_PLATFORM_WIN32s)
        return 0;

    GetConsoleTitle(szOriginalTitle, sizeof(szOriginalTitle));
    sprintf(szTempTitle, "%u - %u", (unsigned int) GetTickCount(), (unsigned int) GetCurrentProcessId());
    SetConsoleTitle(szTempTitle);
    Sleep(40);
    //handle for NT and XP
    hConWnd = FindWindow(NULL,szTempTitle );
    SetConsoleTitle(szOriginalTitle);

    //may not work on WIN9x
    if(os.dwPlatformId == VER_PLATFORM_WIN32_WINDOWS)
    {
        hConWnd = GetWindow(hConWnd, GW_CHILD);
        if(hConWnd == NULL)
            return 0;
        GetClassName(hConWnd, szClassName, sizeof (szClassName));
        //while(_stricmp(szClassName, "ttyGrab") != 0)
        while(strcmp(szClassName, "ttyGrab") != 0)
        {
            hConWnd = GetNextWindow(hConWnd, GW_HWNDNEXT);
            if(hConWnd == NULL)
                return 0;
            GetClassName(hConWnd, szClassName, sizeof(szClassName));
        }
    }

    return hConWnd;
}

