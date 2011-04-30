<?php
require_once('config.inc.php');

class Db extends mysqli {
	
	/**
	 * Construct a new DB connection
	 * 
	 * Connection parameters are defined in the configuration file
	 * 
	 * @param string $base OPTIONAL - Name of the database you want to connect to
	 * @return mysqli MySQLi object instance
	 */
	public function __construct($base = 'exposerm') {
		global $_CONF;
		parent::__construct($_CONF['db']['host'] , $_CONF['db']['user'] , $_CONF['db']['pass'] , $base , $_CONF['db']['port']);
		$this->query('SET CHARACTER SET "utf8"');
	}
	
	public function query($query , $resultmode = MYSQLI_STORE_RESULT) {
		if(DEBUG >= 2) {trigger_error('[DEBUG2] ('.__METHOD__.') Database query : '.$query , E_USER_NOTICE);}
		if(!$result = parent::query($query , $resultmode)) {throw new Exception('Error while querying database : '.$this->error , 1);}
		return $result;
	}
	
	// public function __destruct() {
		// @$this->close();
	// }
}
?>