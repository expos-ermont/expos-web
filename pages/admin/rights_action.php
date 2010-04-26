<?php
/**
 * Edit rights
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
$id_pages = (isset($_POST['id_pages'])) ? $_POST['id_pages'] : array();
$id_users = (isset($_POST['id_users'])) ? $_POST['id_users'] : array();

function add () {
	global $name , $id_pages , $id_users;
	$db = new Db();
	
	// Add the new right
	$query = 'INSERT INTO rights (name) VALUES("'.$db->real_escape_string($name).'")';
	$db->query($query);
	if($db->affected_rows === 0) {return 'Error while creating the new right';}
	// Set the granted pages
	Control::addRightToPages($db->insert_id , $id_pages);
	// Set the granted users
	Control::addRightForUsers($db->insert_id , $id_users);
	
	$db->close();
	return 'Right "'.$name.'" successfully created.';
}

function mod () {
	global $name , $id_pages , $id_users;
	$db = new Db();
	$query = 'SELECT name FROM rights WHERE id_right = "'.$db->real_escape_string($_GET['id']).'"';
	$result = $db->query($query);
	if($result->num_rows === 0) {return 'There is no right with id#'.$_GET['id'];}
	
	// Set the name of the right
	$query = 'UPDATE rights SET name = "'.$db->real_escape_string($name).'" WHERE id_right = "'.$db->real_escape_string($_GET['id']).'"';
	$db->query($query);
	// Set the granted pages
	Control::removeRightToPages($_GET['id']);
	Control::addRightToPages($_GET['id'] , $id_pages);
	// Set the granted users
	Control::removeRightForUsers($_GET['id']);
	Control::addRightForUsers($_GET['id'] , $id_users);
	
	$db->close();
	return 'Right "'.$name.'" successfully updated.';
}

function del () {
	$db = new Db();
	$query = 'DELETE FROM rights WHERE id_right = "'.$db->real_escape_string($_GET['id']).'"';
	$db->query($query);
	$db->close();
	return 'Right id#'.$_GET['id'].' successfully deleted.';
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
	Cliquez <a href="rights_list.php" title="Liste">ici</a> si vous n\'êtes pas redirigé automatiquement.
';

header('Refresh: 3;url=rights_list.php');

$page = new Page();
$page->add('content' , $content);
$page->send();
?>
