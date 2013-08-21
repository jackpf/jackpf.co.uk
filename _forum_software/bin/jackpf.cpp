#include <windows.h>

#define Window_Class "WindowClass"

char Title[] = "Title!", Message[] = "Hello world :)";

//window callback handler
LRESULT CALLBACK WndProc(HWND hwnd, UINT msg, WPARAM wParam, LPARAM lParam)
{
	//switch message...
	switch(msg)
	{
		//display window
		case WM_PAINT:
			PAINTSTRUCT ps;
			HDC hdc = BeginPaint(hwnd, &ps);

			TextOut(hdc,
					5, 5,
					Message, strlen(Message));

            CreateWindow(
            "BUTTON",
            "Working on this...",
            WS_VISIBLE | WS_CHILD,
            5, 60,
            150, 20,
            hwnd,
            NULL, NULL, NULL);

			EndPaint(hwnd, &ps);
		break;
		//close window
		case WM_CLOSE:
			MessageBox(hwnd, "Bye", ":)", MB_OK);
			DestroyWindow(hwnd);
		break;
		//register window process
		default:
			return DefWindowProc(hwnd, msg, wParam, lParam);
		break;
	}

	return 0;
}

int WINAPI WinMain(HINSTANCE hInstance, HINSTANCE hPrevInstance,
                   char *lpCmdLine, int nCmdShow)
{
	//register windows classes
	WNDCLASSEX	window;
	HWND		hwnd;
	MSG			msg;

	//register window with....windows ;)
	window.cbSize			= sizeof(WNDCLASSEX);
	window.style			= 0;
	window.lpfnWndProc		= WndProc;
	window.cbClsExtra		= 0;
	window.cbWndExtra		= 0;
	window.hInstance		= hInstance;
	window.hIcon			= LoadIcon(NULL, IDI_APPLICATION);
	window.hCursor			= LoadCursor(NULL, IDC_ARROW);
	window.hbrBackground