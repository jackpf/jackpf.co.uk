//sdk tools
#include <sourcemod>
#include <sdktools>
#include <sdkhooks.inc>
#include <socket>

//plugin version
#define PLUGIN_VERSION "0.7.7"
//plugin debugging
#define PLUGIN_DEBUG 0

//plugin info
public Plugin:myinfo = 
{
	name		= "Rotoblin Hardcore",
	author		= "jackpf",
	description	= "Rotoblin hardcore mode.",
	version		= PLUGIN_VERSION,
	url			= "http://jackpf.co.uk"
}

//plugin setup
new Handle:Rotoblin_Enable			= INVALID_HANDLE;
new Handle:Rotoblin_Hardcore_Enable	= INVALID_HANDLE;

public OnPluginStart()
{
	//require Left 4 Dead
	decl String:Game[64];
	GetGameFolderName(Game, sizeof(Game));
	if(!StrEqual(Game, "left4dead", false))
		SetFailState("Plugin supports Left 4 Dead only.");
	
	//register cvars
	CreateConVar("rotoblin_hardcore_version", PLUGIN_VERSION, "Rotoblin Hardcore version.", FCVAR_PLUGIN | FCVAR_SPONLY | FCVAR_NOTIFY);
	Rotoblin_Enable = FindConVar("rotoblin_enable");
	Rotoblin_Hardcore_Enable = CreateConVar("rotoblin_hardcore_enable", "0", "Sets whether rotoblin hardcore is enabled.", FCVAR_PLUGIN | FCVAR_NOTIFY);
	if(Rotoblin_Enable == INVALID_HANDLE || Rotoblin_Hardcore_Enable == INVALID_HANDLE)
		SetFailState("Plugin must be loaded after Rotoblin.");
	
	//hook events
	HookEvent("round_start", Rotoblin_Hardcore);
	HookEvent("player_use", Rotoblin_Hardcore_Weapons);
}

