<?php
/**
 * Store page
 *
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

require_once('../lib/config.inc.php');
require_once($_CONF['libRoot'].'Page.class.php');
require_once($_CONF['libRoot'].'Db.class.php');

$content = '
	<h2>Boutique Expos</h2>
	<table>
		<tr>
			<td><img alt="T-S Noir" src="'.$_CONF['wwwRoot'].'picts/Boutique/tee-shirt.JPG" /></td>
			<td>
				Tee-Shirt Noir<br />
				Logo: Expos<br />
				<br />
				5€
			</td>
		</tr>
		<tr>
			<td><img alt="Casquette Fermée" src="'.$_CONF['wwwRoot'].'picts/Boutique/Casquette.JPG" /></td>
			<td>
				Casquette fermée noire<br />
				Logo: Abeille<br />
				<br />
				15€
			</td>
		</tr>
		<tr>
			<td><img alt="Tour de cou" src="'.$_CONF['wwwRoot'].'picts/Boutique/tour_de_cou.JPG" /></td>
			<td>
				Tour de cou<br />
				Expos Ermont<br />
				<br />
				2€
			</td>
		</tr>
	</table>
';

$page = new Page();
$page->add('content' , $content);
$page->send();
?>