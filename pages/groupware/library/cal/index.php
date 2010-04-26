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

// see if there is already a session, and if not create one.
/*if(session_id()==""){
	session_start();
}*/


// security bit

define("CAL_SECURITY_BIT",1);

// load required files
//require('library/cal/cal_config.php');
require('library/cal/cal_sql_layer.php');
require('library/cal/cal_gatekeeper.php');
require('library/cal/cal_functions.php');


// Make the database connection.
$cal_db = new cal_database(DB_HOST, DB_USER, DB_PASS, DB_NAME, false);
if(!$cal_db->db_connect_id) die("Failed to connect to database...");

$_SESSION['cal_version']=true;




############### Set options and sid ###################
// load options from dB
cal_load_options();
// include the language file (suppress possible errors for security)
@include('library/cal/languages/'.cal_option("language").".php");
// if user is logging in, check the password etc
//if(array_var($_POST,'user')!="") cal_check_user();
// set the permissions for the user
	
		

#######################################################




######################### Set Date info ########################################

// Set Month
if(array_var($_POST,'month')!="") $_SESSION['month'] = $_POST['month'];
elseif(array_var($_GET,'month')!="") $_SESSION['month'] = $_GET['month'];
elseif(array_var($_SESSION,'month')=="") $_SESSION['month'] = date('n');

// Set year
if(array_var($_POST,'year')!="") $_SESSION['year'] = $_POST['year'];
elseif(array_var($_GET,'year')!="") $_SESSION['year'] = $_GET['year'];
elseif(array_var($_SESSION,'year')=="") $_SESSION['year'] = date('Y');

// Set day
if(array_var($_POST,'day')!="") $_SESSION['day'] = $_POST['day'];
elseif(array_var($_GET,'day')!="") $_SESSION['day'] = $_GET['day'];
elseif(array_var($_SESSION,'day')=="") $_SESSION['day'] = date('j');

// the max day can change, so we just adjust for this.
// nothing should change if the day is within the month and year's actual day ranges.
// this is also important because it removes sql injection stuff from posted dates given to the server as a bonus.
$adjust_time = mktime(0,0,0,$_SESSION['month'],$_SESSION['day'],$_SESSION['year']);
$_SESSION['year'] = date("Y",$adjust_time);
$_SESSION['month'] = date("n",$adjust_time);
$_SESSION['day'] = date("d",$adjust_time);

// extra year check. We have to do this since if the year goes way out of wack due to a really strange day number,
// it will not throw mysql off and enter a time of 0 when it should be 9999 for endless repeating events, etc.
// not sure what will happen if a date is before 1000 AD, maybe nothing?  better safe than sorry.
if(array_var($_SESSION,'year')<1000) $_SESSION['year'] = 1000;
elseif(array_var($_SESSION,'year')>9999) $_SESSION['year'] = 9999;

##############################################################################

?>
