/*
 * ============================================================================
 *
 *  File:			pumpswap.sp
 *  Type:			Module
 *  Description:	Blocks a pump shotgun exploit which allows survivors to
 *					shoot faster than normal.
 *					This exploit is only for L4D.
 *
 *  Copyright (C) 2010  Mr. Zero <mrzerodk@gmail.com>
 *  This file is part of ZACK.
 *
 *  ZACK is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  ZACK is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with ZACK.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ============================================================================
 */

/*
 * ==================================================
 *                    Preprocessor
 * ==================================================
 */

/* Don't include module if SDKHooks is not used */
#if !defined USE_SDKHOOKS

/* Adding plugin start function here so main script doesnt get a unknown 
 *function error upon compiling */
public _PumpSwap_OnPluginStart() return;

/* End input of file */
#endinput

#endif

/*
 * ==================================================
 *                     Variables
 * ==================================================
 */

/*
 * --------------------
 *       Private
 * --------------------
 */

static	const	String:	WEAPON_PUMPSHOTGUN[]				= "weapon_pumpshotgun"; // Used for weapon swaping
static	const	String:	PUMPSHOTGUN[]						= "pumpshotgun"; // Used for weapon fire
static	const	String:	NETPROP_NEEDPUMP[]					= "m_needPump"; // Net prop for the survivor to re pump their shotgun before it can be used again

static	const	Float:	PUMPSHOTGUN_COOLDOWN				= 1.0; // How long we will track the weapon fire for

static			bool:	g_bInCooldown[MAXPLAYERS + 1]		= {false}; // In cooddown after weapon fire
static			bool:	g_bSwapedWeapon[MAXPLAYERS + 1]		= {false}; // If client swaped weapon and back to pumpshotgun
static			bool:	g_bIsShotgunActive[MAXPLAYERS + 1]	= {false}; // And if the clients current active weapon is the shotgun
static			bool:	g_bHaveLogged[MAXPLAYERS + 1]		= {false}; // To prevent log spam

/*
 * ==================================================
 *                     Forwards
 * ==================================================
 */

/**
 * Called on plugin start.
 *
 * @noreturn
 */
public _PumpSwap_OnPluginStart()
{
	/* Don't enable module if game isn't Left 4 Dead */
	if (!IsGameLeft4Dead()) return;

	HookGlobalForward(FWD_ON_PLUGIN_ENABLED, _PS_OnPluginEnabled);
	HookGlobalForward(FWD_ON_PLUGIN_DISABLED, _PS_OnPluginDisabled);
}

/**
 * Called on plugin enabled.
 *
 * @noreturn
 */
public _PS_OnPluginEnabled()
{
	HookGlobalForward(FWD_ON_CLIENT_PUT_IN_SERVER, _PS_OnClientPutInServer);
	HookGlobalForward(FWD_ON_CLIENT_DISCONNECT, _PS_OnClientDisconnect);
	HookGlobalForward(FWD_ON_PLAYER_RUN_CMD, _PS_OnPlayerRunCmd);
	HookEvent("weapon_fire", _PS_OnWeaponFire_Event, EventHookMode_Post);

	FOR_EACH_HUMAN(client)
	{
		SDKHook(client, SDKHook_WeaponSwitch, _PS_OnWeaponSwitch);
	}
}

/**
 * Called on plugin disabled.
 *
 * @noreturn
 */
public _PS_OnPluginDisabled()
{
	UnhookGlobalForward(FWD_ON_CLIENT_PUT_IN_SERVER, _PS_OnClientPutInServer);
	UnhookGlobalForward(FWD_ON_CLIENT_DISCONNECT, _PS_OnClientDisconnect);
	UnhookGlobalForward(FWD_ON_PLAYER_RUN_CMD, _PS_OnPlayerRunCmd);
	UnhookEvent("weapon_fire", _PS_OnWeaponFire_Event, EventHookMode_Post);

	FOR_EACH_HUMAN(client)
	{
		SDKUnhook(client, SDKHook_WeaponSwitch, _PS_OnWeaponSwitch);
	}
}

/**
 * Called on client put in server.
 *
 * @param client		Client index.
 * @noreturn
 */
public _PS_OnClientPutInServer(client)
{
	if (IsFakeClient(client)) return;
	g_bInCooldown[client] = false;
	g_bSwapedWeapon[client] = false;
	SDKHook(client, SDKHook_WeaponSwitch, _PS_OnWeaponSwitch);
}

