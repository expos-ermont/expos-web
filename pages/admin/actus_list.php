<?php
/**
 * List the news
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
		a.id_actu AS id,
		a.title,
		u.nom,
		u.prenom,
		DATE_FORMAT(a.time , "%d/%m/%Y") AS date,
		DATE_FORMAT(a.time , "%H:%i") AS time,
		promote
	FROM 
		actus a
		JOIN users u ON a.id_author=u.id_user 
	ORDER BY a.time DESC
';
$result = $db->query($query);

$i = 0;
$rows = '';
while($data = $result->fetch_array()) {
	$i++;
	$bgClass = ($i%2==0) ? 'bgLightGray' : 'bgMediumGray';
	$promPict = ($data['promote'] == 1) ? 'star.png' : 'star_bw.png';
	$promAction = ($data['promote'] == 1) ? 'unprom' : 'prom';
	$rows .= '
		<tr class="'.$bgClass.'">
			<td>'.stripslashes($data['title']).'</td>
			<td>'.$data['prenom'].' '.$data['nom'].'</td>
			<td class="noWrap">le '.$data['date'].' à '.$data['time'].'</td>
			<td class="icon"><a href="'.setGetVar('id' , $data['id'] , 'pages/admin/actus_edit.php').'"><img src="'.$_CONF['wwwRoot'].'picts/pencil.png" alt="Mod." title="Modifier" class="noBorder" /></a></td>
			<td class="icon"><a href="'.setGetVar(array('action' , 'id') , array($promAction , $data['id']) , 'pages/admin/actus_action.php').'"><img src="'.$_CONF['wwwRoot'].'picts/'.$promPict.'" alt="Prom." class="noBorder" /></a></td>
			<td class="icon"><a href="javascript:confirmDel(\''.setGetVar(array('action' , 'id') , array('del' , $data['id']) , 'pages/admin/actus_action.php').'\');"><img src="'.$_CONF['wwwRoot'].'picts/delete.png" alt="Del." title="Supprimer" class="noBorder" /></a></td>
		</tr>
	';
}

$content = '
	<h2>Administration des actualités</h2>
	<table class="listing">
		<tr class="bgDarkGray">
			<td>'.$result->num_rows.' actus</td>
			<td colspan="5" class="alignRight"><a href="pages/admin/actus_edit.php"><img src="'.$_CONF['wwwRoot'].'picts/add.png" alt="Add" title="Ajouter" class="noBorder" /></a></td>
		</tr>
		'.$rows.'
	</table>
';

$db->close();

$page = new Page();
$page->add('content' , $content);
$page->send();
?>