//hardcore
new bool:Rotoblin_Hardcore_Complete = false;
public Action:Rotoblin_Hardcore(Handle:event, const String:name[], bool:dontBroadcast) //for late spawns
{
	if(GetConVarBool(Rotoblin_Enable) && GetConVarBool(Rotoblin_Hardcore_Enable))
	{
		#if PLUGIN_DEBUG
			PrintToChatAll("[Rotoblin] Giving items.");
		#endif
		
		new String:Cmd_Give[] = "give";
		new Cmd_Flags = GetCommandFlags(Cmd_Give);
		SetCommandFlags(Cmd_Give, Cmd_Flags & ~FCVAR_CHEAT);
		for(new i = 1; i <= MaxClients; i++)
		{
			if(IsClientConnected(i) && IsClientInGame(i) && GetClientTeam(i) == 2 /*survivors*/)
			{
				FakeClientCommand(i, "give pain_pills");
				FakeClientCommand(i, "give pumpshotgun"); //:D
			}
		}
		SetCommandFlags(Cmd_Give, Cmd_Flags);
		
		CreateTimer(1.0, Rotoblin_Hardcore2); //some fucking epic late spawns -_-
	}
	
	return Plugin_Handled;
}
public Action:Rotoblin_Hardcore2(Handle:timer)
{
	if(GetConVarBool(Rotoblin_Enable) && GetConVarBool(Rotoblin_Hardcore_Enable))
	{
		Rotoblin_Hardcore_Complete = false;
		
		//Rotoblin_Hardcore_Roundstart, CreateTimer(0.1, Rotoblin_Hardcore_Items)
		new Entity = -1, Removed = 0;
		
		#if PLUGIN_DEBUG
			PrintToChatAll("[Rotoblin] Removing non-saferoom pill spawns.");
		#endif
		
		/*new Float:SaferoomLocation[3];
		new SaferoomEnt = FindEntityByClassname(-1, "info_survivor_position");
		if(!IsValidEntity(SaferoomEnt))
		{
			new SaferoomEnt2 = -1;
			while((SaferoomEnt2 = FindEntityByClassnameEx(SaferoomEnt2, "prop_door_rotating_checkpoint")) != -1)
				if(GetEntProp(SaferoomEnt2, Prop_Data, "m_bLocked") == 1)
					SaferoomEnt = SaferoomEnt2;
		}
		
		if(IsValidEntity(SaferoomEnt))
		{
			GetEntPropVector(SaferoomEnt, Prop_Send, "m_vecOrigin", SaferoomLocation);
			while((Entity = FindEntityByClassnameEx(Entity, "weapon_pain_pills_spawn")) != -1)
			{
				new Float:EntityLocation[3];
				GetEntPropVector(Entity, Prop_Send, "m_vecOrigin", EntityLocation);
				if(GetVectorDistance(EntityLocation, SaferoomLocation) > 500)
				{
					RemoveEdict(Entity);
					Removed++;
				} 
			}
		}*/
		while((Entity = FindEntityByClassnameEx(Entity, "weapon_pain_pills_spawn")) != -1)
		{
			RemoveEdict(Entity);
			Removed++;
		}
		while((Entity = FindEntityByClassnameEx(Entity, "weapon_first_aid_kit_spawn")) != -1) //incase they've not been converted yet?
		{
			RemoveEdict(Entity);
			Removed++;
		}
		
		#if PLUGIN_DEBUG
			PrintToChatAll("[Rotoblin] Removed %i pill spawns.", Removed);
		#endif
		
		Entity = -1;
		Removed = 0;
		
		#if PLUGIN_DEBUG
			PrintToChatAll("[Rotoblin] Removing molotov spawns.");
		#endif

		while((Entity = FindEntityByClassnameEx(Entity, "weapon_molotov_spawn")) != -1)
		{
			RemoveEdict(Entity);
			Removed++;
		}
		
		#if PLUGIN_DEBUG
			PrintToChatAll("[Rotoblin] Removed %i molotov spawns.", Removed);
		#endif
		
		Entity = -1;
		Removed = 0;
		
		#if PLUGIN_DEBUG
			PrintToChatAll("[Rotoblin] Removing pipebomb spawns.");
		#endif
		
		while((Entity = FindEntityByClassnameEx(Entity, "weapon_pipe_bomb_spawn")) != -1)
		{
			RemoveEdict(Entity);
			Removed++;
		}
		
		#if PLUGIN_DEBUG
			PrintToChatAll("[Rotoblin] Removed %i pipebomb spawns.", Removed);
		#endif
		
		Entity = -1;
		Removed = 0;
		
		#if PLUGIN_DEBUG
			PrintToChatAll("[Rotoblin] Removing gascan spawns.");
		#endif
		
		decl String:ModelName[128];
		
		while((Entity = FindEntityByClassnameEx(Entity, "prop_physics")) != -1) 
		{
			GetEntPropString(Entity, Prop_Data, "m_ModelName", ModelName, sizeof(ModelName));
			if((StrContains(ModelName, "gascan", false) != -1 ||
				StrContains(ModelName, "propane", false) != -1 ||
				StrContains(ModelName, "oxygen", false) != -1)
				&& (bool:GetEntProp(Entity, Prop_Send, "m_isCarryable", 1)))
			{
				RemoveEdict(Entity);
				Removed++;
			}
		}
		
		#if PLUGIN_DEBUG
			PrintToChatAll("[Rotoblin] Removed %i gascan spawns.", Removed);
		#endif
		
		Rotoblin_Hardcore_Complete = true;
	}
}
public OnEntityCreated(entity, const String:classname[]) //late spawned...sanity?
{
	if(GetConVarBool(Rotoblin_Enable) && GetConVarBool(Rotoblin_Hardcore_Enable))
	{
		if(Rotoblin_Hardcore_Complete)
		{
			if(StrEqual(classname, "weapon_pain_pills_spawn") || StrEqual(classname, "weapon_first_aid_kit_spawn") ||
			StrEqual(classname, "weapon_molotov_spawn") ||
			StrEqual(classname, "weapon_pipe_bomb_spawn"))
			{
				CreateTimer(0.1, OnEntityCreated_Delayed, entity);
			}
			else if(StrEqual(classname, "prop_physics")) 
			{
				decl String:ModelName[128];
				GetEntPropString(entity, Prop_Data, "m_ModelName", ModelName, sizeof(ModelName));
				if((StrContains(ModelName, "gascan", false) != -1 ||
					StrContains(ModelName, "propane", false) != -1 ||
					StrContains(ModelName, "oxygen", false) != -1)
					&& (bool:GetEntProp(entity, Prop_Send, "m_isCarryable", 1)))
				{
					CreateTimer(0.1, OnEntityCreated_Delayed, entity);
				}
			}
		}
	}
}
public Action:OnEntityCreated_Delayed(Handle:timer, any:entity)
{
	if(IsValidEdict(entity))
		RemoveEdict(entity);
	
	#if PLUGIN_DEBUG
		PrintToChatAll("[Rotoblin] Removed 1 late item spawn.");
	#endif
	
	KillTimer(timer);
	return Plugin_Handled;
}

