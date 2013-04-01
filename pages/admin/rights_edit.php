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

$db = new Db();

$id_right = (isset($_GET['id']) && (int) $_GET['id'] !== 0) ? (int) $_GET['id'] : null;

// Set the default values
$action = 'add';
$name = '';
$granted_pages = array();
$granted_users = array();

// If this is a modification retrieve the values from DB
if(!is_null($id_right)) {
	$query = 'SELECT id_right, name FROM rights WHERE id_right = "'.$db->real_escape_string($id_right).'"';
	$result = $db->query($query);
	if($result->num_rows === 0) {
		$error = 'Right id#'.$id_right.' doesn\'t exists, by submitting this form you\'ll create a new entry.';
	} else {
		$data = $result->fetch_array();
		$action = 'mod';
		$name = $data['name'];
		
		// Retrieve the granted pages
		$query = 'SELECT id_page FROM pages JOIN pages_rights USING(id_page) WHERE id_right = "'.$db->real_escape_string($id_right).'"';
		$result = $db->query($query);
		while($data = $result->fetch_array()) {$granted_pages[] = $data['id_page'];}
		
		// Retrieve the granted users
		$query = 'SELECT id_user FROM users JOIN users_rights USING(id_user) WHERE id_right = "'.$db->real_escape_string($id_right).'"';
		$result = $db->query($query);
		while($data = $result->fetch_array()) {$granted_users[] = $data['id_user'];}
	}
}

// Build the pages options
$options_pages = '';
$query = 'SELECT id_page, name FROM pages ORDER BY name';
$result = $db->query($query);
while($data = $result->fetch_array()) {
	$selected = (in_array($data['id_page'] , $granted_pages)) ? 'selected="selected"' : '';
	$options_pages .= '<option value="'.$data['id_page'].'" '.$selected.'>'.$data['name'].'</option>';
}

// Build the users options
$options_users = '';
$query = 'SELECT id_user, CONCAT(prenom , " " , nom) AS name FROM users ORDER BY name';
$result = $db->query($query);
while($data = $result->fetch_array()) {
	$selected = (in_array($data['id_user'] , $granted_users)) ? 'selected="selected"' : '';
	$options_users .= '<option value="'.$data['id_user'].'" '.$selected.'>'.$data['name'].'</option>';
}

$content = '
	'.((isset($error)) ? '<div class="error">'.$error.'</div>' : '').'
	<form method="post" action="'.setGetVar(array('action' , 'id') , array($action , $id_right) , 'pages/admin/rights_action.php').'">
		<table class="noBorder">
			<tr>
				<td>Nom : </td>
				<td><input type="text" name="name" value="'.$name.'" /></td>
			</tr>
			<tr>
				<td class="alignTop">Pages authorisées : </td>
				<td>
					<select multiple="multiple" name="id_pages[]">
						'.$options_pages.'
					</select>
				</td>
			</tr>
			<tr>
				<td class="alignTop">Utilisateurs authorisés : </td>
				<td>
					<select multiple="multiple" name="id_users[]">
						'.$options_users.'
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<button type="submit">Valider</button>
				</td>
			</tr>
		</table>
	</form>
';

$db->close();

$page = new Page();
$page->add('content' , $content);
$page->send();
?>