/**
 * Called on client disconnect.
 *
 * @param client		Client index.
 * @noreturn
 */
public _PS_OnClientDisconnect(client)
{
	if (IsFakeClient(client) || !IsValidEntity(client)) return;
	SDKUnhook(client, SDKHook_WeaponSwitch, _PS_OnWeaponSwitch);
}

/**
 * Called when weapon fire event is fired.
 *
 * @param event			Handle to event.
 * @param name			String containing the name of the event.
 * @param dontBroadcast	True if event was not broadcast to clients, false 
 *						otherwise.
 * @noreturn
 */
public _PS_OnWeaponFire_Event(Handle:event, const String:name[], bool:dontBroadcast)
{
	new client = GetClientOfUserId(GetEventInt(event, "userid"));
	if (client < CLIENT_INDEX_FIRST || 
		client > CLIENT_INDEX_LAST ||
		g_bIsFake[client] ||
		TeamIndex[client] != TEAM_SURVIVOR ||
		g_bInCooldown[client])
		return;

	decl String:classname[32];
	GetEventString(event, "weapon", classname, sizeof(classname));
	if (!StrEqual(classname, PUMPSHOTGUN)) return;

	g_bInCooldown[client] = true;
	g_bIsShotgunActive[client] = true;
	CreateTimer(PUMPSHOTGUN_COOLDOWN, _PS_Cooldown_Timer, client);
}

/**
 * Called when colddown timer interval has elapsed.
 *
 * @param timer			Handle to the timer object.
 * @param client		Client index.
 * @return				Plugin_Stop.
 */
public Action:_PS_Cooldown_Timer(Handle:timer, any:client)
{
	g_bInCooldown[client] = false;
	g_bSwapedWeapon[client] = false;
	g_bIsShotgunActive[client] = false;
	g_bHaveLogged[client] = false;
	return Plugin_Stop;
}

/**
 * Called on client switches weapon.
 *
 * @param client		Client index.
 * @param weapon		Weapon entity index.
 * @return				Plugin_Continue.
 */
public Action:_PS_OnWeaponSwitch(client, weapon)
{
	if (!g_bInCooldown[client] ||
		weapon < CLIENT_INDEX_FIRST ||
		weapon > MAXENTITIES ||
		!IsValidEntity(weapon))
		return Plugin_Continue;

	decl String:classname[32];
	GetEdictClassname(weapon, classname, 32);
	if (StrEqual(classname, WEAPON_PUMPSHOTGUN))
	{
		g_bSwapedWeapon[client] = true;
		g_bIsShotgunActive[client] = true;
	}
	else
	{
		g_bIsShotgunActive[client] = false;
	}
	return Plugin_Continue;
}

/**
 * Called when a clients movement buttons are being processed.
 *
 * @param client		Index of the client.
 * @param buttons		Copyback buffer containing the current commands (as 
 *						bitflags - see entity_prop_stocks.inc).
 * @param impulse		Copyback buffer containing the current impulse command.
 * @param vel			Players desired velocity.
 * @param angles		Players desired view angles.
 * @param weapon		Entity index of the new weapon if player switches 
 *						weapon, 0 otherwise.
 * @return				Plugin_Handled to block the commands from being 
 *						processed, Plugin_Continue otherwise.
 */
public Action:_PS_OnPlayerRunCmd(client, &buttons, &impulse, Float:vel[3], Float:angles[3], &weapon)
{
	if (g_bInCooldown[client] && // If in cooldown
		g_bSwapedWeapon[client] && // And swaped from shotgun -> another item -> back to shotgun
		g_bIsShotgunActive[client] && // And currently holding the shotgun
		buttons & IN_ATTACK) // And wants to attack
	{
		buttons ^= IN_ATTACK; // remove attack from pressed buttons
		new shotgun = GetPlayerWeaponSlot(client, 0); // Get shotgun entity index
		if (shotgun != -1 && IsValidEntity(shotgun))
		{
			SetEntProp(shotgun, Prop_Send, NETPROP_NEEDPUMP, 1, 1); // Set clients shotgun to need a pump
		}
		if (!g_bHaveLogged[client])
		{
			LogPluginMessage("Forced %L to repump their shotgun after doing the pump swap.", client);
			g_bHaveLogged[client] = true;
		}
	}
	return Plugin_Continue;
}