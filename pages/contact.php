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
		<li class="vcard">Président : <a class="email" href="mailto:president@expos-ermont.com" title="Ecrire"><span class="fn">Alexandre Soulier</span></a> (06 22 68 05 10)</li>
		<li class="vcard">Vice-Président : <span class="fn">Stephan Bacquet</span></li>
		<li class="vcard">Secrétaire : <span class="fn">Eric Faivre</span></li>
		<li class="vcard">Vice-Secrétaire : <span class="fn">Florent Captier</span></li>
		<li class="vcard">Trésorière : <span class="fn">Sally English</span></li>
		<li class="vcard">Vice-Trésorier : <span class="fn">Esteban Pereira</span></li>
	</ul>
	<h2>Les responsables d\'équipes</h2>
	<ul>
		<li>Séniors 1: <span class="vcard"><span class="fn">Florent Captier</span> (<span class="tel">06 80 24 21 26</span>)</span> &amp; <span class="vcard"><span class="fn">Stanislas Klenner</span> (<span class="tel">06 15 87 62 18</span>)</span></li>
		<li>Séniors 2: <span class="vcard"><span class="fn">Romain Odic</span> (<span class="tel">06 07 70 23 02</span>)</span> &amp; <span class="vcard"><span class="fn">Soulier Jeremie</span> (<span class="tel">06 19 99 52 98</span>)</span></li>
		<li>Séniors 3: <span class="vcard"><span class="fn">Regis Minfray</span> (<span class="tel">06 12 52 46 28</span>)</span> &amp; <span class="vcard"><span class="fn">Eric Faivre</span> (<span class="tel">06 80 88 52 82</span>)</span></li>
		<li>Softball: <span class="vcard"><span class="fn">Camilla English</span> (<span class="tel">06 72 24 87 89</span>)</span> &amp; <span class="vcard"><span class="fn">Benoit Hermel</span> (<span class="tel">06 99 01 27 27</span>)</span></li>
		<li>Cadet: <span class="vcard"><span class="fn">Régis Minfray</span> (<span class="tel">06 12 52 46 28</span>)</span> &amp; <span class="vcard"><span class="fn">Thomas Messager</span> (<span class="tel">06 01 96 82 12</span>)</span></li>
		<li>Minimes: <span class="vcard"><span class="fn">Lipcius Patino</span> (<span class="tel">06 31 74 41 77</span>)</span></li>
		<li>Benjamins: <span class="vcard"><span class="fn">Régis Minfray</span> (<span class="tel">06 12 52 46 28</span>)</span> &amp; <span class="vcard"><span class="fn">Clément Greau</span> (<span class="tel">06 33 67 72 69</span>)</span></li>
	</ul>
	
	<h2>Les entraineurs</h2>
	<ul>
		<li class="vcard">Séniors 1 : <span class="fn">Stanislas Klenner</span> (<span class="tel">06 15 87 62 18</span>)</li>
		<li class="vcard">Séniors 2 : <span class="fn">Romain Odic</span> (<span class="tel">06 07 70 23 02</span>)</li>
		<li class="vcard">Séniors 3 : <span class="fn">Régis Minfray</span> (<span class="tel">06 12 52 46 28</span>)</li>
		<li class="vcard">Softball : <span class="fn">Camilla English</span> (<span class="tel">06 72 24 87 89</span>)</li>
		<li class="vcard">Cadets : <span class="fn">Thomas Messager</span> (<span class="tel">06 01 96 82 12</span>)</li>
		<li class="vcard">Minimes : <span class="fn">Lipcius Patino</span> (<span class="tel">06 31 74 41 77</span>)</li>
		<li class="vcard">Benjamins: <span class="fn">Clément Greau</span> (<span class="tel">06 33 67 72 69</span>)</li>
	</ul>
	
	<h2>Le responsable web</h2>
	<div class="vcard">
		<span class="fn">Florent Captier</span> (<a class="email" href="mailto:webmaster@expos-ermont.com" title="M\'écrire">webmaster@expos-ermont.com</a>)
	</div>
';

$page = new Page();
$page->title = 'Contact';
$page->add('content' , $content);
$page->send();
?>
