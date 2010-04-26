<?php
/**
 * Global configuration file
 *
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */
 
define('EOL' , "<br />");
define('DEBUG' , 1); // Can be 0:off , 1:first-level messages , 2:verbose messages
define('GRANT_ALL' , 0); // Can be 0 or 1, shutdown all the access controls

if(isset($_SERVER['WINDIR'])) {
	// Windows mode
	define('DIR_SEP' , '\\');
} else {
	// UNIX mode
	define('DIR_SEP' , '/');
}

$_CONF['title']       = 'Expos Ermont Baseball/Softball club';
$_CONF['keywords']    = 'baseball,softball,animations sportives,sport,balle,batte,american sport,sport americain,expos,ermont,sport ermont,baseball ermont,ermont baseball,baseball france,baseball val d\'oise,baseball ile de france,baseball 95';
$_CONF['description'] = 'Le site officiel du club de baseball et softball des Expos d\'Ermont (Val d\'Oise), retouvez les équipes, les resultats, les actualités et les informations du club';
$_CONF['wwwRoot']     = 'http://localhost/~florent/exposerm/';
$_CONF['root']        = preg_replace('/[^\\'.DIR_SEP.']+$/' , '' , dirname(__FILE__));
$_CONF['libRoot']     = $_CONF['root'].'lib'.DIR_SEP;

$_CONF['db']['user'] = 'exposerm';
$_CONF['db']['pass'] = 'vFRQQsyi';
$_CONF['db']['host'] = 'localhost';
$_CONF['db']['port'] = '3306';

$_CONF['medias']['pictsRoot'] = $_CONF['root'].'picts'.DIR_SEP.'photos'.DIR_SEP;
$_CONF['medias']['actuPictsRoot'] = $_CONF['root'].'picts'.DIR_SEP.'actus'.DIR_SEP;
$_CONF['medias']['teamPictsRoot'] = $_CONF['root'].'picts'.DIR_SEP.'teams'.DIR_SEP;
$_CONF['medias']['wwwActuPictsRoot'] = $_CONF['wwwRoot'].'picts'.DIR_SEP.'actus'.DIR_SEP;
$_CONF['medias']['wwwLienPictsRoot'] = $_CONF['wwwRoot'].'picts'.DIR_SEP.'liens'.DIR_SEP;
$_CONF['medias']['wwwTeamPictsRoot'] = $_CONF['wwwRoot'].'picts'.DIR_SEP.'teams'.DIR_SEP;

# Inclusion of some standard files
require_once($_CONF['libRoot'].'functions.inc.php');
require_once($_CONF['libRoot'].'User.class.php');

session_start();

//set_exception_handler('myExceptionHandler');
set_include_path(get_include_path().':'.$_CONF['libRoot']);

if(DEBUG >= 2) {trigger_error('[DEBUG2] Session var dump : '.var_export($_SESSION , true) , E_USER_NOTICE);}
if(DEBUG >= 2 && !empty($_POST)) {trigger_error('[DEBUG2] POST var dump : '.var_export($_POST , true) , E_USER_NOTICE);}
if(DEBUG >= 2 && !empty($_GET)) {trigger_error('[DEBUG2] GET var dump : '.var_export($_GET , true) , E_USER_NOTICE);}
?>
