<?php
/**
 * Define actions on categories
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
$picture = (isset($_FILES['picture'])) ? $_FILES['picture'] : null;
$resetPicture = (isset($_POST['resetPicture'])) ? true : false;

/**
 * Set the picture of a category
 * @param int $id_category ID of the category to change the picture to
 * @param array $picture Array of the uploaded picture retrieved from POST vars
 * @return bool
 */
function setPicture($id_category , $picture) {
	global $_CONF , $resetPicture;
	$db = new Db();
	$file = 'default_'.$id_category.'.png';
	
	if(!$resetPicture) {
		if($picture['error'] !=  UPLOAD_ERR_OK || !is_uploaded_file($picture['tmp_name'])) {return false;}
		resamplePicture($picture['tmp_name'] , 107 , $_CONF['medias']['actuPictsRoot'].$file , $picture['name']);
	}
	
	$query = 'UPDATE categories SET default_picture = '.(($resetPicture) ? 'NULL' : '"'.$file.'"').' WHERE id_category = "'.$id_category.'"';
	$db->query($query);
	
	$db->close();
	return true;
}

function add () {
	global $name , $picture;
	$db = new Db();
	
	try {
		// Insert the category into database
		$query = 'INSERT INTO categories (name) VALUES("'.$db->real_escape_string($name).'")';
		$db->query($query);
		if($db->affected_rows === 0) {throw new Exception('No row were created.' , 1);}
		// Set the picture
		setPicture($db->insert_id , $picture);
	} catch(Exception $e) {
		trigger_error('Error while creating a new category : '.$e->getMessage() , E_USER_NOTICE);
		return 'Error while creating the new category';
	}
	
	$db->close();
	return 'Category "'.$name.'" successfully created.';
}

function mod () {
	global $name , $picture;
	$db = new Db();
	$query = 'SELECT id_category FROM categories WHERE id_category = "'.$db->real_escape_string($_GET['id']).'"';
	$result = $db->query($query);
	if($result->num_rows === 0) {return 'There is no category with id#'.$_GET['id'];}
	
	// Set the name of the page
	$query = '
		UPDATE categories SET 
			name = "'.$db->real_escape_string($name).'"
		WHERE id_category = "'.$db->real_escape_string($_GET['id']).'"';
	$db->query($query);
	// Set the picture
	setPicture($_GET['id'] , $picture);
	
	$db->close();
	return 'Category "'.$name.'" successfully updated.';
}

function del () {
	$db = new Db();
	$query = 'DELETE FROM categories WHERE id_category = "'.$db->real_escape_string($_GET['id']).'"';
	$db->query($query);
	$db->close();
	return 'Category id#'.$_GET['id'].' successfully deleted.';
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
	Cliquez <a href="categories_list.php" title="Liste">ici</a> si vous n\'ètes pas redirigé automatiquement.
';

header('Refresh: 3;url=categories_list.php');

$page = new Page();
$page->add('content' , $content);
$page->send();
?>
