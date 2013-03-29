<?php 
/**
 * Generate a XML file of the RSS format to feed the news
 *
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

header('Content-Type: text/xml');

require_once('../lib/config.inc.php');
require_once('Actu.class.php');

$actus = '';

foreach(Actu::getLimitedSet(100) as $actu) {
	$actus .= '
		<item>
			<title>'.str_replace('&', '&amp;', $actu->getTitle()).'</title>
			<link>'.$_CONF['wwwRoot'].'pages/actu/'.urlencode(str_replace('/' , '' , mb_convert_case($actu->getTitle() , MB_CASE_UPPER , 'UTF-8'))).'_'.$actu->getId().'.html</link>
			<guid>'.$_CONF['wwwRoot'].'pages/actu/'.urlencode(str_replace('/' , '' , mb_convert_case($actu->getTitle() , MB_CASE_UPPER , 'UTF-8'))).'_'.$actu->getId().'.html</guid>
			<pubDate>'.date('r' , $actu->getUnixTime()).'</pubDate>
			<description><![CDATA['.normalizeToHTML($actu->getContent()).']]></description>
			<author>'.$actu->getAuthor().'</author>
			<category>'.$actu->getCategory().'</category>      
		</item>
	';
}

echo '<?xml version="1.0" encoding="utf-8"?>
	<rss version="2.0">
	  <channel>
		<title>'.$_CONF['title'].'</title>
		<link>'.$_CONF['wwwRoot'].'</link>
		<description>'.$_CONF['description'].'</description>
		<language>fr-FR</language>
		<pubDate>'.date('r').'</pubDate>
		<lastBuildDate>'.date('r').'</lastBuildDate>
		<generator>El webmaster himself</generator>
		<webMaster>webmaster@expos-ermont.com</webMaster>
		<image>
		  <url>'.$_CONF['wwwRoot'].'picts/icon.png</url>
		  <title>Icone des Expos</title>
		  <link>'.$_CONF['wwwRoot'].'</link>
		</image>
		<copyright>expos-ermont.com / Tous droits réservés</copyright>
		'.$actus.'
	  </channel>
	</rss>
';
?>