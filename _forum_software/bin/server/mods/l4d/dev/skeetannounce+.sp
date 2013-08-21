#include <sourcemod>

#define PLUGIN_VERSION "1.0.0"
#define FL_CUMSKEET 1
#define FL_DEADSTOPS 2
#define FL_ALLSKEET 4
#define HITGROUP_HEAD 1

new pouncing[MAXPLAYERS + 1];
//Skeet damage variables
new damageDone[MAXPLAYERS + 1][MAXPLAYERS + 1];
new hitsDone[MAXPLAYERS + 1][MAXPLAYERS + 1];
new headshots[MAXPLAYERS + 1][MAXPLAYERS + 1];

new SkeetStats[MAXPLAYERS + 1][4];
#define SKEETSTAT_DEADSTOPS 0
#define SKEETSTAT_SKEETS 1
#define SKEETSTAT_HEADSHOTS 2
#define SKEETSTAT_CR0WNS 3

public Plugin:myinfo = 
{
	name		= "Skeet Announce",
	author		= "jackpf",
	description	= "Announces skeets, deadstops, headshots, cr0wns etc...",
	version		= PLUGIN_VERSION,
	url			= "http://jackpf.co.uk"
}

new Handle:hSkeetAnnounce = INVALID_HANDLE;

public OnPluginStart()
{
	CreateConVar("l4d_skeetannounce_version", PLUGIN_VERSION, "Skeet announce version", FCVAR_PLUGIN | FCVAR_SPONLY | FCVAR_REPLICATED | FCVAR_NOTIFY | FCVAR_DONTRECORD);
	hSkeetAnnounce = CreateConVar("l4d_skeetannounce", "0", "Skeet announce.", FCVAR_PLUGIN | FCVAR_SPONLY | FCVAR_NOTIFY);
	
	HookEvent("player_shoved", Event_PlayerShoved);
	HookEvent("player_hurt", Event_PlayerHurt);
	HookEvent("ability_use", Event_AbilityUse);
	HookEvent("player_death", Event_PlayerDeath);
	HookEvent("witch_killed", Event_WitchKilled);
	
	RegConsoleCmd("sm_skeetannounce_stats", Cmd_SkeetAnnounce_Stats);
}


public OnClientConnected(client)
{
	if(IsClient(client))
		SkeetStats[client] = {0, 0, 0, 0};
}
public OnMapEnd()
{
	new String:SkeetStatsFile[PLATFORM_MAX_PATH];
	BuildPath(Path_SM, SkeetStatsFile, sizeof(SkeetStatsFile), "/gamedata/skeetannounce_stats.txt");
	
	if(!FileExists(SkeetStatsFile))
		OpenFile(SkeetStatsFile, "w");
	
	new Handle:SkeetStatsKV = CreateKeyValues("skeetannounce_stats"), Handle:SkeetStatsKV_Persist = INVALID_HANDLE;
	
	FileToKeyValues(SkeetStatsKV_Persist, SkeetStatsFile);
	
	if(KvGotoFirstSubKey(SkeetStatsKV_Persist))
	{
		new String:Auth[64];
		KvGetSectionName(SkeetStatsKV_Persist, Auth, sizeof(Auth));
		KvSetNum(SkeetStatsKV, "deadstops", KvGetNum(SkeetStatsKV_Persist, "deadstops"));
		KvSetNum(SkeetStatsKV, "skeets", KvGetNum(SkeetStatsKV_Persist, "skeets"));
		KvSetNum(SkeetStatsKV, "headshots", KvGetNum(SkeetStatsKV_Persist, "headshots"));
		KvSetNum(SkeetStatsKV, "cr0wns", KvGetNum(SkeetStatsKV_Persist, "cr0wns"));
	}
	
	for(new i = 1; i < MaxClients; i++)
	{
		if(IsClient(i))
		{
			new String:SteamID[64];
			GetClientAuthString(i, SteamID, sizeof(SteamID));
			
			KvSetSectionName(SkeetStatsKV, SteamID);
			KvSetNum(SkeetStatsKV, "deadstops", SkeetStats[i][SKEETSTAT_DEADSTOPS]);
			KvSetNum(SkeetStatsKV, "skeets", SkeetStats[i][SKEETSTAT_SKEETS]);
			KvSetNum(SkeetStatsKV, "headshots", SkeetStats[i][SKEETSTAT_HEADSHOTS]);
			KvSetNum(SkeetStatsKV, "cr0wns", SkeetStats[i][SKEETSTAT_CR0WNS]);
		}
	}
	
	KeyValuesToFile(SkeetStatsKV, SkeetStatsFile);
	
	CloseHandle(SkeetStatsKV);
}



