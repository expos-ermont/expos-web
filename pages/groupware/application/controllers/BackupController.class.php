<?php

if (!defined('MYSQLDUMP_COMMAND')) define('MYSQLDUMP_COMMAND', 'mysqldump'); // For Windows: define('MYSQLDUMP_COMMAND', '"c:\Program Files\MySQL\MySQL Server 5.0\bin\mysqldump"');
if (!defined('DB_BACKUP_FILENAME')) define('DB_BACKUP_FILENAME', 'db.sql');
if (!defined('BACKUP_FOLDER')) define('BACKUP_FOLDER', "tmp/backup");
if (!defined('BACKUP_TIME_LIMIT')) define('BACKUP_TIME_LIMIT', "300");
if (!defined('BACKUP_FILENAME')) define('BACKUP_FILENAME', "opengoo_backup.zip");

/**
 * Backup controller
 *
 * @version 1.0
 * @author Marcos Saiz <marcos.saiz@opengoo.org>
 */
class  BackupController extends ApplicationController {

	/**
	 * Construct the BackupController
	 *
	 * @access public
	 * @param void
	 * @return BackupController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');

		// Access permissios
		if(!(logged_user()->isAccountOwner())){
			flash_error(lang('no access permissions'));
			ajx_current("empty");
		} // if
	} // __construct

	
	/**
	 * Shows the backup menu
	 *
	 */
	function index(){
		// Access permissios
		if(!(logged_user()->isAccountOwner())){
			flash_error(lang('no access permissions'));
			ajx_current("empty");
		} // if
		$filename =  BACKUP_FILENAME;
		$folder = BACKUP_FOLDER;
		if (is_file($folder . '/' .$filename )) {
			$last_backup = @filectime($folder . '/' .$filename );
			$has_backup = true;
		} else {
			$has_backup = false;
		}
		if ($last_backup) {
			$date = new DateTimeValue($last_backup);
			$date = $date->format("Y/m/d H:i:s");
		}
		tpl_assign('has_backup', $has_backup);
		tpl_assign('last_backup', $date);
	}
	
	/**
	 * Download bakcup file
	 *
	 */
	function download(){
		// Access permissios
		if(!(logged_user()->isAccountOwner())){
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return ;
		} // if
		if(!(can_manage_configuration(logged_user()))){
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return ;
		}
		$filename = BACKUP_FOLDER . '/' . BACKUP_FILENAME;
		if (!is_file($filename)) {
			flash_error(lang('file dnx'));
			ajx_current("empty");
			return ;
		}
		
		$size = filesize($filename);
		session_commit();
		download_file($filename, 'application/zip', BACKUP_FILENAME , $size, true);
		die();
	}
	
	/**
	 * Delete backup located in tmp/backup
	 *
	 */
	function delete(){
		// Access permissios
		if(!(logged_user()->isAccountOwner())){
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return ;
		} // if
		$filename = BACKUP_FOLDER .'/'. BACKUP_FILENAME;
		if(is_file($filename)) {
			$ret = unlink($filename);
			if(!$ret){
				$ret = chown($filename,'nobody1234') && unlink($filename);
			}
			
		}
		if(!$ret){
			flash_error(lang('error delete backup'));
		}
		else {
			flash_success(lang('success delete backup'));
		}
		ajx_current("reload");
	}
	
	/**
	 * Launch backup process
	 *
	 */
	function launch() {
		ajx_current("empty");
		// Access permissios
		if (!(logged_user()->isAccountOwner())) {
			flash_error(lang('no access permissions'));
			return ;
		} // if
		try {
			do_backup();
			flash_success(lang('success db backup'));
		} catch (Exception $ex) {
			flash_error(lang('error db backup', $ex->getMessage()));
			return;
		}
		ajx_current("reload");
	}
} // BackupController

function do_backup() {
	set_time_limit(BACKUP_TIME_LIMIT);
	$filename = BACKUP_FOLDER .'/'. BACKUP_FILENAME;
	$folder = BACKUP_FOLDER;
	if(!dir($folder)){
		$ret = mkdir($folder);
		if(!$ret){
			throw new Exception(lang('error create backup folder'));
		}		
	}
	//start backup
	$db_host = DB_HOST;
	$db_user = DB_USER;
	$db_pass = DB_PASS;
	$db_name = DB_NAME;
	$db_backup = BACKUP_FOLDER .'/'. DB_BACKUP_FILENAME;
	$mysqldump_cmd = MYSQLDUMP_COMMAND;
	exec("$mysqldump_cmd --host=$db_host --user=$db_user --password=$db_pass $db_name > $db_backup", $ret, $code);
	if ($code != 0) {
		throw new Exception(lang("return code", $code) . ". " . implode("\n",  $ret));
	}
	if (is_file($db_backup)) {
		if(file_exists($filename)){
			unlink($filename);
		}
		do_backup_zip();
		unlink($db_backup);
	} else {
		throw new Exception(lang('backup command failed'));
	}
}

function do_backup_zip() {
	$filename = BACKUP_FOLDER .'/'. BACKUP_FILENAME;
	$files = array();
	do_backup_parse_dir(".", $files);
	if (is_file($filename)) unlink($filename);
	if (in_array('ZipArchive', get_declared_classes())) {
		$backup = new ZipArchive();
		$backup->open($filename, ZIPARCHIVE::OVERWRITE);
		$count = 0;
		foreach ($files as $file) {
			if (str_starts_with($file, "./tmp")) continue; // don't backup tmp folder
			if (str_starts_with($file, "./config")) continue; // don't backup config folder
			$backup->addFile($file, substr($file, 2));
			$count++;
			if ($count == 200) {
				// the ZipArchive class allows upto 200 file descriptors,
				// so we close the zip to write files and reopen it to continue
				$backup->close();
				$backup = new ZipArchive();
				$backup->open($filename);
				$count = 0;
			}
		}
		$backup->addFile(BACKUP_FOLDER .'/'. DB_BACKUP_FILENAME, "db.sql");
		$backup->close();
	} else {
		$backup = new zip_file($filename);
		$backup->set_options(array('inmemory' => 0, 'recurse' => 1, 'storepaths' => 1));
		$backup->add_files(array("*")); 
		$backup->create_archive();
	}
}

function do_backup_parse_dir($dirname, &$files) {
	$dir = @opendir($dirname);
	while ($file = @readdir($dir)) {
		$fullname = "$dirname/$file";
		if ($file == "." || $file == "..") {
			continue;
		} else if (@is_dir($fullname)) {
			do_backup_parse_dir($fullname, $files);
		} else if (@is_file($fullname)) {
			$files[] = $fullname;
		}
	}
	@closedir($dir);
}


?>