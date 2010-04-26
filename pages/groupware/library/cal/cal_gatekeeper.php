<?php
/*
	
	Copyright (c) Reece Pegues
	sitetheory.com

    Reece PHP Calendar is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or 
	any later version if you wish.

    You should have received a copy of the GNU General Public License
    along with this file; if not, write to the Free Software
    Foundation Inc, 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
	
*/


// only allow a call to this file if loaded from index.php
//if ( !defined('CAL_SECURITY_BIT') ) die("Hacking attempt");


# USER SETTINGS AND OPTIONS EXPLAINATIONS
#####################################################################################
$cal_options = array();
$cal_options['timeout'] = 5;				// ( int ) the timeout for logged in users. (initially set low for security)
$cal_options['skin'] = "default"; 			// ( string ) this variable will eventually be the skin used.
$cal_options['language'] = "english"; 		// ( string ) this variable will eventually be the languge file loaded
$cal_options['show_times'] = FALSE;			// ( boolean ) whether to show the starting times on the main page.		
$cal_options['hours_24'] = FALSE;			// ( boolean ) whether to usre 12 or 24 hour clock.
$cal_options['start_monday'] = FALSE;		// ( boolean ) start calendar on monday or sunday.
$cal_options['anon_naming'] = FALSE;		// ( boolean ) allows anonymous users to use an alias when adding events.
//$cal_options['root_password'] = md5(CAL_ROOT_PASSWORD.CAL_SQL_PASSWD_SALT);	// ( string ) this is the default root password.  Used if not already set in the database.

$cal_permissions = array();
$cal_permissions['read'] = FALSE;			//  ( boolean ) allow the user to view the calendar or nt.
$cal_permissions['write'] = FALSE;			//  ( boolean ) allow the user to add/edit events or not.
$cal_permissions['edit'] = FALSE;			//  ( boolean ) allow the user to edit his own events
$cal_permissions['editothers'] = FALSE;		//  ( boolean ) allow users to edit other user's events.
$cal_permissions['editpast'] = FALSE;		//  ( boolean ) allow users to edit the past or not.
$cal_permissions['readothers'] = FALSE;		//  ( boolean ) allow users to view other user's events
$cal_permissions['remind_set'] = FALSE;		//  ( boolean ) allow the user to set reminders on events
$cal_permissions['remind_get'] = FALSE;		//  ( boolean ) allow the user to receive reminders
$cal_permissions['admin'] = FALSE;			//  ( boolean ) if the user is an admin or not.
$cal_permissions['disabled'] = FALSE;		//  ( boolean ) if the user account is disabled or not.
$cal_options['weekendoverride'] = FALSE;		
#####################################################################################




/* ##################################################################
	These functions return true or false according to if the user
	is anonymous, root, or admin.
###################################################################*/
function cal_anon(){
	global $user;
	if($_SESSION['cal_user']=="") return true;
	return false;
}
function cal_root(){
	global $user;
	return logged_user()->isAdministrator();
}
function cal_admin(){
	global $admin;
	return logged_user()->isAdministrator();
}



/* ##################################################################
	These functions return the options or permissions needed.
	Done this way so I don't have to include a global variable in every function.
###################################################################*/
function cal_option($n){
	global $cal_options;
	return $cal_options[$n];
}
function cal_permission($n){
	global $cal_permissions;
	return $cal_permissions[$n];
}
function cal_permission_by_id($n , $idCalendar){
/*	switch ($n){
		case "read":
			ObjectUserPermissions::getAllPermissionsByObjectIdAndManager($idCalendar,'CalendarEvents')
			break;
	}*/
	global $cal_permissions;
	return $cal_permissions[$n];
}



