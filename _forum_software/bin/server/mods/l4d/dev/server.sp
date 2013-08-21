//sdk tools
#include <sourcemod>
#include <sdktools>

//plugin version
#define PLUGIN_VERSION "1.0.0"

//plugin info
public Plugin:myinfo = 
{
	name		= "Jackpf's Server",
	author		= "jackpf",
	description	= "Server stuff.",
	version		= PLUGIN_VERSION,
	url			= "http://jackpf.co.uk"
}

//plugin setup
new Handle:Server_Message_Display	= INVALID_HANDLE;
new Handle:Server_Message			= INVALID_HANDLE;
new Handle:Server_Message_Interval	= INVALID_HANDLE;

new Handle:Server_Rotoblin_Enable	= INVALID_HANDLE;
new Handle:Server_Rotoblin_Cfg		= INVALID_HANDLE;

public OnPluginStart()
{
	//require Left 4 Dead
	decl String:Game[64];
	GetGameFolderName(Game, sizeof(Game));
	if(!StrEqual(Game, "left4dead", false))
		SetFailState("Plugin supports Left 4 Dead only.");
	
	//register cvars
	Server_Message_Display	= CreateConVar("server_message_display", "1", "Enable server message.", FCVAR_PLUGIN | FCVAR_NOTIFY);
	Server_Message			= CreateConVar("server_message", "", "Server message.", FCVAR_PLUGIN | FCVAR_NOTIFY);
	Server_Message_Interval	= CreateConVar("server_message_interval", "5.0", "Interval between server messages.", FCVAR_PLUGIN | FCVAR_NOTIFY);
	
	Server_Rotoblin_Enable	= CreateConVar("server_rotoblin_enable", "1", "Enable Rotoblin exec.", FCVAR_PLUGIN | FCVAR_NOTIFY);
	Server_Rotoblin_Cfg		= CreateConVar("server_rotoblin_cfg", "comp.cfg", "Name of Rotoblin cfg.", FCVAR_PLUGIN | FCVAR_NOTIFY);
	
	HookConVarChange(Server_Message_Interval, Server_Message_Interval_Cvar);
	
	//register cmds
	RegConsoleCmd("sm_rotoblin", Server_Rotoblin, "Exec Rotoblin Cfg.");
	RegConsoleCmd("sm_rotoblin_off", Server_Rotoblin_Off, "Exec Default Cfg.");
}


//server message
new Handle:Timer = INVALID_HANDLE;

Server_Message_Create_Timer()
{
	if(Timer == INVALID_HANDLE)
		Timer = CreateTimer(60.0 * GetConVarFloat(Server_Message_Interval), Server_Message_Show, _, TIMER_REPEAT);
}

public Action:Server_Message_Show(Handle:_Timer)
{
	if(GetConVarBool(Server_Message_Display))
	{
		decl String:Message[512];
		GetConVarString(Server_Message, Message, sizeof(Message));
		
		PrintToChatAll("\x01%s", Message);
	}
	
	return Plugin_Handled;
}

public Server_Message_Interval_Cvar(Handle:cvar, const String:oldValue[], const String:newValue[])
{
	KillTimer(Timer);
	
	Server_Message_Create_Timer();
}


//server rotoblin
#define TEAM_SPECTATOR	1
#define TEAM_SURVIVOR	2
#define TEAM_INFECTED	3

new String:Team_Names[TEAM_INFECTED + 1][64] = {"", "spectator", "survivor", "infected"};

new bool:Rotoblin_Requests[TEAM_INFECTED + 1] = {false, false};

public Action:Server_Rotoblin(client, args)
{
	new Client_Team		= GetClientTeam(client),
		Opposite_Team	= (Client_Team == TEAM_SURVIVOR) ? TEAM_INFECTED : TEAM_SURVIVOR;
	
	if(GetConVarBool(Server_Rotoblin_Enable) && (Client_Team == TEAM_SURVIVOR || Client_Team == TEAM_INFECTED))
	{
		if(!Rotoblin_Requests[Client_Team])
		{
			Rotoblin_Requests[Client_Team] = true;
			
			if(!Rotoblin_Requests[Opposite_Team])
				PrintToChatAll("[Rotoblin] The %s team have requested Rotoblin exec, the %s team must agree by typing !rotoblin.", Team_Names[Client_Team], Team_Names[Opposite_Team]);
			else if(Rotoblin_Requests[TEAM_SURVIVOR] && Rotoblin_Requests[TEAM_INFECTED])
			{
				PrintToChatAll("[Rotoblin] The %s team have agreed to a Rotoblin exec, Rotoblin will now be loaded.", Team_Names[Client_Team], Team_Names[Opposite_Team]);
				
				decl String:Roto_Cfg[64];
				GetConVarString(Server_Rotoblin_Cfg, Roto_Cfg, sizeof(Roto_Cfg));
				
				ServerCommand("exec %s", Roto_Cfg); //sm_exec
				//1v1, 2v2, 3v3, 4v4...?
				decl String:XvX_Cfg[4];
				GetCmdArg(1, XvX_Cfg, sizeof(XvX_Cfg));
				
				if(StrEqual(XvX_Cfg, "1v1", false) || StrEqual(XvX_Cfg, "2v2", false) || StrEqual(XvX_Cfg, "3v3", false) || StrEqual(XvX_Cfg, "4v4", false))
					ServerCommand("exec %s.cfg", XvX_Cfg); //sm_exec
				else
					PrintToChat(client, "[Rotoblin] Invalid XvX cfg specified.");
			}
		}
		else
			PrintToChat(client, "[Rotoblin] Your team has already requested a Rotoblin exec.");
	}
	else
	{
		PrintToChat(client, "[Rotoblin] You cannot request rotoblin exec.");
	}
	
	return Plugin_Handled;
}

public Action:Server_Rotoblin_Off(client, args)
{
	PrintToChat(client, "[Rotoblin] Not implemented yet.");
	
	return Plugin_Handled;
}

//global init
public OnMapStart()
{
	//server message
	Server_Message_Create_Timer();
	
	//server rotoblin
	//Rotoblin_Requests = {false, false};
	Rotoblin_Requests[TEAM_SURVIVOR] = false;
	Rotoblin_Requests[TEAM_INFECTED] = false;
}