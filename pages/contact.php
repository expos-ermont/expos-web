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

$page = new Page();
$page->title = 'Contact';
$page->add('content' , file_get_contents($_CONF['root'].DIR_SEP.'pages'.DIR_SEP.'contact.xhtml'));
$page->send();
?>