/* ##################################################################
  cal_load_permissions()
   This function fetchs the user's permissions from the database.  If a permission is not found,
   the default from the above is kept (deny).
###################################################################*/
function cal_load_permissions(){
	global $cal_db, $cal_permissions;
	// get user id from session variable.
	$id =  $_SESSION['cal_userid'];
	// if no user ID set, use id=0 (anonymous)
/*	if(!is_numeric($id)){
		$id = 0;
		$_SESSION['cal_userid'] = 0;
	}*/
	// if root set all permissions to true
	if( logged_user()->isAdministrator() ){
		$cal_permissions['read'] = TRUE;
		$cal_permissions['write'] = TRUE;
		$cal_permissions['edit'] = TRUE;
		$cal_permissions['editothers'] = TRUE;
		$cal_permissions['editpast'] = TRUE;
		$cal_permissions['readothers'] = TRUE;
		$cal_permissions['remind_get'] = TRUE;
		$cal_permissions['remind_set'] = TRUE;
		$cal_permissions['admin'] = TRUE;
	}
	// if not root get the user's permissions.  they anonymous user_id is 0
	else{
		// call database
/*		$result = cal_query_permissions($id);
		// put all permissions for this user into a temporary array
		$d = array();
		while($t = $cal_db->sql_fetchrow($result)){
			$d[$t['pname']] = $t['pvalue'];
		}
		// set the permissions if they are set in the DB (notice if not in the DB, it uses the defualts)
		if(array_var($d,'read')=='y') 		$cal_permissions['read'] = TRUE;
		if(array_var($d,'write')=='y') 		$cal_permissions['write'] = TRUE;
		if(array_var($d,'edit')=='y') 		$cal_permissions['edit'] = TRUE;
		if(array_var($d,'editothers')=='y') 	$cal_permissions['editothers'] = TRUE;
		if(array_var($d,'editpast')=='y') 	$cal_permissions['editpast'] = TRUE;
		if(array_var($d,'readothers')=='y') 	$cal_permissions['readothers'] = TRUE;
		if(array_var($d,'remind_get')=='y') 	$cal_permissions['remind_get'] = TRUE;
		if(array_var($d,'remind_set')=='y') 	$cal_permissions['remind_set'] = TRUE;
		if(array_var($d,'admin')=='y') 		$cal_permissions['admin'] = TRUE;
		if(array_var($d,'disabled')=='y') 	$cal_permissions['disabled'] = TRUE;
	*/	
	}
}




/* ##################################################################
  cal_clear_permissions()
   This function clears the user's permissions.  
   It is meant to be used before calling cal_load_permissions() again to refresh the permissions.
###################################################################*/
function cal_clear_permissions(){
	global $cal_permissions;
	// originally set all permissions to false
	$cal_permissions['read'] = FALSE;
	$cal_permissions['write'] = FALSE;
	$cal_permissions['edit'] = FALSE;
	$cal_permissions['editothers'] = FALSE;
	$cal_permissions['editpast'] = FALSE;
	$cal_permissions['readothers'] = FALSE;
	$cal_permissions['remind_get'] = FALSE;
	$cal_permissions['remind_set'] = FALSE;
	$cal_permissions['admin'] = FALSE;
	$cal_permissions['disabled'] = FALSE;
}




/* ##################################################################
  cal_load_options()
   This function sets the options of the calendar saved in the options table.
   note the most important of these is the root password.
   if the root password is remove from the options table, it defaults to that in config.php!
###################################################################*/
function cal_load_options(){
	global $cal_db, $cal_options;
	// call the DB for options
	$result = cal_query_getoptions();
	// get all options
	$d = array();
	while($t = $cal_db->sql_fetchrow($result)){
		$d[$t['opname']] = $t['opvalue'];
	}
	// set the options that have values
	if(array_var($d,'timeout')!="") 		$cal_options['timeout'] = $d['timeout'];
	if(array_var($d,'skin')!="") 			$cal_options['skin'] = $d['skin'];
	if(array_var($d,'language')!="") 		$cal_options['language'] = $d['language'];
	if(array_var($d,'root_password')!="") $cal_options['root_password'] = $d['root_password'];
	// set the options that are true or false
	if(array_var($d,'show_times')=='y') 	$cal_options['show_times'] = TRUE;
	else 						$cal_options['show_times'] = FALSE;
	if(array_var($d,'hours_24')=='y') 	$cal_options['hours_24'] = TRUE;
	else 						$cal_options['hours_24'] = FALSE;
	if(array_var($d,'start_monday')=='y') $cal_options['start_monday'] = TRUE;
	else 						$cal_options['start_monday'] = FALSE;
	if(array_var($d,'anon_naming')=='y') 	$cal_options['anon_naming'] = TRUE;
	else 						$cal_options['anon_naming'] = FALSE;
}








/* ##################################################################
  cal_is_online()
   tells whether a user is logged in or not.
###################################################################*/
function cal_online(){
	// very simple right now, but you can add extra login checks here if you wish to integrate with other sites.
	if( logged_user()->get()!="") return true;
	// if not logged in, set userid to 0 so we can track the anonymous user correctly, then return false
//	else $_SESSION['cal_userid'] = 0;
	return false;
}



/* ##################################################################
  cal_logout()
   remove the user from being online
###################################################################*/
function cal_logout(){
	// remove the session variables that marks the user as logged in.
	// note that I do not destroy the session as that might disrupt other sites this integrates with.
//	$_SESSION['cal_user'] = "";
//	$_SESSION['cal_userid'] = 0;
}




?>