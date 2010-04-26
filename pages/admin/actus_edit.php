<?php
/**
 * Edit the news
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
$id = 0;
$title = '';
$id_category = 0;
$content = '';
$picture = '';
$action = 'add';
if (isset($_GET['id'])) {
	$result = $db->query('SELECT * FROM actus WHERE id_actu="'.$db->real_escape_string($_GET['id']).'"');
	if ($data = $result->fetch_array()) {
		$id = $data['id_actu'];
		$title = stripslashes($data['title']);
		$id_category = $data['id_category'];
		$content = stripslashes($data['content']);
		$picture = $data['picture'];
		$action = 'mod';
	}
}

// Build the categories options
$options_categories = '';
$query = 'SELECT id_category, name FROM categories ORDER BY name';
$result = $db->query($query);
while($data = $result->fetch_array()) {
	$selected = ($data['id_category'] === $id_category) ? 'selected="selected"' : '';
	$options_categories .= '<option value="'.$data['id_category'].'" '.$selected.'>'.$data['name'].'</option>';
}

$content = '
<script type="text/javascript" src="'.$_CONF['wwwRoot'].'js/ckeditor/ckeditor.js"></script>
<form action="actus_action.php?action='.$action.'&id='.$id.'" method="post" enctype="multipart/form-data">
	<table class="noBorder">
		<tr>
			<td class="alignTop">Titre : </td>
			<td class="alignTop"><input type="text" name="title" size="35" value="'.$title.'" /></td>
		</tr>
		<tr>
			<td class="alignTop noWrap">Catégorie : </td>
			<td class="alignTop">
				<select name="id_category">
					<option value="">aucune</option>
					'.$options_categories.'
				</select>
			</td>
		</tr>
		<tr>
			<td class="alignTop noWrap">Illustration : </td>
			<td class="alignTop">
				'.((!empty($picture)) ? '<img src="'.$_CONF['medias']['wwwActuPictsRoot'].$picture.'" alt="" /><br /><input type="checkbox" name="resetPicture" /> Supprimer l\'image actuelle.<br />' : '').'
				Si aucune illustration n\'est ajoutée il en sera affecté une par défaut en fonction de la catégorie (et si le contenu est assez long).<br />
				<input type="file" name="picture" />
			</td>
		</tr>
		<tr>
			<td class="alignTop noWrap">Contenu : </td>
			<td class="alignTop">
				<textarea id="actuContent" name="content" cols="76" rows="15">'.$content.'</textarea>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<button type="submit" class="button">OK</button>
			</td>
		</tr>
	</table>
</form>
<script type="text/javascript">
<!--
CKEDITOR.replace("actuContent",
	{
		toolbar: [
			["Source"],
			["Bold","Italic","Underline","Strike","-","Subscript","Superscript"],
			["NumberedList","BulletedList","-","Outdent","Indent"],
			["JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock"],
			["Link","Unlink"],
			["FontSize"],
			["TextColor"]
		]
	}
);
-->
</script>
';

$page = new Page();
$page->add('content' , $content);
$page->send();
?>