new String:Player_Weapons[MAXPLAYERS + 1][64];
public Action:Rotoblin_Hardcore_Weapons(Handle:event, const String:name[], bool:dontBroadcast)
{
	if(GetConVarBool(Rotoblin_Enable) && GetConVarBool(Rotoblin_Hardcore_Enable))
	{
		decl String:Weapon_Name[64];
		new Client = GetClientOfUserId(GetEventInt(event, "userid"));
		new Weapon = GetPlayerWeaponSlot(Client, 0);
		
		if(IsValidEdict(Weapon))
		{
			GetEdictClassname(Weapon, Weapon_Name, sizeof(Weapon_Name));
			
			if(StrEqual(Weapon_Name, "weapon_hunting_rifle", false))
			{
				#if PLUGIN_DEBUG
					//PrintToChatAll("[Rotoblin] HR picked up!");
				#endif
				
				decl String:iWeapon_Name[64];
				new HR_Count = 0, iWeapon;
				
				for(new i = 1; i <= MaxClients; i++)
				{
					if(IsClientConnected(i) && IsPlayerAlive(i))
					{
						iWeapon = GetPlayerWeaponSlot(i, 0);
						GetEdictClassname(iWeapon, iWeapon_Name, sizeof(iWeapon_Name));
						
						if(StrEqual(iWeapon_Name, "weapon_hunting_rifle", false))
						{
							#if PLUGIN_DEBUG
								//PrintToChatAll("[Rotoblin] %N has a HR.", i);
							#endif
							
							HR_Count++;
						}
						else
						{
							#if PLUGIN_DEBUG
								//PrintToChatAll("[Rotoblin] %N does not have a HR.", i);
							#endif
						}
					}
				}
				
				#if PLUGIN_DEBUG
					//PrintToChatAll("[Rotoblin] HR count is %i.", HR_Count);
				#endif
				
				if(HR_Count > 1)
				{
					#if PLUGIN_DEBUG
						//PrintToChatAll("[Rotoblin] %i HRs, use stopped.", HR_Count);
					#endif
					
					if(IsValidEdict(Weapon))
					{
						#if PLUGIN_DEBUG
							//PrintToChatAll("[Rotoblin] Removing weapon, giving previous weapon.");
						#endif
						
						RemoveEdict(Weapon);
						
						new Flags = GetCommandFlags("give");
						SetCommandFlags("give", Flags ^ FCVAR_CHEAT);
						FakeClientCommand(Client, "give %s", (!StrEqual(Player_Weapons[Client], "")) ? Player_Weapons[Client] : "pumpshotgun");
						SetCommandFlags("give", Flags);
					}
					
					PrintToChat(Client, "[Rotoblin] Only 1 HR is allowed.");
				}
				else
				{
					#if PLUGIN_DEBUG
						//PrintToChatAll("[Rotoblin] 0 HRs, use allowed.");
					#endif
				}
			}
			else if(StrEqual(Weapon_Name, "weapon_pumpshotgun", false) || StrEqual(Weapon_Name, "weapon_smg", false)) //weapon tracking
			{
				Player_Weapons[Client] = Weapon_Name;
			}
		}
	}
	
	return Plugin_Handled;
}

