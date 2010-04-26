<?php
/**
 * Logout the current user and redirect him to the previous visited page
 *
 * @var string ref GET var which indicate the page to redirect the user to
 *
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

require_once('../lib/config.inc.php');

unset($_SESSION['user']);

header('Location: '.$_GET['ref']);
?>