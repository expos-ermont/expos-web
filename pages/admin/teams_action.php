<?php
/**
 * Define actions on teams
 *
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

require_once('../../lib/config.inc.php');
require_once($_CONF['libRoot'].'Page.class.php');
require_once($_CONF['libRoot'].'Db.class.php');
require_once($_CONF['libRoot'].'Control.class.php');

Control::accessToPage(__FILE__);

$name = (isset($_POST['name'])) ? $_POST['name'] : null;
$id_championship = (isset($_POST['id_championship']) && !empty($_POST['id_championship'])) ? $_POST['id_championship'] : null;
$picture = (isset($_FILES['picture'])) ? $_FILES['picture'] : null;
$resetPicture = (isset($_POST['resetPicture'])) ? true : false;
$ids_users = (isset($_POST['ids_users'])) ? $_POST['ids_users'] : null;

/**
 * Set the picture of a team
 * @param int $id_team ID of the team to change the picture to
 * @param array $picture Array of the uploaded picture retrieved from POST vars
 * @return bool
 */
function setPicture($id_team , $picture) {
	global $_CONF , $resetPicture;
	$db = new Db();
	$file = $id_team.'.png';
	
	if(!$resetPicture) {
		if($picture['error'] !=  UPLOAD_ERR_OK || !is_uploaded_file($picture['tmp_name'])) {return false;}
		resamplePicture($picture['tmp_name'] , 300 , $_CONF['medias']['teamPictsRoot'].$file , $picture['name']);
	}
	
	$query = 'UPDATE equipes SET picture = '.(($resetPicture) ? 'NULL' : '"'.$file.'"').' WHERE id_equipe = "'.$db->real_escape_string($id_team).'"';
	$db->query($query);
	
	$db->close();
	return true;
}

/**
 * Set the new players of a team
 * @param int $id_team ID of the concerned team
 * @return bool
 */
function setPlayers($id_team) {
	global $ids_users;
	$db = new Db();
	
	// Reset all the existing players
	$query = 'UPDATE users SET id_equipe = NULL WHERE id_equipe = "'.$db->real_escape_string($id_team).'"';
	$db->query($query);
	// Add the new players
	if(!is_null($ids_users)) {
		array_map(array($db , 'real_escape_string') , $ids_users);
		$query = 'UPDATE users SET id_equipe = "'.$db->real_escape_string($id_team).'" WHERE id_user IN ("'.join('","' , $ids_users).'")';
		$db->query($query);
	}
	
	$db->close();
	return true;
}

function add () {
	global $name , $id_championship , $picture;
	$db = new Db();
	
	try {
		// Insert the team into database
		$query = '
			INSERT INTO equipes (nom , id_championship) 
			VALUES("'.$db->real_escape_string($name).'" , '.((is_null($id_championship)) ? 'NULL' : '"'.$db->real_escape_string($id_championship).'"').')
		';
		$db->query($query);
		if($db->affected_rows === 0) {throw new Exception('No row were created.' , 1);}
		// Add the team players
		setPlayers($db->insert_id);
		// Set the picture
		setPicture($db->insert_id , $picture);
	} catch(Exception $e) {
		trigger_error('Error while creating a new team : '.$e->getMessage() , E_USER_NOTICE);
		return 'Error while creating the new team';
	}
	
	$db->close();
	return 'Team "'.$name.'" successfully created.';
}

function mod () {
	global $name , $id_championship , $picture;
	$db = new Db();
	$query = 'SELECT id_equipe FROM equipes WHERE id_equipe = "'.$db->real_escape_string($_GET['id']).'"';
	$result = $db->query($query);
	if($result->num_rows === 0) {return 'There is no team with id#'.$_GET['id'];}
	
	try {
		// Set vars of the team
		$query = '
			UPDATE equipes SET 
				nom = "'.$db->real_escape_string($name).'",
				id_championship = '.((is_null($id_championship)) ? 'NULL' : '"'.$db->real_escape_string($id_championship).'"').'
			WHERE id_equipe = "'.$db->real_escape_string($_GET['id']).'"
		';
		$db->query($query);
		// Add the team players
		setPlayers($_GET['id']);
		// Set the picture
		setPicture($_GET['id'] , $picture);
	} catch(Exception $e) {
		trigger_error('Error while modifying the team : '.$e->getMessage() , E_USER_NOTICE);
		return 'Error while modifying the team';
	}
	
	$db->close();
	return 'Team "'.$name.'" successfully updated.';
}

function del () {
	$db = new Db();
	$query = 'DELETE FROM equipes WHERE id_equipe = "'.$db->real_escape_string($_GET['id']).'"';
	$db->query($query);
	$db->close();
	return 'Team id#'.$_GET['id'].' successfully deleted.';
}

switch($_GET['action']) {
	
	case 'add': 
		$message = add();
		break;
		
	case 'mod': 
		$message = mod();
		break;
		
	case 'del': 
		$message = del();
		break;
	
	default:
		$message = 'Unknown action "'.$_GET['action'].'"';
		break;
}

$content = '
	'.$message.'<br /><br />
	Cliquez <a href="teams_list.php" title="Liste">ici</a> si vous n\'ètes pas redirigé automatiquement.
';

header('Refresh: 3;url=teams_list.php');

$page = new Page();
$page->add('content' , $content);
$page->send();
?>