public Event_PlayerShoved(Handle:event, const String:name[], bool:dontBroadcast)
{
	new victim = GetClientOfUserId(GetEventInt(event, "userid"));
	new attacker = GetClientOfUserId(GetEventInt(event, "attacker"));
	
	//If the hunter lands on another player's head, they're technically grounded.
	//Instead of using isGrounded, this uses the pouncing[] array with less precise timer
	if(pouncing[victim])
	{
		//Dead Stop
		pouncing[victim] = false; //Hunter was deadstopped so he can no longer be pouncing
		
		//if(GetConVarBool(hSkeetAnnounce))
			PrintToChatAll("\x03%N deadstopped %N", attacker, victim);
		
		SkeetStats[attacker][SKEETSTAT_DEADSTOPS]++;
	}
}


public Event_PlayerHurt(Handle:event, const String:name[], bool:dontBroadcast)
{
	new user = GetClientOfUserId(GetEventInt(event, "userid"));
	
	if(pouncing[user])
	{ 
		new attacker = GetClientOfUserId(GetEventInt(event, "attacker"));
		new victim = GetClientOfUserId(GetEventInt(event, "userid"));
		new damage = GetEventInt(event, "dmg_health");
		new hitGroup = GetEventInt(event, "hitgroup");
		new String: weapon[MAX_NAME_LENGTH];

		GetEventString(event, "weapon", weapon, sizeof(weapon));
		if(isAcceptableWeapon(weapon))
		{
			if(!IsPlayerAlive(victim) || GetClientHealth(victim) <= 0)
			{
				pouncing[victim] = false; //Hunter is dead so can no longer be pouncing
				
				damageDone[victim][attacker] += damage;
				hitsDone[victim][attacker]++;
				if(hitGroup == HITGROUP_HEAD)
				{
					headshots[victim][attacker]++;
					SkeetStats[attacker][SKEETSTAT_HEADSHOTS]++;
				}
				SkeetStats[attacker][SKEETSTAT_SKEETS]++;
			}
		}
	}
}

public Event_PlayerDeath(Handle:event, const String:name[], bool:dontBroadcast)
{
	new victim = GetClientOfUserId(GetEventInt(event, "userid"));
	
	if(pouncing[victim])
	{
		for(new i = 0; i < MaxClients;i++)
		{
			if(hitsDone[victim][i] > 0)
			{
				if(headshots[victim][i] > 0)
					//if(GetConVarBool(hSkeetAnnounce))
						PrintToChatAll(/*"\x03%N skeeted %N, landing %d bullet(s) with %d headshot(s) for %d damage"*/"\x03%N headshotted %N", i, victim /*,hitsDone[victim][i], headshots[victim][i], damageDone[victim][i]*/);
				else
					//if(GetConVarBool(hSkeetAnnounce))
						PrintToChatAll(/*"\x03%N skeeted %N, landing %d bullet(s) for %d damage"*/"\x03%N skeeted %N", i, victim /*,hitsDone[victim][i], damageDone[victim][i]*/);
				
				hitsDone[victim][i] = 0;
				damageDone[victim][i] = 0;
				headshots[victim][i] = 0;
			}
		}
	}
}

public Event_WitchKilled(Handle:event, const String:name[], bool:dontBroadcast)
{
	new attacker = GetClientOfUserId(GetEventInt(event, "userid"));
	
	if(/*GetConVarBool(hSkeetAnnounce) && */IsClient(attacker) && GetEventBool(event, "oneshot"))
	{
		SkeetStats[attacker][SKEETSTAT_CR0WNS]++;
		
		PrintToChatAll("\x03%N cr0wnd the witch", attacker);
	}
}

