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
$id_rights = (isset($_POST['id_rights'])) ? $_POST['id_rights'] : array();

function add () {
	global $name , $id_rights;
	$db = new Db();
	
	// Insert the new page
	$query = 'INSERT INTO pages (name) VALUES("'.$db->real_escape_string($name).'")';
	$db->query($query);
	if($db->affected_rows === 0) {return 'Error while creating the new page';}
	// Set the granted rights
	Control::addRightsToPage($db->insert_id , $id_rights);
	
	$db->close();
	return 'Page "'.$name.'" successfully created.';
}

function mod () {
	global $name , $id_rights;
	$db = new Db();
	$query = 'SELECT name FROM pages WHERE id_page = "'.$db->real_escape_string($_GET['id']).'"';
	$result = $db->query($query);
	if($result->num_rows === 0) {return 'There is no page with id#'.$_GET['id'];}
	
	// Set the name of the page
	$query = 'UPDATE pages SET name = "'.$db->real_escape_string($name).'" WHERE id_page = "'.$db->real_escape_string($_GET['id']).'"';
	$db->query($query);
	// Set the granted rights
	Control::removeRightsToPage($_GET['id']);
	Control::addRightsToPage($_GET['id'] , $id_rights);
	
	$db->close();
	return 'Page "'.$name.'" successfully updated.';
}

function del () {
	$db = new Db();
	$query = 'DELETE FROM pages WHERE id_page = "'.$db->real_escape_string($_GET['id']).'"';
	$db->query($query);
	$db->close();
	return 'Page id#'.$_GET['id'].' successfully deleted.';
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
	Cliquez <a href="pages_list.php" title="Liste">ici</a> si vous n\'êtes pas redirigé automatiquement.
';

header('Refresh: 3;url=pages_list.php');

$page = new Page();
$page->add('content' , $content);
$page->send();
?>
