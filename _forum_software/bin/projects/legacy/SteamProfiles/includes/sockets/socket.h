/**
        socket.h
	        socket lib
 **/

#include "WinSock2.h"
#pragma comment(lib, "ws2_32.lib")

class Socket
{
    protected:
    WSADATA WsaData;
    SOCKET Connection;
    sockaddr_in Address;

    public:
    static const int MSGLEN = 1024;
    struct ConnectInfo
    {
        char *Address;
        int Port;
    } ConnectInfo;
    typedef char SockMessage[MSGLEN];

    public:
    Socket(void);
    ~Socket(void);
    bool Connect(struct ConnectInfo);
    bool Bind(struct ConnectInfo);
    void Listen(void);
    void SendData(char *);
    int RecvData(SockMessage, int);
    void KeepAlive(void);
    void CloseConnection(void);
};