public Event_AbilityUse(Handle:event, const String:name[], bool:dontBroadcast)
{
	new user = GetClientOfUserId(GetEventInt(event, "userid"));
	new String:abilityName[64];
	
	GetEventString(event, "ability", abilityName, sizeof(abilityName));
	if(IsClient(user) && strcmp(abilityName, "ability_lunge", false) == 0 && !pouncing[user])
	{
		//Hunter pounce
		pouncing[user] = true;
		CreateTimer(0.5, groundTouchTimer, user, TIMER_REPEAT);
	}
	else if(pouncing[user])
	{
		//Hunter is pouncing again so he must have already landed and the timer didn't catch it
		for(new i = 1; i <= MaxClients; i++)
		{
			hitsDone[user][i] = 0;
			damageDone[user][i] = 0;
			headshots[user][i] = 0;
		}
	}
}

public Action:groundTouchTimer(Handle:timer, any:client)
{
	if(IsClient(client) && (isGrounded(client) || !IsPlayerAlive(client)))
	{
		//Reached the ground or died in mid-air
		pouncing[client] = false;
		KillTimer(timer);
	}
}

public bool:isGrounded(client)
{
	return ((GetEntProp(client, Prop_Data, "m_fFlags") & FL_ONGROUND) > 0);
}

public bool:IsClient(client)
{
	return (IsClientConnected(client) && IsClientInGame(client) && !IsFakeClient(client));
}

public bool:isAcceptableWeapon(const String:weapon[])
{
	if(strcmp(weapon,"autoshotgun") == 0 || strcmp(weapon, "smg") == 0 || strcmp(weapon,"rifle") == 0 || 
	strcmp(weapon,"pumpshotgun") == 0 || strcmp(weapon, "hunting_rifle") == 0 || strcmp(weapon,"pistol") == 0 ||
	strcmp(weapon,"pipe_bomb") == 0 || strcmp(weapon, "prop_minigun") == 0)
		return true;
	else
		return false;
}

public Action:Cmd_SkeetAnnounce_Stats(client, args)
{
	PrintToChat(client, "\x03%N's Skeet Stats:", client);
	PrintToChat(client, "	Deadstops: %i", SkeetStats[client][SKEETSTAT_DEADSTOPS]);
	PrintToChat(client, "	Skeets: %i", SkeetStats[client][SKEETSTAT_SKEETS]);
	PrintToChat(client, "	Headshots: %i", SkeetStats[client][SKEETSTAT_HEADSHOTS]);
	PrintToChat(client, "	Cr0wns: %i", SkeetStats[client][SKEETSTAT_CR0WNS]);
	
	return Plugin_Handled;
}
public Action:Cmd_SkeetAnnounce_Stats2(client, args)
{
	new SkeetStats2[4];
	
	new String:SkeetStatsFile[PLATFORM_MAX_PATH];
	BuildPath(Path_SM, SkeetStatsFile, sizeof(SkeetStatsFile), "/gamedata/skeetannounce_stats.txt");
	
	new String:SteamID[64];
	GetClientAuthString(client, SteamID, sizeof(SteamID));
	
	if(FileExists(SkeetStatsFile))
	{
		new Handle:SkeetStatsKV_Persist = INVALID_HANDLE;
		
		FileToKeyValues(SkeetStatsKV_Persist, SkeetStatsFile);
		
		if(KvJumpToKey(SkeetStatsKV_Persist, SteamID))
		{
			new String:Auth[64];
			KvGetSectionName(SkeetStatsKV_Persist, Auth, sizeof(Auth));
			SkeetStats2[SKEETSTAT_DEADSTOPS] = KvGetNum(SkeetStatsKV_Persist, "deadstops");
			SkeetStats2[SKEETSTAT_SKEETS] = KvGetNum(SkeetStatsKV_Persist, "skeets");
			SkeetStats2[SKEETSTAT_HEADSHOTS] = KvGetNum(SkeetStatsKV_Persist, "headshots");
			SkeetStats2[SKEETSTAT_CR0WNS] = KvGetNum(SkeetStatsKV_Persist, "cr0wns");
		}
		
		CloseHandle(SkeetStatsKV_Persist);
	}
	
	PrintToChat(client, "\x03%N's Skeet Stats2:", client);
	PrintToChat(client, "	Deadstops: %i", SkeetStats2[SKEETSTAT_DEADSTOPS]);
	PrintToChat(client, "	Skeets: %i", SkeetStats2[SKEETSTAT_SKEETS]);
	PrintToChat(client, "	Headshots: %i", SkeetStats2[SKEETSTAT_HEADSHOTS]);
	PrintToChat(client, "	Cr0wns: %i", SkeetStats2[SKEETSTAT_CR0WNS]);
	
	return Plugin_Handled;
}