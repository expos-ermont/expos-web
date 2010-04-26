------------------------------------------------------------<?php echo "\r\n"
?><?php echo lang('dont reply wraning') ?><?php echo "\r\n"
?>------------------------------------------------------------<?php echo "\r\n"
?><?php echo "\r\n"
?><?php
	echo lang('event invitation response');
	$projectName = $event->getProject()->getName();
	echo ': ' . $event->getSubject() . ' - ' . $projectName . ' - ';
	echo lang('date') . ': ' . $date;
	echo "\r\n\r\n";
	if ($invitation->getInvitationState() == 1)
		echo lang('user will attend to event', $from_user->getDisplayName());
	else if ($invitation->getInvitationState() == 2)
		echo lang('user will not attend to event', $from_user->getDisplayName());
		
?>.<?php echo "\r\n"
?><?php echo "\r\n"
?><?php echo lang('view event') ?>: <?php echo str_replace('&amp;', '&', $event->getViewUrl())
?><?php echo "\r\n"
?><?php echo "\r\n"
?><?php echo "\r\n"
?><?php echo lang('company') ?>: <?php echo owner_company()->getName() ?><?php echo "\r\n"
?><?php echo "\r\n"
?><?php echo lang('workspace') ?>: <?php echo $projectName ?><?php echo "\r\n"
?><?php echo "\r\n"
?><?php echo "\r\n"
?> --<?php echo "\r\n"
?><?php echo ROOT_URL ?><?php echo "\r\n\r\n\r\n"
?> 