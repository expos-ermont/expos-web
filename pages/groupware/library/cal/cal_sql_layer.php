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

//if ( !defined('CAL_SECURITY_BIT') ) die("Hacking attempt");

if( !defined('CAL_DB_LOADED')) {
	define('CAL_DB_LOADED', 1);
	// the php file extention
	$cal_phpEx = 'php';
	// the databases path holds the sql_layer for the DB in question.  I use phpBB's layer.
	$cal_database_path = "databases";
	// the "queries" path holds the functions for all the actual query syntax.
	// right now only mysql exists, but you could theoretically write the functions for any DB
	$cal_query_path = "library/cal/queries";
	// pick DB and include the files
	/*switch(CAL_SQL_TYPE){
		case 'mysql':*/
			require($cal_database_path . '/mysql.'.$cal_phpEx);
			require($cal_query_path.'/mysql.'.$cal_phpEx);
		/*	break;
		case 'mysql4':
			require($cal_database_path . '/mysql4.'.$cal_phpEx);
			require($cal_query_path.'/mysql4.'.$cal_phpEx);
			break;
		case 'postgres7':
			require($cal_database_path . '/postgres7.'.$cal_phpEx);
			require($cal_query_path.'/postgres7.'.$cal_phpEx);
			break;
		case 'mssql':
			require($cal_database_path . '/mssql.'.$cal_phpEx);
			require($cal_query_path.'/mssql.'.$cal_phpEx);
			break;
		case 'oracle':
			require($cal_database_path . '/oracle.'.$cal_phpEx);
			require($cal_query_path.'/oracle.'.$cal_phpEx);
			break;
		case 'msaccess':
			require($cal_database_path . '/msaccess.'.$cal_phpEx);
			require($cal_query_path.'/msaccess.'.$cal_phpEx);
			break;
		case 'mssql-odbc':
			require($cal_database_path . '/mssql-odbc.'.$cal_phpEx);
			require($cal_query_path.'/mssql-odbc.'.$cal_phpEx);
			break;
	}*/
}

?>
