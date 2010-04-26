<?php
/*  Gelsheet Project, version 0.0.1 (Pre-alpha)
 *  Copyright (c) 2008 - Ignacio Vazquez, Fernando Rodriguez, Juan Pedro del Campo
 *
 *  Ignacio "Pepe" Vazquez <elpepe22@users.sourceforge.net>
 *  Fernando "Palillo" Rodriguez <fernandor@users.sourceforge.net>
 *  Juan Pedro "Perico" del Campo <pericodc@users.sourceforge.net>
 *
 *  Gelsheet is free distributable under the terms of an GPL license.
 *  For details see: http://www.gnu.org/copyleft/gpl.html
 *
 */
//include_once ($cnf['site']['path']."/". $cnf['og']['path']."application/functions.php" );
//include_once ($cnf['site']['path']."/". $cnf['og']['path']."init.php" );
//include_once ($cnf['site']['path']."/". $cnf['og']['path']."environment/functions/general.php" )


class OpengooController {
	public function __construct(){
		echo "ok";
	}
	
	public function saveBook($fileId = '', $booName = '') {
		echo "entra Save BOok OG ";
		$userId = 1;
		$filename = $bookName;
		$is_private= 0;
		$is_important = 0; 
		$is_locked= 0;
		$is_visible= 1;
		$expiration_time = "0";
		$comments_enabled = 1;
		$anonymous_comments_enabled = 1;
		$created_on = time();
		$updated_on = time();
		$checked_out_on = time();
		$was_auto_checked_out = 0;
		
		$workspaceId =1;
		$conn = new Connection("localhost","root","lalala","OgelIntegration");		
		$sql = "insert into project_files(created_by_id,updated_by_id,filename, is_private,is_important,is_locked,is_visible,expiration_time,comments_enabled,anonymous_comments_enabled,created_on,updated_on,checked_out_on,was_auto_checked_out) 
					VALUES ($userId,$userId,'$filename', $is_private,$is_important,$is_locked,$is_visible,now(),$comments_enabled,$anonymous_comments_enabled,now(),now(),now(),$was_auto_checked_out)"; 
		
		
		$error = mysql_query($sql);
		if(!$error)
			echo mysql_error();
		
		$lastId = mysql_insert_id();	
		$sql = "insert into workspace_objects(workspace_id,object_manager, object_id,created_by_id,created_on) 
					VALUES ($workspaceId,'ProjectFiles',$lastId,$userId,now())";
		
		$error = mysql_query($sql);
		if(!$error)
			echo mysql_error();
		
	}
}
?>