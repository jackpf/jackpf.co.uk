#include "socket.h"

Socket::Socket()
{
    //create socket
    if(WSAStartup(MAKEWORD(2, 2), &WsaData) == NO_ERROR)
    {
        Connection = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP);

        if(Connection == INVALID_SOCKET)
        {
            WSACleanup();

            return;
        }
    }
    else
    {
        WSACleanup();

        return;
    }
}
Socket::~Socket()
{
    WSACleanup();
}
bool Socket::Connect(struct ConnectInfo _ConnectInfo)
{
    //resolve domain names
    const char *_Address = _ConnectInfo.Address;
    int Port = _ConnectInfo.Port;

    if(atoi(_Address) == 0)
    {
        struct hostent *Host;
        struct in_addr HostAddress;
        Host = gethostbyname(_Address);
        HostAddress.s_addr = *(u_long *) Host->h_addr_list[0];
        _Address = inet_ntoa(HostAddress);
    }

    //connect
    Address.sin_family = AF_INET;
    Address.sin_addr.s_addr = inet_addr(_Address);
    Address.sin_port = htons(Port);

    if(connect(Connection, (SOCKADDR *) &Address, sizeof(Address)) != SOCKET_ERROR)
        return true;
    else
    {
        WSACleanup();

        return false;
    }
}
bool Socket::Bind(struct ConnectInfo _ConnectInfo)
{
    Address.sin_family = AF_INET;
    Address.sin_addr.s_addr = INADDR_ANY;
    Address.sin_port = htons(_ConnectInfo.Port);

    if(bind(Connection, (SOCKADDR *) &Address, sizeof(Address)) != SOCKET_ERROR)
        return true;
    else
    {
        WSACleanup();

        return false;
    }
}
void Socket::Listen(void)
{
    while(listen(Connection, SOMAXCONN) == SOCKET_ERROR);

    SOCKET _Connection = Connection;
    int AddressSize = sizeof(Address);
    Connection = accept(Connection, (SOCKADDR *) &Address, &AddressSize);

    //...

    closesocket(Connection);
    Connection = _Connection;
}
void Socket::SendData(char *Buffer)
{
    send(Connection, Buffer, strlen(Buffer), 0);
}
int Socket::RecvData(SockMessage Buffer, int Size)
{
    int i = recv(Connection, Buffer, Size, 0);
    Buffer[i] = '\0';

    return i;
}
void Socket::KeepAlive(void)
{
    Connect(ConnectInfo);
}
void Socket::CloseConnection(void)
{
    closesocket(Connection);
}
