<?php
/**
 * List the rights
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
		id_right,
		name
	FROM 
		rights
	ORDER BY name
';
$result = $db->query($query);

$i = 0;
$rows = '';
while($data = $result->fetch_array()) {
	$i++;
	$bgClass = ($i%2==0) ? 'bgLightGray' : 'bgMediumGray'; 
	$rows .= '
		<tr class="'.$bgClass.'">
			<td>'.$data['name'].'</td>
			<td class="icon"><a href="pages/admin/rights_edit.php?id='.$data['id_right'].'"><img src="'.$_CONF['wwwRoot'].'picts/pencil.png" alt="Mod." title="Modifier" class="noBorder" /></a></td>
			<td class="icon"><a href="javascript:confirmDel(\''.setGetVar(array('action' , 'id') , array('del' , $data['id_right']) , 'pages/admin/rights_action.php').'\');"><img src="'.$_CONF['wwwRoot'].'picts/delete.png" alt="Del." title="Supprimer" class="noBorder" /></a></td>
		</tr>
	';
}

$content = '
	<h2>Administration des droits</h2>
	<table class="listing">
		<tr class="bgDarkGray">
			<td>'.$result->num_rows.' droits</td>
			<td colspan="2" class="alignRight"><a href="pages/admin/rights_edit.php"><img src="'.$_CONF['wwwRoot'].'picts/add.png" alt="Add" title="Ajouter" class="noBorder" /></a></td>
		</tr>
		'.$rows.'
	</table>
';

$db->close();

$page = new Page();
$page->add('content' , $content);
$page->send();
?>