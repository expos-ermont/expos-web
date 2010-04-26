<?php echo lang('milestone assigned', $milestone_assigned->getName()) ?>.<?php echo "\r\n"
?><?php echo "\r\n"
?><?php echo lang('view assigned milestones') ?>: <?php echo str_replace('&amp;', '&', $milestone_assigned->getViewUrl()) ?><?php echo "\r\n"
?><?php echo "\r\n"
?><?php echo "\r\n"
?><?php echo lang('company') ?>: <?php echo owner_company()->getName() ?><?php echo "\r\n"
?><?php echo "\r\n"
?><?php echo lang('workspace') ?>: <?php echo $milestone_assigned->getProject()->getName() ?><?php echo "\r\n"
?><?php if (isset($date)) {
		 	echo "\r\n";
		 	echo lang('date') ?>: <?php echo $date ?><?php echo "\r\n";
		}
?><?php echo "\r\n"
?><?php echo "\r\n"
?>--<?php echo "\r\n"
?><?php echo ROOT_URL ?><?php echo "\r\n\r\n\r\n"
?>