<?php
/**
 * Edit the teams
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

//Initialisation des variables
$id_team = 0;
$name = '';
$ids_users = array();
$championship = '';
$picture = '';
$action = 'add';

if (isset($_GET['id'])) {
	$query = '
		SELECT
			e.id_equipe,
			e.nom,
			e.picture,
			GROUP_CONCAT(u.id_user) AS ids_users
		FROM
			equipes e
			LEFT JOIN users u USING(id_equipe)
		WHERE e.id_equipe = "'.$db->real_escape_string($_GET['id']).'"
		GROUP BY e.id_equipe
	';
	$result = $db->query($query);
	if ($data = $result->fetch_array()) {
		$id_team = $data['id_equipe'];
		$name = stripslashes($data['nom']);
		$picture = $data['picture'];
		$ids_users = split(',' , $data['ids_users']);
		$action = 'mod';
	}
}

// Build the championships options
$options_championships = '';
$query = 'SELECT id_championship, name FROM championships ORDER BY name';
$result = $db->query($query);
while($data = $result->fetch_array()) {
	$selected = (in_array($data['id_championship'] , $ids_users)) ? 'selected="selected"' : '';
	$options_championships .= '<option value="'.$data['id_championship'].'" '.$selected.'>'.$data['name'].'</option>';
}

// Build the users options
$options_users = '';
$query = 'SELECT id_user, CONCAT(prenom , " " , nom) AS name FROM users ORDER BY name';
$result = $db->query($query);
while($data = $result->fetch_array()) {
	$selected = (in_array($data['id_user'] , $ids_users)) ? 'selected="selected"' : '';
	$options_users .= '<option value="'.$data['id_user'].'" '.$selected.'>'.$data['name'].'</option>';
}

$content = '
<form action="pages/admin/teams_action.php?action='.$action.'&id='.$id_team.'" method="post" enctype="multipart/form-data">
	<table class="noBorder">
		<tr>
			<td class="alignTop">Nom : </td>
			<td class="alignTop"><input type="text" name="name" value="'.$name.'" /></td>
		</tr>
		<tr>
			<td class="alignTop">Photo : </td>
			<td class="alignTop">
				'.((!empty($picture)) ? '<img src="'.$_CONF['medias']['wwwTeamPictsRoot'].$picture.'" alt="" /><br /><input type="checkbox" name="resetPicture" /> Supprimer l\'image actuelle.<br />' : '').'
				<input type="file" name="picture" />
			</td>
		</tr>
		<tr>
			<td class="alignTop">Championnat : </td>
			<td class="alignTop">
				<select name="id_championship">
					<option value=""></option>
					'.$options_championships.'
				</select>
			</td>
		</tr>
		<tr>
			<td class="alignTop">Joueurs : </td>
			<td>
				<select multiple="multiple" name="ids_users[]">
					'.$options_users.'
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<button type="submit" class="button">OK</button>
			</td>
		</tr>
	</table>
</form>
<div id="preview"></div>
';

$page = new Page();
$page->add('content' , $content);
$page->send();
?>
