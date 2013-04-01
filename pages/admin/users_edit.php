<?php
/**
 * Edit user informations
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

$id_user = (isset($_GET['id']) && (int) $_GET['id'] !== 0) ? (int) $_GET['id'] : null;

// Set the default values
$action = 'add';
$firstname = '';
$lastname = '';
$mail = '';
$login = '';
$granted_rights = array();

// If this is a modification retrieve the values from DB
if(!is_null($id_user)) {
	$query = 'SELECT id_user, nom , prenom , mail , login FROM users WHERE id_user = "'.$db->real_escape_string($id_user).'"';
	$result = $db->query($query);
	if($result->num_rows === 0) {
		$error = 'User id#'.$id_user.' doesn\'t exists, by submitting this form you\'ll create a new entry.';
	} else {
		$data = $result->fetch_array();
		$action = 'mod';
		$firstname = $data['prenom'];
		$lastname = $data['nom'];
		$mail = $data['mail'];
		$login = $data['login'];
		
		// Retrieve the granted rights
		$query = 'SELECT id_right FROM rights JOIN users_rights USING(id_right) WHERE id_user = "'.$db->real_escape_string($id_user).'"';
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
	<script language="javascript">
		<!--
		var action = "'.$action.'";
		
		function fillLogin() {
			var firstName = document.getElementById("firstname").value;
			var lastName = document.getElementById("lastname").value;
			
			if(action == "add") {document.getElementById("login").value = firstName.toLowerCase()+"."+lastName.toLowerCase();}
		}
		-->
	</script>
	'.((isset($error)) ? '<div class="error">'.$error.'</div>' : '').'
	<form method="post" action="'.setGetVar(array('action' , 'id') , array($action , $id_user) , 'pages/admin/users_action.php').'">
		<table class="noBorder">
			<tr>
				<td>Nom : </td>
				<td><input type="text" name="lastname" id="lastname" value="'.$lastname.'" onkeyup="fillLogin();" /></td>
			</tr>
			<tr>
				<td>Prénom : </td>
				<td><input type="text" name="firstname" id="firstname" value="'.$firstname.'" onkeyup="fillLogin();" /></td>
			</tr>
			<tr>
				<td>Mail : </td>
				<td><input type="text" name="mail" value="'.$mail.'" /></td>
			</tr>
			<tr>
				<td>Login : </td>
				<td><input type="text" name="login" id="login" value="'.$login.'" /></td>
			</tr>
			<tr>
				<td colspan="2"><input type="checkbox" name="genPassword" value="1" '.(($action == 'add') ? 'checked="checked" disabled="disabled"' : '').' /> Générer un mot de passe (il sera automatiquement envoyé par email à l\'adresse ci-dessus) ?</td>
			</tr>
			<tr>
				<td class="alignTop">Droits autorisés : </td>
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