<?php
header('Content-Type: image/png');

require_once('../lib/config.inc.php');
require_once($_CONF['libRoot'].'functions.inc.php');

$image = $_CONF['medias']['pictsRoot'].$_GET['i'];

resamplePicture($image);
?>