------------------------------------------------------------<?php echo "\r\n"
?><?php echo lang('dont reply wraning') ?><?php echo "\r\n"
?>------------------------------------------------------------<?php echo "\r\n"
?><?php echo "\r\n"
?><?php echo lang("$context $type reminder desc", $object->getObjectName(), $date->format("Y/m/d H:i:s")) ?>.<?php echo "\r\n"
?><?php echo "\r\n"
?><?php echo lang("view $type") ?>: <?php echo str_replace('&amp;', '&', $object->getViewUrl()) ?><?php echo "\r\n"
?><?php echo "\r\n"
?><?php echo "\r\n"
?><?php echo lang('company') ?>: <?php echo owner_company()->getName() ?><?php echo "\r\n"
?><?php echo "\r\n"
?><?php echo "\r\n"
?>--<?php echo "\r\n"
?><?php echo ROOT_URL ?><?php echo "\r\n\r\n\r\n"
?>
