<?php
/**
 * Index page of the website
 *
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

require_once('lib/config.inc.php');
require_once($_CONF['libRoot'].'Page.class.php');

// Redirect to news page
/*
header('HTTP/1.1 307 Temporary Redirect');
header('Location: '.$_CONF['wwwRoot'].'pages/actus.php');
exit();
*/

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
		c.name AS cat_name
	FROM
		actus a
		LEFT JOIN categories c USING(id_category)
		JOIN users m ON a.id_author = m.id_user
	ORDER BY
		date DESC, 
		time DESC
	LIMIT 1
';
$result = $db->query($query);
$last_actu = $result->fetch_array();
	// TODO Finir de faire apparaitre une actu dans la page d'accueil

$content = '
	<div class="indexHalfBox">
		<h2>Dernière actualité</h2>
		<h3>'.$last_actu['title'].'</h3>
		'.$last_actu['content'].'
	</div>
	<div class="indexHalfBox">
		blabla
	</div>
	Bienvenue sur le nouveau site des Expos d\'Ermont, cette version apporte son lot de changements et va évoluer encore beaucoup dans les prochaines semaines.<br />
	Retrouvez les <a href="pages/actus.php" title="Actualités">actualités</a>, les <a href="pages/club.php" title="Informations">informations pratiques du club</a>, les <a href="pages/equipes.php" title="Equipes">équipes</a>, la <a href="pages/boutique.php" title="Boutique">boutique</a>, les <a href="pages/medias.php" title="Photos">photos</a>, le <a href="pages/blog" title="Blog">blog</a>, les <a href="pages/liens.php" title="Liens">liens</a>, les différents <a href="pages/contact.php" title="Contacts">contacts</a> et bien d\'autres choses dans les rubriques du menu ci-dessus.<br />
	<br />
	Bonne visite,<br />
	<br />
	Le webmaster des Expos d\'Ermont
';

$page = new Page();
$page->add('content' , $content);
$page->send();
?>