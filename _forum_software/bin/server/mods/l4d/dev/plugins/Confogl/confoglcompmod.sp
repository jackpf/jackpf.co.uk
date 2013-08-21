#pragma semicolon 1

#include <sourcemod>
#include <sdktools>
//#include <sdkhooks>

#include "includes/BaseVars.sp"
#include "includes/MapInfo.sp"
#include "includes/WepInfo.sp"
#include "includes/ItemRemove.sp"
#include "includes/ReqMatch.sp"
#include "includes/CvarSettings.sp"
#include "includes/GhostTank.sp"
#include "includes/ReqBuffSI.sp"
#include "includes/WaterSlowdown.sp"
#include "includes/UnreserveLobby.sp"
#include "includes/GhostWarp.sp"
#include "includes/UnprohibitBosses.sp"
#include "includes/FinaleAutoSpawnRemoval.sp"
#include "includes/Password.sp"
#include "includes/DisableRoaming.sp"

#define PLUGIN_VERSION	"0.91.2"

new bool:g_bIsCvarsChanged = false;

public Plugin:myinfo = 
{
	name = "Confogl's Competitive Mod",
	author = "Mr. Zero & CanadaRox",
	description = "A competitive mod for L4D2",
	version = PLUGIN_VERSION,
	url = "http://confogl.googlecode.com/"
}

public OnPluginStart()
{
	// Water Slowdown cvar
	g_hWaterSlowdown		= CreateConVar("confogl_waterslowdown"			, "1", "Sets whether water will slowdown the survivors by another 15%",FCVAR_PLUGIN);
	
	// Unreserve lobby cvar
	g_hKillLobbyReservation	= CreateConVar("confogl_killlobbyres"			, "1", "Sets whether the plugin will clear lobby reservation once a match have begun",FCVAR_PLUGIN);
	
	// GhostWarp
	g_hGhostWarp			= CreateConVar("confogl_ghost_warp"				, "1", "Sets whether infected ghosts can right click for warp to next survivor",FCVAR_PLUGIN);
	
	// Unprohibit bosses
	g_hUnprohibitBosses		= CreateConVar("confogl_boss_unprohibit"		, "1", "Enable bosses spawning on all maps, even through they normally aren't allowed",FCVAR_PLUGIN);
	
	// Finale Auto Spawn Removal
	g_hFASR_Enable			= CreateConVar("confogl_ghost_allowfinale"		, "1", "Allow infected players to ghost in finale",FCVAR_PLUGIN);
	
	// Password
	g_hP_Password			= CreateConVar("confogl_password"				, "", "Set a password on the server, if empty password disabled. See Confogl's wiki for more information",FCVAR_PLUGIN|FCVAR_DONTRECORD|FCVAR_PROTECTED);
	
	// Request BuffSI
	RegConsoleCmd("sm_buffsi", RBS_Command_BuffSI);
	RegAdminCmd("sm_forcebuffsi", RBS_Command_ForceBuffSI, ADMFLAG_BAN, "Forces the game to buff the Special Infected");
	
	// Water Slowdown
	HookConVarChange(g_hWaterSlowdown,WS_ConVarChange);
	HookEvent("round_start", WS_RoundStart);
	HookEvent("jockey_ride", WS_JockeyRide);
	HookEvent("jockey_ride_end", WS_JockeyRideEnd);
	
	// UnreserveLobby
	RegAdminCmd("sm_killlobbyres", ForceKillLobbyRes, ADMFLAG_BAN, "Forces the plugin to kill lobby reservation");
	
	// Ghost Warp
	HookEvent("player_death",GW_Event_PlayerDeath);
	HookConVarChange(g_hGhostWarp,GW_ConVarChange);
	RegConsoleCmd("sm_warptosurvivor",GW_Cmd_WarpToSurvivor);
	
	// Finale Auto Spawn Removal
	FASR_SetGhosting();
	HookConVarChange(g_hFASR_Enable,FASR_OnConVarChange);
	
	// Password
	HookConVarChange(g_hP_Password,P_ConVarChange);
	HookEvent("player_disconnect", P_SuppressDisconnectMsg, EventHookMode_Pre);
	
	BV_OnModuleStart();
	MI_OnModuleStart();
	IR_OnModuleStart();
	RM_OnModuleStart();
	
	CS_OnModuleStart();
	GT_OnModuleStart();
	DR_OnModuleStart();
}

public OnPluginEnd()
{
	BV_OnModuleEnd();
	MI_OnModuleEnd();
	IR_OnModuleEnd();
	RM_OnModuleEnd();
	
	CS_OnModuleEnd();
	GT_OnModuleEnd();
	DR_OnModuleEnd();
}

public OnGameFrame()
{
	if(!IsServerProcessing() || !g_bIsPluginEnabled){return;}
	WaterSlowdown();
}

public OnMapStart()
{
	RM_CheckDependencies();
}

public OnMapEnd()
{
	// Water slowdown
	WS_SetStatus(true);
	
	if(g_bIsPluginEnabled && !g_bIsCvarsChanged)
	{
		g_bIsCvarsChanged = true;
		SetCvars(true);
	}
	else if(!g_bIsPluginEnabled && g_bIsCvarsChanged)
	{
		g_bIsCvarsChanged = false;
		SetCvars(false);
		BuffSI(false);
	}
	
	// Password
	P_SetPasswordOnClients();
	
	DR_OnMapEnd();
}

public OnClientDisconnect_Post(client)
{
	GT_SpecHUD_ClientDC(client);
	CreateTimer(10.0, RM_MatchResetTimer);
}

public OnClientDisconnect(client)
{
	DR_OnClientDisconnect(client);
}

public OnClientPutInServer(client)
{
	LobbyUnreserve();
	P_CheckPassword(client);
}

public bool:AskPluginLoad()
{
	MarkNativeAsOptional("L4D_LobbyUnreserve");
	MarkNativeAsOptional("L4D_LobbyIsReserved");
	MarkNativeAsOptional("L4D_ToggleGhostsInFinale");
	return true;
}