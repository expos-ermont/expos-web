<?php
/**
 * Define a User object
 *
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

require_once($_CONF['libRoot'].'Db.class.php');
require_once($_CONF['libRoot'].'Password.class.php');
 
class User {
	private $id = null;
	private $login = null;
	private $firstname = null;
	private $lastname = null;
	private $birthdate = null;
	private $street = null;
	private $postalcode = null;
	private $city = null;
	private $mail = null;
	private $phone = null;
	private $public_terms = null;
	private $int_terms = null;
	
	public function __construct($id_user) {
		$this->id = $id_user;
		$this->_loadFromDb();
	}
	
	public function __get($name) {
		/*
		if(!is_null($this->$name)) {return $this->$name;}
		
		if(DEBUG >= 2) {trigger_error('[DEBUG2] ('.__METHOD__.') Retrieve '.$name.' for '.$login , E_USER_NOTICE);}
		
		$db = new Db();
		$query = 'SELECT '.$name.' FROM users WHERE id_user = "'.$this->id.'"';
		$result = $db->query($query);
		$data = $result->fetch_array();
		$this->$name = $data[$name];
		*/
		return $this->$name;
	}
	
	/**
	 * Reload the variables of a user from DB
	 * @return bool
	 */
	public function reload() {
		$this->_loadFromDb();
		return true;
	}
	
	/**
	 * Get an array of multiple data of current user
	 * @param array $keys Columns names
	 * @return array
	 */
	public function getData($keys) {
		$return = array();
		foreach($keys as $key) {
			if(!in_array($key , self::getValidVariables())) {throw new Exception($key.' is not a valid column of users table');}
			$return[] = $this->$key;
		}
		return $return;
	}

	/**
	 * Try to authenticate a user with his login and password couple
	 * @param string $login
	 * @param string $password
	 * @return User User object if success, Exception if not
	 */
	public static function authenticate($login , $password) {
		if(DEBUG >= 2) {trigger_error('[DEBUG2] ('.__METHOD__.') Try to authenticate '.$login , E_USER_NOTICE);}
	
		$db = new Db();
		$hashPassword = Password::hash($password);
		
		$query = '
			SELECT
				id_user
			FROM
				users
			WHERE
				login = "'.$db->real_escape_string($login).'"
				AND password = "'.$hashPassword.'"
		';
		$result = $db->query($query);
		if(!$data = $result->fetch_array()) {throw new Exception('Invalid credentials for '.$login , 2);}
		
		$db->close();
		return new User($data['id_user']);
	}
	
	/**
	 * Change the password of a given user id
	 * 
	 * @param int $id_user ID of the user to change the password to
	 * @param string $password New password of the user
	 * @return bool Status of change
	 */
	public static function changePassword($id_user , $password) {
		$db = new Db();
		$hashPassword = Password::hash($password);
		
		$query = 'UPDATE users SET password = "'.$hashPassword.'" WHERE id_user = "'.$id_user.'"';
		$db->query($query);
		
		$db->close();
		return true;
	}
	
	/**
	 * Check if some various data are belonging to the same user
	 * Used to verify the identity of a certain user without asking login/password
	 * 
	 * @param array $columns The fields to check (MUST be defined in users table)
	 * @param array $values The corresponding values
	 * @return mixed User ID in case of success, false if not, in case of error this will return an exception
	 */
	public static function checkData(array $columns , array $values) {
		if(DEBUG >= 2) {trigger_error('[DEBUG2] ('.__METHOD__.') Check user data' , E_USER_NOTICE);}
		
		if(count($columns) != count($values)) {throw new Exception('The number of elements of columns and values tables are not the same' , 1);}
		
		$db = new Db();
		
		// Escape the characters for MySQL connection
		array_map(array($db , 'real_escape_string') , $columns);
		array_map(array($db , 'real_escape_string') , $values);
		
		$query = '
			SELECT
				id_user
			FROM
				users
			WHERE ('.implode(',' , $columns).') = ("'.implode('","' , $values).'")
		';
		$result = $db->query($query);
		if(!$data = $result->fetch_array()) {return false;}
		
		$db->close();
		
		return $data['id_user'];
	}
	
	/**
	 * Return the columns available in DB for the users table
	 * @return array
	 */
	public static function getValidVariables() {
		$return = array();
		
		$db = new Db();
		$query = 'DESCRIBE users';
		$result = $db->query($query);
		while($data = $result->fetch_array()) {$return[] = $data['Field'];}
		$db->close();
		
		return $return;
	}
	
	/**
	 * Return an array containing users values
	 * @param array $columns Array of columns we want to retrieve
	 * @return array
	 */
	public static function getUsersTable(array $columns) {
		$return = array();
		
		$db = new Db();
		$query = '
			SELECT '.implode(',' , $columns).'
			FROM users
		';
		$result = $db->query($query);
		while($data = $result->fetch_array()) {$return[] = $data;}
		$db->close();
		
		return $return;
	}
	
	/**
	 * Format a value regarding the column it reffers to
	 * @param string $column Name of column involved, MUST be a valid one, check with 'getValidVariables' from this class
	 * @param string $value Value to format
	 * @return string Formatted value
	 */
	public static function formatValue($column , $value) {
		if(!in_array($column , self::getValidVariables())) {throw new Exception($column.' is not a valid column of users table');}
		switch($column) {
			case 'nom':
			case 'prenom':
			case 'street':
			case 'city':
				$value = ucwords(strtolower($value));
				break;
			case 'phone':
				$value = str_replace('.' , '' , $value);
				break;
			case 'birthdate':
				$value = preg_replace('/(\d{2})\/(\d{2})\/(\d{4})/' , '\3-\2-\1' , $value);
				//$value = @date('Y-m-d' , @strtotime($value));
				break;
			default:
				break;
		}
		return $value;
	}
	
	/**
	 * Add a new user in the table
	 * @param array $columns Columns of the users table
	 * @param array $values Corresponding values
	 * @return bool
	 */
	public static function add(array $columns , array $values) {
		if(DEBUG >= 2) {trigger_error('[DEBUG2] ('.__METHOD__.') Add user' , E_USER_NOTICE);}
		
		foreach($columns as &$column) {if(!in_array($column , self::getValidVariables() , true)) {throw new Exception($column.' is not a valid column of users table');}}
		if(count($columns) != count($values)) {throw new Exception('The number of elements of columns and values tables are not the same' , 1);}
		if(count($columns) < 1) {throw new Exception('Invalid number of columns (MUST be at least 1)');}
		
		$db = new Db();
		
		// Escape the characters for MySQL connection
		array_map(array($db , 'real_escape_string') , $columns);
		array_map(array($db , 'real_escape_string') , $values);
		
		$query = 'INSERT INTO users ('.implode(',' , $columns).',createdate) VALUES ("'.implode('","' , $values).'",NOW())';
		$db->query($query);
		
		$db->close();
		
		return true;
	}
	
	/**
	 * Update user data
	 * @param int $id_user ID of user to update
	 * @param array $columns The fields to check (MUST be defined in users table)
	 * @param array $values The corresponding values
	 * @return bool
	 */
	public static function update($id_user , array $columns , array $values) {
		if(DEBUG >= 2) {trigger_error('[DEBUG2] ('.__METHOD__.') Update user data' , E_USER_NOTICE);}
		
		foreach($columns as &$column) {if(!in_array($column , self::getValidVariables() , true)) {throw new Exception($column.' is not a valid column of users table');}}
		if(count($columns) != count($values)) {throw new Exception('The number of elements of columns and values tables are not the same' , 1);}
		if(count($columns) < 1) {throw new Exception('Invalid number of columns (MUST be at least 1)');}
		
		$db = new Db();
		
		// Escape the characters for MySQL connection
		$id_user = $db->real_escape_string($id_user);
		array_map(array($db , 'real_escape_string') , $columns);
		array_map(array($db , 'real_escape_string') , $values);
		
		$update_clause = '';
		for($i = 0; $i < count($columns); $i++) {
			$update_clause .= $columns[$i].'="'.$values[$i].'",'; 
		}
		$update_clause = rtrim($update_clause , ',');
		
		$query = '
			UPDATE users 
			SET '.$update_clause.',modifydate=NOW()
			WHERE id_user = "'.$id_user.'"
		';
		$result = $db->query($query);
		
		$db->close();
		
		return true;
	}
	
	private function _loadFromDb() {
		if(DEBUG >= 2) {trigger_error('[DEBUG2] ('.__METHOD__.') Load from DB user #'.$this->id , E_USER_NOTICE);}
		
		$db = new Db();
		$query = '
			SELECT
				nom,
				prenom,
				birthdate,
				street,
				postalcode,
				city,
				mail,
				phone,
				login,
				public_terms,
				int_terms
			FROM
				users
			WHERE
				id_user = "'.$this->id.'"
		';
		$result = $db->query($query);
		if(!$data = $result->fetch_array()) {throw new Exception('No informations for id '.$this->id.' in users table' , 3);}
		
		$this->lastname = $data['nom'];
		$this->firstname = $data['prenom'];
		$this->birthdate = $data['birthdate'];
		$this->street = $data['street'];
		$this->postalcode = $data['postalcode'];
		$this->city = $data['city'];
		$this->mail = $data['mail'];
		$this->phone = $data['phone'];
		$this->login = $data['login'];
		$this->public_terms = $data['public_terms'];
		$this->int_terms = $data['int_terms'];
		
		$db->close();
	}
}
?>