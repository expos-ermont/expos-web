<?php
/**
 * Information page about teams
 *
 * TODO faire un vrai formulaire de contact
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

require_once('../lib/config.inc.php');
require_once($_CONF['libRoot'].'Page.class.php');

$content = '
	<h2>Le comité directeur</h2>
	<ul>
		<li>Président : <a href="mailto:president@expos-ermont.com" title="Ecrire">Alexandre Soulier</a> (06 22 68 05 10)</li>
		<li>Vice-Président : Fabien Rousset</li>
		<li>Secrétaire : Eric Faivre</li>
		<li>Vice-Secrétaire : Florent Captier</li>
		<li>Trésorière : Sally English</li>
		<li>Vice-Trésorier : Benoit Hermel</li>
	</ul>
	<h2>Les responsables d\'équipes</h2>
	<ul>
		<li>Séniors 1: Florent Captier (06 80 24 21 26) & Stanislas Klener (06 16 54 78 00)</li>
		<li>Séniors 2: Romain Odic (06 07 70 23 02) & Soulier Jeremie (06 19 99 52 98)</li>
		<li>Séniors 3: Regis Minfray (06 12 52 46 28) & Eric Faivre (06 80 88 52 82)</li>
		<li>Softball: Camilla English (06 72 24 87 89) & Benoit Hermel (06 99 01 27 27)</li>
		<li>Cadet: Régis Minfray (06 12 52 46 28) & Thomas Messager (06 01 96 82 12)</li>
		<li>Minimes: Lipcius Patino (06 31 74 41 77)</li>
		<li>Benjamins: Régis Minfray (06 12 52 46 28) & Clément Greau (06 33 67 72 69)</li>
	</ul>
	
	<h2>Les entraineurs</h2>
	<ul>
		<li>Séniors 1 : Stanislas Klener (06 15 87 62 18)</li>
		<li>Séniors 2 : Romain Odic (06 07 70 23 02)</li>
		<li>Séniors 3 : Régis Minfray (06 12 52 46 28)</li>
		<li>Softball : Camilla English (06 72 24 87 89)</li>
		<li>Cadets : Thomas Messager (06 01 96 82 12)</li>
		<li>Minimes : Lipcius Patino (06 31 74 41 77)</li>
		<li>Benjamins: Clément Greau (06 33 67 72 69)</li>
	</ul>
	
	<h2>Le responsable web</h2>
	Florent Captier (<a href="mailto:webmaster@expos-ermont.com" title="M\'écrire">webmaster@expos-ermont.com</a>)<br />
';

$page = new Page();
$page->title = 'Contact';
$page->add('content' , $content);
$page->send();
?>
