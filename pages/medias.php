<?php
/**
 * Display the medias
 *
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

require_once('../lib/config.inc.php');
require_once($_CONF['libRoot'].'Page.class.php');

$sysDir = $_CONF['medias']['pictsRoot'];
$wwwDir = $_CONF['wwwRoot'].'picts/photos/';

$curDir = (isset($_GET['dir']) && !empty($_GET['dir'])) ? urldecode($_GET['dir']) : '';

// Normalize the current directory in order to prevent some hacks
$curDir = preg_replace('/^[\.\\\\\/]*/' , '' , $curDir); // Remove leading ./\ to prevent the script to display content of a relative directory
$curDir = rtrim($curDir , DIR_SEP); // Remode ending DIR_SEP to prevent compatibility
$curDir .= DIR_SEP;

$content = '
	<h2>Les photos</h2>
';

$content .= '<p class="rail"><a href="'.setGetVar('dir' , '').'">Photos</a>'.DIR_SEP;
$l = '';
foreach(explode(DIR_SEP , $curDir) as $t) {
	if(empty($t)) {continue;}
	$l .= (empty($l)) ? $t : DIR_SEP.$t;
	$content .= '<a href="'.setGetVar('dir' , $l).'">'.$t.'</a>'.DIR_SEP;
}
$content .= '</p>';

// if ($curDir != '') {
	// $prevDir = preg_replace('/[^\\'.DIR_SEP.']+\\'.DIR_SEP.'$/' , '' , $curDir);
	// $content .= '<a href="'.setGetVar('dir' , $prevDir).'">retour</a><br />';
// }

$dir = opendir($sysDir.$curDir);
$folders = '<p>';
$files = '<p>';
while($elem = readdir($dir)) {
	if(preg_match('/^\./' , $elem)) {continue;} // Jump to the next element if the current one is an hidden one
	if (is_file($sysDir.$curDir.$elem)) {
		$image_size = getimagesize($sysDir.$curDir.$elem);
		$normImg = preg_replace('/\\\/' , '/' , $wwwDir.$curDir.$elem);
		$files .= '<a href="javascript:new_window(\''.($image_size[0]+20).'\',\''.($image_size[1]+20).'\',\''.$normImg.'\');"><img class="img" src="'.setGetVar('i' , $curDir.$elem , $_CONF['wwwRoot'].'picts/thumbnails.img.php').'" title="'.$elem.'" alt="" /></a> ';
	} elseif ($elem != '.' && $elem != '..') {
		$folders .= '<img src="'.$_CONF['wwwRoot'].'picts/folder.png" alt="" align="middle" /> <a href="'.setGetVar('dir' , $curDir.$elem).'">'.$elem.'</a><br />';
	}
}
$folders .= '</p>';
$files .= '</p>';

$content .= $folders.$files;

$page = new Page();
$page->add('content' , $content);
$page->send();
?>
