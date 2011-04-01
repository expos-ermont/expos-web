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
		<li>Président : <a href="mailto:president@expos-ermont.com" title="Ecrire">Alexandre Soulier</a></li>
		<li>Vice-Président : Fabien Rousset</li>
		<li>Secrétaire : Eric Faivre</li>
		<li>Vice-Secrétaire : Florent Captier</li>
		<li>Trésorière : Sally English</li>
		<li>Vice-Trésorier : Benoit Hermel</li>
		<li>Communication/Marketing : Brice Lardereau</li>
	</ul>
	<h2>Les responsables d\'équipes</h2>
	<ul>
		<li>Séniors 1 : Florent Captier & Alexandre Soulier</li>
		<li>Séniors 2 : Eric Faivre & Brice Lardereau</li>
		<li>Séniors 3 : Régis Minfray</li>
		<li>Softball : Camilla English & Benoit Hermel</li>
		<li>Cadet: Régis Minfray</li>
		<li>Minimes : Thomas Messager & Thomas Gicquel</li>
	</ul>
	
	<h2>Les entraineurs</h2>
	<ul>
		<li>Séniors 1 : Stanislas Klener (06 15 87 62 18)</li>
		<li>Séniors 2 : Régis Minfray & Stanislas Klenner</li>
		<li>Séniors 3 : Régis Minfray (06 12 52 46 28)</li>
		<li>Softball : Camilla English (06 72 24 87 89)</li>
		<li>Cadets : Régis Minfray & Stanislas Klenner</li>
		<li>Minimes : Thomas Messager (06 01 96 82 12)</li>
	</ul>
	
	<h2>Le responsable web</h2>
	Florent Captier (<a href="mailto:webmaster@expos-ermont.com" title="M\'écrire">webmaster@expos-ermont.com</a>)<br />
';

$page = new Page();
$page->title = 'Contact';
$page->add('content' , $content);
$page->send();
?>
