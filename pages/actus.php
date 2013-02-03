<?php
/**
 * Display the news
 *
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

require_once('../lib/config.inc.php');
require_once('Page.class.php');
require_once('Actu.class.php');

$nb_actus = 10;
$num_page = (isset($_GET['page']) && is_string($_GET['page'])) ? $_GET['page'] : 1;
$id_actu = (isset($_GET['id_actu']) && is_string($_GET['id_actu'])) ? $_GET['id_actu'] : null;

$content = '';
$page_title = '';

function getPageLink($num_page) {
	return '<a href="'.setGetVar('page' , $num_page , $_SERVER['PHP_SELF']).'" title="Page '.$num_page.'">'.$num_page.'</a>';
}

$actus = array();
if(is_null($id_actu)) {
	$actus = Actu::getLimitedSet($nb_actus, $num_page);
} else {
	$actus[] = new Actu($id_actu);
}

foreach($actus as $actu) {
	
	// Define and set the controls to display (depending of the privileges of the current user
	$controls = '';
	if(Control::accessToPage('pages/admin/actus_edit.php' , false)) {
		$controls = '
			<br />
			<a href="'.setGetVar('id' , $data['id_actu'] , $_CONF['wwwRoot'].'pages/admin/actus_edit.php').'"><img src="'.$_CONF['wwwRoot'].'picts/pencil.png" alt="Mod." title="Modifier" class="noBorder" /></a>
			<a href="javascript:confirmDel(\''.setGetVar(array('action' , 'id') , array('del' , $data['id_actu']) , $_CONF['wwwRoot'].'pages/admin/actus_action.php').'\');"><img src="'.$_CONF['wwwRoot'].'picts/delete.png" alt="Del." title="Supprimer" class="noBorder" /></a>';
	}
	
	// Define the image to display
	$image = $actu->getPictureUrl();
	if(!empty($image)) {
		$image = '<img src="'.$image.'" alt="" class="illu" />';	
	}
	
	$classPromote = ($actu->isPromoted()) ? 'promote' : '';
	$content .= '
		<div class="actu hentry '.$classPromote.'">
			<h2 class="entry-title"><a href="'.$_CONF['wwwRoot'].'pages/actu/'.urlencode(str_replace('/' , '' , mb_convert_case($actu->getTitle(), MB_CASE_UPPER, 'UTF-8'))).'_'.$actu->getId().'.html" title="'.normalizeToHTML($actu->getTitle()).'">'.normalizeToHTML($actu->getTitle()).'</a></h2>
			<span class="date">le <span class="updated">'.normalizeToHTML($actu->getDate()).'T'.normalizeToHTML($actu->getTime()).'</span></span>
			par <span class="author vcard"><span class="fn">'.$actu->getAuthor().'</span></span> - '.$actu->getCategory().'<br />
			<br />
			'.$image.'
			<div class="entry-content">'.normalizeToHTML($actu->getContent()).'</div>
			'.$controls.'
		</div>
	';
	
	if(!is_null($id_actu)) {
		$page_title = htmlentities($actu->getTitle() , ENT_COMPAT , 'UTF-8');
		$page_description = htmlentities($actu->getTitle() , ENT_COMPAT , 'UTF-8');
	}
}

// Display the number of pages
$content .= '<br />Pages : ';
$last_page = ceil(Actu::getTotalNb() / $nb_actus);
$content .= getPageLink(1).' ';
if($num_page - 3 > 1) {$content .= '.. ';}
for($i = $num_page - 3; $i <= $num_page + 3; $i++) {
	if($i <= 1 || $i >= $last_page) {continue;}
	$content .= getPageLink($i).' ';
}
if($num_page + 3 < $last_page) {$content .= '.. ';}
$content .= getPageLink($last_page);

$content .= '<br /><a href="'.$_CONF['wwwRoot'].'pages/actus_feed.rss.php" title=""><img src="'.$_CONF['wwwRoot'].'picts/rss.png" alt="" align="middle" class="noBorder" /> Flux RSS</a>';
	
$page = new Page();
$page->title = (is_null($id_actu)) ? 'Actualités' : $page_title;
if(!is_null($id_actu)) {$page->description = $page_description;}
$page->add('content' , $content);
$page->send();
?>
