<?php
/**
 * Information page about teams
 *
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

require_once('../lib/config.inc.php');
require_once($_CONF['libRoot'].'Page.class.php');
require_once($_CONF['libRoot'].'Db.class.php');

$db = new Db();

$query = '
	SELECT 
		e.id_equipe AS id_team, 
		e.nom AS name_team, 
		e.picture AS picture_team,
		CONCAT(u.prenom , " " , u.nom) AS name_player 
	FROM 
		equipes e
		JOIN users u USING(id_equipe) 
	ORDER BY name_team , name_player
';
$result = $db->query($query);
$prev_id_team = '';
$nb_teams = 0;
$toc = '';
$content = '';
while($data = $result->fetch_array()) {
	if($data['id_team'] != $prev_id_team) {
		$nb_teams++;
		
		// Write the table of content entry for the team
		$toc .= '<li><a href="#team_'.$data['id_team'].'" title="'.$data['name_team'].'">'.$data['name_team'].'</a></li>';
		
		// Write the presentation of the team
		$content .= '
			<h2 id="team_'.$data['id_team'].'">'.$data['name_team'].'</h2>
			<a href="#toc" title="haut" class="goToc"><img src="'.$_CONF['wwwRoot'].'picts/bullet_arrow_up.png" alt="haut" class="noBorder" /></a><br />
			<img src="'.$_CONF['medias']['wwwTeamPictsRoot'].$data['picture_team'].'" alt="" /><br />
			<u>Joueurs</u> :<br />
		';
	}
	// Write the line for a player
	$content .= $data['name_player'].'<br />';
	$prev_id_team = $data['id_team'];
}

$content = '
	Le club des Expos d\'Ermont est composé de '.$nb_teams.' équipes toutes catégories confondues, quel que soit votre âge et votre expérience du jeu vous trouverez forcément votre place dans l\'une d\'elles.<br />
	Différents niveaux de jeu sont bien sûr représentés et on retrouve aussi bien des équipes dont l\'objectif est la compétition que d\'autres pour lesquelles c\'est d\'avantage le "fun" qui sert de moteur.<br />
	Dans tous les cas, comme le baseball aussi bien que le softball sont des sports d\'équipes avant tout, tous les joueurs du club sont intégrés à l\'une d\'entre elles afin de s\'intégrer à un groupe correspondant à ses attentes, son niveau et ses envies.<br />
	<br />
	<strong>Grâce à un large panel de catégories et de niveaux représentés, le club des Expos permet aux joueurs de tout âges et de toutes expériences de s\'intégrer à une équipe lui permettant de découvrir et pratiquer le baseball et/ou le softball dans les meilleures conditions.</strong><br />
	<br />
	Liste des équipes : 
	<ul id="toc">'.$toc.'</ul>'.$content;

$page = new Page();
$page->title = 'Les équipes';
$page->add('content' , $content);
$page->send();
?>