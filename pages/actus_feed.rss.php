<?php 
/**
 * Generate a XML file of the RSS format to feed the news
 *
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

header('Content-Type: text/xml');

require_once('../lib/config.inc.php');
require_once('Page.class.php');
require_once('Db.class.php');

$db = new Db();
$actus = '';
$query = '
	SELECT 
		id_actu,
		title,
		CONCAT(prenom," ",nom) AS auteur,
		content,
		UNIX_TIMESTAMP(time) as time,
		c.name AS cat_name
	FROM 
		actus a
		JOIN users u ON u.id_user = a.id_author
		LEFT JOIN categories c USING(id_category)
	ORDER BY time DESC
';
$result = $db->query($query);
while($data = $result->fetch_array()) {
	$actus .= '
		<item>
			<title>'.$data['title'].'</title>
			<link>'.$_CONF['wwwRoot'].'pages/actu/'.urlencode(str_replace('/' , '' , mb_convert_case($data['title'] , MB_CASE_UPPER , 'UTF-8'))).'_'.$data['id_actu'].'.html</link>
			<guid>'.$_CONF['wwwRoot'].'pages/actu/'.urlencode(str_replace('/' , '' , mb_convert_case($data['title'] , MB_CASE_UPPER , 'UTF-8'))).'_'.$data['id_actu'].'.html</guid>
			<pubDate>'.date('r' , $data['time']).'</pubDate>
			<description><![CDATA['.normalizeToHTML($data['content']).']]></description>
			<author>'.$data['auteur'].'</author>
			<category>'.$data['cat_name'].'</category>      
		</item>
	';
}
$db->close();

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