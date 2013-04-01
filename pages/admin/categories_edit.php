<?php
/**
 * Edit the categories
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
$id_category = 0;
$name = '';
$picture = '';
$action = 'add';
if (isset($_GET['id'])) {
	$result = $db->query('SELECT * FROM categories WHERE id_category="'.$db->real_escape_string($_GET['id']).'"');
	if ($data = $result->fetch_array()) {
		$id_category = $data['id_category'];
		$name = stripslashes($data['name']);
		$picture = $data['default_picture'];
		$action = 'mod';
	}
}

$content = '
<form action="pages/admin/categories_action.php?action='.$action.'&id='.$id_category.'" method="post" enctype="multipart/form-data">
	<table class="noBorder">
		<tr>
			<td class="alignTop">Name : </td>
			<td class="alignTop"><input type="text" name="name" value="'.$name.'" /></td>
		</tr>
		<tr>
			<td class="alignTop noWrap">Illustration : </td>
			<td class="alignTop">
				'.((!empty($picture)) ? '<img src="'.$_CONF['medias']['wwwActuPictsRoot'].$picture.'" alt="" /><br /><input type="checkbox" name="resetPicture" /> Supprimer l\'image actuelle.<br />' : '').'
				L\'illustration qui sera choisie sera affectée aux actualités de cette catégorie lorsque le contenu en sera assez conséquent.<br />
				<input type="file" name="picture" />
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
