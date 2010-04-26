<?php
/**
 * Display the news
 *
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

require_once('../lib/config.inc.php');
require_once($_CONF['libRoot'].'Page.class.php');
require_once($_CONF['libRoot'].'Db.class.php');

$nb_actus = 10;
$first_actu = (isset($_GET['page']) && is_string($_GET['page'])) ? ($_GET['page'] - 1) * $nb_actus : 0;
$id_actu = (isset($_GET['id_actu']) && is_string($_GET['id_actu'])) ? $_GET['id_actu'] : null;

$content = '';
$page_title = '';
$db = new Db();

$query = '
	SELECT
		id_actu,
		title,
		content,
		date(time) AS date,
		time(time) AS time,
		CONCAT(prenom , " " , nom) AS author,
		picture,
		c.default_picture,
		c.name AS cat_name,
		promote
	FROM
		actus a
		LEFT JOIN categories c USING(id_category)
		JOIN users m ON a.id_author = m.id_user
	'.((!is_null($id_actu)) ? 'WHERE id_actu = "'.$db->real_escape_string($id_actu).'"' : '').'
	ORDER BY
		promote DESC,
		date DESC, 
		time DESC
	LIMIT '.$db->real_escape_string($first_actu).' , '.$nb_actus.'
';
$result = $db->query($query);

while($data = $result->fetch_array()) {
	
	// Define the picture to display
	$image = '';
	if(!empty($data['picture'])) {
		$image = '<img src="'.$_CONF['medias']['wwwActuPictsRoot'].$data['picture'].'" alt="" class="illu" />';
	} elseif(strlen($data['content'])>300) {
		if(!empty($data['default_picture'])) {
			$image = '<img src="'.$_CONF['medias']['wwwActuPictsRoot'].$data['default_picture'].'" alt="" class="illu" />';
		} /*else {
			$image = '<img src="'.$_CONF['medias']['wwwActuPictsRoot'].'default.png" alt="" />';
		}*/
	}
	
	// Define and set the controls to display (depending of the privileges of the current user
	$controls = '';
	if(Control::accessToPage('pages/admin/actus_edit.php' , false)) {
		$controls = '
			<br />
			<a href="'.setGetVar('id' , $data['id_actu'] , $_CONF['wwwRoot'].'pages/admin/actus_edit.php').'"><img src="'.$_CONF['wwwRoot'].'picts/pencil.png" alt="Mod." title="Modifier" class="noBorder" /></a>
			<a href="javascript:confirmDel(\''.setGetVar(array('action' , 'id') , array('del' , $data['id_actu']) , $_CONF['wwwRoot'].'pages/admin/actus_action.php').'\');"><img src="'.$_CONF['wwwRoot'].'picts/delete.png" alt="Del." title="Supprimer" class="noBorder" /></a>';
	}
	
	$classPromote = ($data['promote'] == 1) ? ' promote' : '';
	
	$content .= '
		<div class="actu'.$classPromote.'">
			<h2><a href="'.$_CONF['wwwRoot'].'pages/actu/'.urlencode(str_replace('/' , '' , mb_convert_case($data['title'] , MB_CASE_UPPER , 'UTF-8'))).'_'.$data['id_actu'].'.html" title="'.normalizeToHTML($data['title']).'">'.normalizeToHTML($data['title']).'</a></h2>
			<span class="date">le '.normalizeToHTML($data['date']).' à '.normalizeToHTML($data['time']).'</span>
			par '.$data['author'].' - '.$data['cat_name'].'<br />
			<br />
			'.$image.'
			'.normalizeToHTML($data['content']).'
			'.$controls.'
		</div>
	';
	if(!is_null($id_actu)) {
			$page_title = htmlentities($data['title'] , ENT_COMPAT , 'UTF-8');
			$page_description = htmlentities($data['content'] , ENT_COMPAT , 'UTF-8');
	}
}

// Display the number of pages
$content .= '<br />Pages : ';
$query = 'SELECT CEIL(count(*) / '.$nb_actus.') AS nb_pages FROM actus';
$result = $db->query($query);
if($data = $result->fetch_array()) {
	for($i = 1; $i <= $data['nb_pages']; $i++) {
		$content .= '<a href="'.setGetVar('page' , $i , $_SERVER['PHP_SELF']).'" title="Page '.$i.'">'.$i.'</a> ';
	}
}

$content .= '<br /><a href="'.$_CONF['wwwRoot'].'pages/actus_feed.rss.php" title=""><img src="'.$_CONF['wwwRoot'].'picts/rss.png" alt="" align="middle" class="noBorder" /> Flux RSS</a>';

$db->close();
	
$page = new Page();
$page->title = (is_null($id_actu)) ? 'Actualités' : $page_title;
if(!is_null($id_actu)) {$page->description = $page_description;}
$page->add('content' , $content);
$page->send();
?>
