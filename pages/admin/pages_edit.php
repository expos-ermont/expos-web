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

$id_page = (isset($_GET['id']) && (int) $_GET['id'] !== 0) ? (int) $_GET['id'] : null;

// Set the default values
$action = 'add';
$name = '';
$granted_rights = array();

// If this is a modification retrieve the values from DB
if(!is_null($id_page)) {
	$query = 'SELECT id_page, name FROM pages WHERE id_page = "'.$db->real_escape_string($id_page).'"';
	$result = $db->query($query);
	if($result->num_rows === 0) {
		$error = 'Page id#'.$id_right.' doesn\'t exists, by submitting this form you\'ll create a new entry.';
	} else {
		$data = $result->fetch_array();
		$action = 'mod';
		$name = $data['name'];
		
		// Retrieve the granted rights
		$query = 'SELECT id_right FROM rights JOIN pages_rights USING(id_right) WHERE id_page = "'.$db->real_escape_string($id_page).'"';
		$result = $db->query($query);
		while($data = $result->fetch_array()) {$granted_rights[] = $data['id_right'];}
	}
}

// Build the rights options
$options_rights = '';
$query = 'SELECT id_right, name FROM rights ORDER BY name';
$result = $db->query($query);
while($data = $result->fetch_array()) {
	$selected = (in_array($data['id_right'] , $granted_rights)) ? 'selected="selected"' : '';
	$options_rights .= '<option value="'.$data['id_right'].'" '.$selected.'>'.$data['name'].'</option>';
}

$content = '
	'.((isset($error)) ? '<div class="error">'.$error.'</div>' : '').'
	<form method="post" action="'.setGetVar(array('action' , 'id') , array($action , $id_page) , 'pages_action.php').'">
		<table class="noBorder">
			<tr>
				<td>Nom : </td>
				<td><input type="text" name="name" value="'.$name.'" /></td>
			</tr>
			<tr>
				<td class="alignTop">Droits authorisés : </td>
				<td>
					<select multiple="multiple" name="id_rights[]">
						'.$options_rights.'
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