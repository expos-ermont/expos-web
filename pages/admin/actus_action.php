<?php
/**
 * Define actions on news
 *
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

require_once('../../lib/config.inc.php');
require_once($_CONF['libRoot'].'Page.class.php');
require_once($_CONF['libRoot'].'Db.class.php');
require_once($_CONF['libRoot'].'Control.class.php');

Control::accessToPage(__FILE__);

$title = (isset($_POST['title'])) ? $_POST['title'] : null;
$id_category = (isset($_POST['id_category'])) ? $_POST['id_category'] : 0;
$picture = (isset($_FILES['picture'])) ? $_FILES['picture'] : null;
$content = (isset($_POST['content'])) ? $_POST['content'] : null;
$resetPicture = (isset($_POST['resetPicture'])) ? true : false;
$id_author = $_SESSION['user']->id;

/**
 * Set the picture of a news
 * @param int $id_actu ID of the news to change the picture to
 * @param array $picture Array of the uploaded picture retrieved from POST vars
 * @return bool
 */
function setPicture($id_actu , $picture) {
	global $_CONF , $resetPicture;
	$db = new Db();
	$file = $id_actu.'.png';
	
	if(!$resetPicture) {
		if($picture['error'] !=  UPLOAD_ERR_OK || !is_uploaded_file($picture['tmp_name'])) {return false;}
		resamplePicture($picture['tmp_name'] , 107 , $_CONF['medias']['actuPictsRoot'].$file , $picture['name']);
	}
	
	$query = 'UPDATE actus SET picture = '.(($resetPicture) ? 'NULL' : '"'.$file.'"').' WHERE id_actu = "'.$id_actu.'"';
	$db->query($query);
	
	$db->close();
	return true;
}

function add () {
	global $title , $id_category , $picture , $content , $id_author;
	$db = new Db();
	
	try {
		// Insert the news into database
		$query = 'INSERT INTO actus (title , content , id_category , id_author, time) VALUES("'.$db->real_escape_string($title).'","'.$db->real_escape_string($content).'",'.((empty($id_category)) ? 'NULL' : '"'.$db->real_escape_string($id_category).'"').',"'.$id_author.'",NOW())';
		$db->query($query);
		if($db->affected_rows === 0) {throw new Exception('No row were created.' , 1);}
		// Set the picture
		setPicture($db->insert_id , $picture);
	} catch(Exception $e) {
		trigger_error('Error while creating a new news : '.$e->getMessage() , E_USER_NOTICE);
		return 'Error while creating the new news';
	}
	
	$db->close();
	return 'News "'.$title.'" successfully created.';
}

function mod () {
	global $title , $id_category , $picture , $content;
	$db = new Db();
	$query = 'SELECT id_actu FROM actus WHERE id_actu = "'.$db->real_escape_string($_GET['id']).'"';
	$result = $db->query($query);
	if($result->num_rows === 0) {return 'There is no news with id#'.$_GET['id'];}
	
	// Set the name of the page
	$query = '
		UPDATE actus SET 
			title = "'.$db->real_escape_string($title).'",
			id_category = '.((empty($id_category)) ? 'NULL' : '"'.$db->real_escape_string($id_category).'"').',
			content = "'.$db->real_escape_string($content).'"
		WHERE id_actu = "'.$db->real_escape_string($_GET['id']).'"';
	$db->query($query);
	// Set the picture
	setPicture($_GET['id'] , $picture);
	
	$db->close();
	return 'News "'.$title.'" successfully updated.';
}

function unPromote() {
	$db = new Db();
	$db->query('UPDATE actus SET promote="0"');
	$db->close();
	return 'News properly un-promoted.';
}

function promote() {
	unPromote();
	$db = new Db();
	$query = 'UPDATE actus SET promote = "1" WHERE id_actu="'.$_GET['id'].'"';
	$db->query($query);
	$db->close();
	return 'News id#'.$_GET['id'].' successfully promoted.';
}

function del () {
	$db = new Db();
	$query = 'DELETE FROM actus WHERE id_actu = "'.$db->real_escape_string($_GET['id']).'"';
	$db->query($query);
	$db->close();
	return 'News id#'.$_GET['id'].' successfully deleted.';
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
	
	case 'prom':
		$message = promote();
		break;
		
	case 'unprom':
		$message = unPromote();
		break;
	
	default:
		$message = 'Unknown action "'.$_GET['action'].'"';
		break;
}

$content = '
	'.$message.'<br /><br />
	Cliquez <a href="pages/admin/actus_list.php" title="Liste">ici</a> si vous n\'ètes pas redirigé automatiquement.
';

header('Refresh: 3;url=actus_list.php');

$page = new Page();
$page->add('content' , $content);
$page->send();
?>
