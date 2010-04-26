<?php

/**
 * Chinchulin upgrade script will upgrade OpenGoo 1.1+ to OpenGoo 1.2.1
 *
 * @package ScriptUpgrader.scripts
 * @version 1.1
 * @author Ignacio de Soto <ignacio.desoto@opengoo.org>
 */
class ChinchulinUpgradeScript extends ScriptUpgraderScript {

	/**
	 * Array of files and folders that need to be writable
	 *
	 * @var array
	 */
	private $check_is_writable = array(
		'/config/config.php',
		'/config',
		'/public/files',
		'/cache',
		'/tmp',
		'/upload'
	 ); // array

	 /**
	 * Array of extensions taht need to be loaded
	 *
	 * @var array
	 */
	private $check_extensions = array(
		'mysql', 'gd', 'simplexml'
	); // array

	 /**
	 * Construct the ChinchulinUpgradeScript
	 *
	 * @param Output $output
	 * @return ChinchulinUpgradeScript
	 */
	function __construct(Output $output) {
		parent::__construct($output);
		$this->setVersionFrom('1.1');
		$this->setVersionTo('1.2.1');
	} // __construct

	function getCheckIsWritable() {
		return $this->check_is_writable;
	}

	function getCheckExtensions() {
		return $this->check_extensions;
	}
	
	/**
	 * Execute the script
	 *
	 * @param void
	 * @return boolean
	 */
	function execute() {
		// ---------------------------------------------------
		//  Connect to database
		// ---------------------------------------------------

		if($this->database_connection = mysql_connect(DB_HOST, DB_USER, DB_PASS)) {
			if(mysql_select_db(DB_NAME, $this->database_connection)) {
				$this->printMessage('Upgrade script has connected to the database.');
			} else {
				$this->printMessage('Failed to select database ' . DB_NAME);
				return false;
			} // if
		} else {
			$this->printMessage('Failed to connect to database');
			return false;
		} // if

		// ---------------------------------------------------
		//  Check MySQL version
		// ---------------------------------------------------

		$mysql_version = mysql_get_server_info($this->database_connection);
		if($mysql_version && version_compare($mysql_version, '4.1', '>=')) {
			$constants['DB_CHARSET'] = 'utf8';
			@mysql_query("SET NAMES 'utf8'", $this->database_connection);
			tpl_assign('default_collation', $default_collation = 'collate utf8_unicode_ci');
			tpl_assign('default_charset', $default_charset = 'DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
		} else {
			tpl_assign('default_collation', $default_collation = '');
			tpl_assign('default_charset', $default_charset = '');
		} // if

		tpl_assign('table_prefix', TABLE_PREFIX);
		if (defined('DB_ENGINE'))
		tpl_assign('engine', DB_ENGINE);
		else
		tpl_assign('engine', 'InnoDB');

		// ---------------------------------------------------
		//  Check test query
		// ---------------------------------------------------

		$test_table_name = TABLE_PREFIX . 'test_table';
		$test_table_sql = "CREATE TABLE `$test_table_name` (
		`id` int(10) unsigned NOT NULL auto_increment,
		`name` varchar(50) $default_collation NOT NULL default '',
		PRIMARY KEY  (`id`)
		) ENGINE=InnoDB $default_charset;";

		if(@mysql_query($test_table_sql, $this->database_connection)) {
			$this->printMessage('Test query has been executed. Its safe to proceed with database migration.');
			@mysql_query("DROP TABLE `$test_table_name`", $this->database_connection);
		} else {
			$this->printMessage('Failed to executed test query. MySQL said: ' . mysql_error($this->database_connection), true);
			return false;
		} // if

		//return ;

		// ---------------------------------------------------
		//  Execute migration
		// ---------------------------------------------------

		$total_queries = 0;
		$executed_queries = 0;
		$installed_version = installed_version();
		if (version_compare($installed_version, "1.1") <= 0) {
			$upgrade_script = tpl_fetch(get_template_path('db_migration/1_2_chinchulin'));
		} else {
			$upgrade_script = "DELETE FROM `".TABLE_PREFIX."config_options` WHERE `name` = 'time_format_use_24';
			UPDATE `".TABLE_PREFIX."config_options` SET `category_name` = 'modules' WHERE `name` = 'enable_email_module';
			ALTER TABLE `".TABLE_PREFIX."mail_contents` ADD COLUMN `content_file_id` VARCHAR(40) NOT NULL default '';
			";
		}
		@unlink("../../language/de_de/._lang.js");
		

		if($this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
			$this->printMessage("Database schema transformations executed (total queries: $total_queries)");
		} else {
			$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
			return false;
		} // if

		$this->printMessage('OpenGoo has been upgraded. You are now running OpenGoo '.$this->getVersionTo().' Enjoy!');
	} // execute
} // ChinchulinUpgradeScript

?>