<?php
/**
 * List the teams
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

$query = '
	SELECT
		e.id_equipe,
		e.nom,
		COUNT(id_user) AS nb_users
	FROM 
		equipes e 
		LEFT JOIN users u USING(id_equipe)
	GROUP BY e.id_equipe
	ORDER BY nom
';
$result = $db->query($query);

$i = 0;
$rows = '';
while($data = $result->fetch_array()) {
	$i++;
	$bgClass = ($i%2==0) ? 'bgLightGray' : 'bgMediumGray'; 
	$rows .= '
		<tr class="'.$bgClass.'">
			<td>'.stripslashes($data['nom']).'</td>
			<td>'.$data['nb_users'].' joueurs</td>
			<td class="icon"><a href="'.setGetVar('id' , $data['id_equipe'] , 'pages/admin/teams_edit.php').'"><img src="'.$_CONF['wwwRoot'].'picts/pencil.png" alt="Mod." title="Modifier" class="noBorder" /></a></td>
			<td class="icon"><a href="javascript:confirmDel(\''.setGetVar(array('action' , 'id') , array('del' , $data['id_equipe']) , 'pages/admin/teams_action.php').'\');"><img src="'.$_CONF['wwwRoot'].'picts/delete.png" alt="Del." title="Supprimer" class="noBorder" /></a></td>
		</tr>
	';
}

$content = '
	<h2>Administration des équipes</h2>
	<table class="listing">
		<tr class="bgDarkGray">
			<td>'.$result->num_rows.' equipes</td>
			<td colspan="3" class="alignRight"><a href="pages/admin/teams_edit.php"><img src="'.$_CONF['wwwRoot'].'picts/add.png" alt="Add" title="Ajouter" class="noBorder" /></a></td>
		</tr>
		'.$rows.'
	</table>
';

$db->close();

$page = new Page();
$page->add('content' , $content);
$page->send();
?>