//version
static const String:SOCK_URL[]	= "jackpf.co.uk";
static const String:SOCK_FILE[]	= "/bin/server/mods/release/Rotoblin_Hardcore/version.txt";
new String:NewVersion[32]		= "";
public OnMapStart()
{
	if(GetConVarBool(Rotoblin_Enable) && GetConVarBool(Rotoblin_Hardcore_Enable))
	{
		decl String:MapName[64];
		GetCurrentMap(MapName, sizeof(MapName));
		
		if(StrContains(MapName, "01_") != -1)
		{
			#if PLUGIN_DEBUG
				PrintToChatAll("[Rotoblin] Checking latest version.");
			#endif
			
			new Handle:Socket = SocketCreate(SOCKET_TCP, Sock_OnSocketError);
			SocketConnect(Socket, Sock_OnSocketConnected, Sock_OnSocketReceive, Sock_OnSocketDisconnected, SOCK_URL, 80);
		}
	}
}
public Sock_OnSocketConnected(Handle:socket, any:arg)
{
	decl String:RequestStr[512];
	Format(RequestStr, sizeof(RequestStr), "GET /%s HTTP/1.0\r\nHost: %s\r\nConnection: close\r\n\r\n", SOCK_FILE, SOCK_URL);
	SocketSend(socket, RequestStr);
}
public Sock_OnSocketReceive(Handle:socket, String:receiveData[], const dataSize, any:hFile)
{
	new ver_offset, ver_count, itemp;
	
	if(StrContains(receiveData, "200 OK", false) != -1)
	{
		while((itemp = FindPatternInString(receiveData[ver_offset], "\r\n\r\n")) != -1) 
			ver_offset += itemp + 4;
		ver_count = CountCharsInString(receiveData[ver_offset], '\n') + 1;
		
		decl String:ver_buf[ver_count][32];
		ExplodeString(receiveData[ver_offset], "\n", ver_buf, ver_count, 32);
		
		for(new i; i < ver_count; i++)
		{
			TrimString(ver_buf[i]);
			if(StrEqual(ver_buf[i], PLUGIN_VERSION))
				return;
			else
				Format(NewVersion, sizeof(NewVersion), ver_buf[i]);
		}
	}
	
	CreateTimer(10.0, Version_Display);
}
public Sock_OnSocketDisconnected(Handle:socket, any:arg)
{
	CloseHandle(socket);
}
public Sock_OnSocketError(Handle:socket, const errorType, const errorNum, any:hFile)
{
	if(hFile != INVALID_HANDLE)
		CloseHandle(hFile);
	if(socket != INVALID_HANDLE)
		CloseHandle(socket);
}
public Action:Version_Display(Handle:timer)
{
	decl String:Message[128];
	if(!StrEqual(NewVersion, ""))
		Format(Message, sizeof(Message), "[Rotoblin] Hardcore outdated. Newest version: %s.", NewVersion);
	else
		Format(Message, sizeof(Message), "[Rotoblin] Unable to retrieve latest Hardcore version.");
	
	PrintToChatAll(Message);
	LogMessage(Message);
	
	return Plugin_Handled;
}

//api
stock FindEntityByClassnameEx(startEnt, const String:classname[])
{
	while(startEnt > -1 && !IsValidEntity(startEnt))
		startEnt--;
	return FindEntityByClassname(startEnt, classname);
}
stock FindPatternInString(const String:str[], const String:pattern[], bool:reverse = false)
{
	new i, c, len;
	
	len = strlen(pattern);
	c = pattern[0];
	while(i < len && (i = FindCharInString(str[i], c, reverse)) != -1)
		if(strncmp(str[i], pattern, len))
			return i;
	return -1;
}
stock CountCharsInString(const String:str[], c)
{
	new off, i, cnt, len = strlen(str);
	
	while(i < len && (off = FindCharInString(str[i], c)) != -1)
	{
		cnt++;
		i += off + 1;
	}
	return cnt;
}