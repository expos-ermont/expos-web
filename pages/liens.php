<?php
/**
 * Links page
 * 
 * @filesource
 * @author Florent Captier <florent@captier.org>
 */

require_once('../lib/config.inc.php');
require_once($_CONF['libRoot'].'Db.class.php');
require_once($_CONF['libRoot'].'Page.class.php');

$content = '
	<h2>Site d\'informations</h2>
	<a href="http://www.rds.ca/baseball/" title="">http://www.rds.ca/baseball/</a><br />
	Le site Québécois de résultats et d\'informations sur le baseball Nord-américains et canadiens. Le plus de RDS est le contenu de ses « nouvelles ». Blessures, anecdotes, résumé complet des matchs et même des jeux Flash. A ne pas rater !<br />
	<br />
	<a href="http://mlb.mlb.com" title="">http://mlb.mlb.com</a><br />
	Le site officiel de la Major ligue Baseball. Consultez les résultats des équipes professionnelles de la ligue nord-américaine. Des infos, stats, e-shop et vidéos de vos équipes favorites sont disponiblent en continu.<br />
	<br />
	<a href="http://www.ffbsc.org/" title="">http://www.ffbsc.org/</a><br />
	Le site officiel de la Fédération Française de Baseball Softball. Consultez les résultats des divers championnats français, l\'actu de nos équipes nationales et du développement du baseball en France. Ce site est agrémenté de vidéos et photographies.<br />
	<br />
	<a href="http://www.ibaf.org/" title="">http://www.ibaf.org/</a><br />
	Le site de la Fédération internationale de baseball ("International Baseball Fédération" en anglais)<br />
	<br />
	<a href="http://www.bafinfos.com" title="">http://www.bafinfos.com</a><br />
	Le site du "Baseball à la française", ce dernier à pour but de vous tenir informer des résultats et de l\'actualité des championnats français. Des résumés des matchs sont disponibles le lundi après chaque journée de championnat.<br />
	
	<h2>Site de vente d\'articles de baseball (livraison en France)</h2>
	<a href="http://www.teamscolors.com" title="">www.teamscolors.com</a><br />
	<a href="http://www.covee.nl" title="">www.covee.nl</a><br />
	<a href="http://www.forelle.com" title="">www.forelle.com</a><br />	
	<a href="http://www.eastbay.com" title="">www.eastbay.com</a><br />
	<a href="http://www.the-baseball-shop.com" title="">www.the-baseball-shop.com</a><br />
	<a href="http://www.lacebaseball.com" title="">www.lacebaseball.com</a><br />
	<a href="http://www.ebay.com" title="">www.ebay.com</a><br />
	<a href="http://www.nike.com" title="">www.nike.com</a><br />
	<a href="http://www.taiwanbaseball.com.tw" title="">www.taiwanbaseball.com.tw</a><br />
	<a href="http://www.justwoodbats.com" title="">www.justwoodbats.com</a><br />
	<a href="http://www.hq4sports.com/" title="">www.hq4sports.com</a>
';

$page = new Page();
$page->title = 'Liens';
$page->add('content' , $content);
$page->send();
?>