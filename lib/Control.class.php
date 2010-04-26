<?php
/**
 * Give a set of functions to manage grants
 * 
 * @filesource
 * @author Florent Captier <florent@captier.org>
 */
 
require_once('config.inc.php');
require_once($_CONF['libRoot'].'Db.class.php');

class Control {

	/**************************************************************************
	 * Public functions
	 *************************************************************************/
	
	/**
	 * Check if a user is authorized or not to access a page
	 * @param string $file Filename to check (based on the system path)
	 * @param bool $redirect OPTIONAL - Redirect the user or not in case of denial
	 * @param User $user OPTIONAL - User object to verify the grants
	 * @return bool
	 */
	public static function accessToPage($file , $redirect = true , $user = null) {
		if(DEBUG >= 2) {trigger_error('[DEBUG2] ('.__METHOD__.') Access to '.$file , E_USER_NOTICE);}
		if(GRANT_ALL == 1) {return true;}
		global $_CONF;
		if(is_null($user)) {
			if(isset($_SESSION['user'])) {$user = $_SESSION['user'];}
			elseif(!$redirect) {return false;}
			else {self::denyAccess('Anonymous access were done on '.$file);}
		}
		$file = str_replace($_CONF['root'] , '' , $file);
		$file = str_replace('\\' , '/' , $file);
		$db = new Db();
		
		$query = 'SELECT * FROM v_users_pages WHERE id_user = "'.$user->id.'" AND page_name = "'.$file.'"';
		$result = $db->query($query);
		
		if($result->num_rows === 0) {
			if($redirect) {
				self::denyAccess($user->login.' (#'.$user->id.') is not allowed to access to file '.$file);
			} else {
				return false;
			}
		} else {
			return true;
		}
	}
	
	public static function addRightToPages($id_right , $id_page) {
		return self::addRightRelations($id_right , 'page' , $id_page);
	}
	
	public static function removeRightToPages($id_right) {
		return self::flushRightRelations($id_right , 'page');
	}
	
	public static function addRightForUsers($id_right , $id_user) {
		return self::addRightRelations($id_right , 'user' , $id_user);
	}
	
	public static function removeRightForUsers($id_right) {
		return self::flushRightRelations($id_right , 'user');
	}
	
	public static function addRightsToPage($id_page , $id_right) {
		return self::addRelationRights($id_page , 'page' , $id_right);
	}
	
	public static function removeRightsToPage($id_page) {
		return self::flushRelationRights($id_page , 'page');
	}
	
	public static function addRightsForUser($id_user , $id_right) {
		return self::addRelationRights($id_user , 'user' , $id_right);
	}
	
	public static function removeRightsForUser($id_user) {
		return self::flushRelationRights($id_user , 'user');
	}
	
	/**************************************************************************
	 * Private functions
	 *************************************************************************/
	
	private static function addRightRelations($id_right , $type , $id_rel) {
		$db = new Db();
		$values = '';
		
		if(!is_array($id_rel)) {$id_rel = array((int) $id_rel);}
		if(empty($id_rel)) {return false;}
		
		$values = '('.$db->real_escape_string($id_right).','.implode('),('.$db->real_escape_string($id_right).',' , $id_rel).')';
		
		$query = 'INSERT INTO '.$type.'s_rights (id_right , id_'.$type.') VALUES '.$values;
		$db->query($query);
		
		$db->close();
		return true;
	}
	
	private static function addRelationRights($id_rel , $type , $id_right) {
		$db = new Db();
		$values = '';
		
		if(!is_array($id_right)) {$id_right = array((int) $id_right);}
		if(empty($id_right)) {return false;}
		
		$values = '('.$db->real_escape_string($id_rel).','.implode('),('.$db->real_escape_string($id_rel).',' , $id_right).')';
		
		$query = 'INSERT INTO '.$type.'s_rights (id_'.$type.' , id_right) VALUES '.$values;
		$db->query($query);
		
		$db->close();
		return true;
	}
	
	private static function flushRightRelations($id_right , $type) {
		$db = new Db();
		$query = 'DELETE FROM '.$type.'s_rights WHERE id_right = "'.$db->real_escape_string($id_right).'"';
		$db->query($query);
		$db->close();
		return true;
	}
	
	private static function flushRelationRights($id_rel , $type) {
		$db = new Db();
		$query = 'DELETE FROM '.$type.'s_rights WHERE id_'.$db->real_escape_string($type).' = "'.$db->real_escape_string($id_rel).'"';
		$db->query($query);
		$db->close();
		return true;
	}
	
	private static function denyAccess($message) {
		global $_CONF;
		header('HTTP/1.1 403 Forbidden');
		header('Location: '.$_CONF['wwwRoot'].'httpError.php?code=403');
		trigger_error($message , E_USER_WARNING);
		exit();
	}
}
?>