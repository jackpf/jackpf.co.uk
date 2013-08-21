#pragma semicolon 1

#include <sourcemod>
#include <sdktools>
#include "includes/left4downtown.inc"

enum SIClasses 
{
	SMOKER_CLASS=1,
	BOOMER_CLASS,
	HUNTER_CLASS,
	//SPITTER_CLASS,
	//JOCKEY_CLASS,
	//CHARGER_CLASS,
	WITCH_CLASS,
	TANK_CLASS,
	NOTINFECTED_CLASS
}

static String:SINames[_:SIClasses][] =
{
	"",
	"smoker",
	"boomer",
	"hunter",
	//"spitter",
	//"jockey",
	//"charger",
	"witch",
	"tank",
	""
};

new Handle:hSpecialInfectedHP[_:SIClasses];

stock GetSpecialInfectedHP(class) return GetConVarInt(hSpecialInfectedHP[class]);
stock GetZombieClass(client) return GetEntProp(client, Prop_Send, "m_zombieClass");

new Handle:CVar_Rules = INVALID_HANDLE;

public Plugin:myinfo = 
{
	name = "1v1 Plugin",
	author = "jackpf",
	description = "A plugin designed to support 1v1.",
	version = "3",
	url = "http://jackpf.co.uk"
}

public OnPluginStart()
{
	CVar_Rules = CreateConVar("rotoblin_1v1_rules", "", "1v1 rules.", FCVAR_PLUGIN | FCVAR_SPONLY | FCVAR_NOTIFY);
	CreateTimer(10.0, DisplayRules, _);
	
	HookEvent("player_hurt", PlayerHurt_Event);
	HookEvent("player_jump", PlayerJump_Event);
	
	decl String:buffer[17];
	for (new i = 1; i < _:SIClasses; i++)
	{
		Format(buffer, sizeof(buffer), "z_%s_health", SINames[i]);
		hSpecialInfectedHP[i] = FindConVar(buffer);
	}
}

public Action:PlayerHurt_Event(Handle:event, const String:name[], bool:dontBroadcast)
{
	new attacker = GetClientOfUserId(GetEventInt(event, "attacker"));
	
	if (!attacker) return Plugin_Continue;
	
	new damage = GetEventInt(event, "dmg_health");
	new zombie_class = GetZombieClass(attacker);
	
	if (GetClientTeam(attacker) == 3 && zombie_class != _:TANK_CLASS && damage > 25) 
	{
		new remaining_health = GetClientHealth(attacker);
		PrintToChatAll("[1v1] %N had %d health remaining!", attacker, remaining_health);
		if (remaining_health <= RoundToCeil(GetSpecialInfectedHP(zombie_class) * 0.1))
		{
			new survivor = GetClientOfUserId(GetEventInt(event, "userid"));
			PrintToChat(survivor, "umadbro?");
		}
		ForcePlayerSuicide(attacker);
	}
	
	return Plugin_Continue;
}
public Action:PlayerJump_Event(Handle:event, const String:name[], bool:dontBroadcast)
{
	new Client = GetClientOfUserId(GetEventInt(event, "userid"));
	
	if(GetClientTeam(Client) == 3 /*infected*/ && (GetClientButtons(Client) & IN_JUMP) /*is not using normal wall pounce (using wallkick)*/)
	{
		HookEvent("ability_use", AbilityUse_Event);
		CreateTimer(0.5, GroundTouchTimer, Client, TIMER_REPEAT);
	}
	
	return Plugin_Continue;
}
public Action:GroundTouchTimer(Handle:timer, any:client)
{
	if((isClient(client) && (GetEntProp(client, Prop_Data, "m_fFlags") & FL_ONGROUND) > 0) || !IsPlayerAlive(client))
	{
		UnhookEvent("ability_use", AbilityUse_Event);
		KillTimer(timer);
	}
	
	return Plugin_Continue;
}
public Action:AbilityUse_Event(Handle:event, const String:name[], bool:dontBroadcast)
{
	new Client = GetClientOfUserId(GetEventInt(event, "userid"));
	new String:AbilityName[64];
	
	GetEventString(event, "ability", AbilityName, sizeof(AbilityName));
	if(isClient(Client) && strcmp(AbilityName, "ability_lunge", false) == 0) //pouncing
	{
		ForcePlayerSuicide(Client);
		PrintToChatAll("[1v1] Blocked %N from wallkicking.", Client);
		return Plugin_Stop;
	}
	
	return Plugin_Continue;
}
public bool:isClient(client)
{
	return IsClientConnected(client) && IsClientInGame(client) && !IsFakeClient(client);
}

/*public Action:L4D_OnSpawnTank(const Float:vector[3], const Float:qangle[3])
{
	if (L4D_IsMissionFinalMap()) return Plugin_Continue;
	
	return Plugin_Handled;
}*/

public OnClientPutInServer(client)
{
	CreateTimer(10.0, DisplayRules, client);
}

public Action:DisplayRules(Handle:timer, any:data)
{
	new String:Rules[1024];
	GetConVarString(CVar_Rules, Rules, sizeof(Rules));
	if(!data)
		PrintToChatAll("[1v1] %s", Rules);
	else if(data && IsClientInGame(data))
		PrintToChat(data, "[1v1] %s", Rules);
	
	KillTimer(timer);
	return Plugin_Handled;
}