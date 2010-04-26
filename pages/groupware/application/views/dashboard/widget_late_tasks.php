<?php 
	$show_help_option = user_config_option('show_context_help', 'until_close'); 
	if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_late_tasks_widget_context_help', true, logged_user()->getId()))) { 
		render_context_help($this, 'chelp late tasks widget', 'late_tasks_widget');
	} // if ?>

<div style="padding:10px">
		
<?php if($hasLate) { 
	$c = 0;
	?>
<div>
  <table style="width:100%">
<?php
	if (isset($late_milestones) && is_array($late_milestones) && count($late_milestones))
	foreach($late_milestones as $milestone) { 
	$c++;
	?>
    <tr class="<?php echo $c % 2 == 1? '':'dashAltRow' ?>"><td class="db-ico ico-milestone"></td>
    <td style="padding-left:5px;padding-bottom:2px">
    <?php $dws = $milestone->getWorkspaces(logged_user()->getActiveProjectIdsCSV());
		$projectLinks = array();
		foreach ($dws as $ws) {
			$projectLinks[] = $ws->getId();
		}
		echo '<span class="project-replace">' . implode(',',$projectLinks) . '</span>';?>
    <a class="internalLink" href="<?php echo $milestone->getViewUrl() ?>">
<?php if($milestone->getAssignedTo() instanceof ApplicationDataObject) { ?>
    <span style="font-weight:bold"> <?php echo clean($milestone->getAssignedTo()->getObjectName()) ?>: </span><?php echo clean($milestone->getName()) ?>
<?php } else { ?>
    <?php echo clean($milestone->getName()) ?>
<?php } // if ?>
	</a></td>
    <td style="text-align:right"><?php echo lang('days late', $milestone->getLateInDays()) ?></td>
	</tr>
<?php } // foreach ?>
<?php
	if (isset($late_tasks) && is_array($late_tasks) && count($late_tasks))
	foreach($late_tasks as $task) { 
	$c++;
	?>
    <tr class="<?php echo $c % 2 == 1? '':'dashAltRow' ?>"><td class="db-ico ico-task"></td>
    <td style="padding-left:5px;padding-bottom:2px">
    <?php 
		$dws = $task->getWorkspaces(logged_user()->getActiveProjectIdsCSV());
		$projectLinks = array();
		foreach ($dws as $ws) {
			$projectLinks[] = $ws->getId();
		}
		echo '<span class="project-replace">' . implode(',',$projectLinks) . '</span>';?>
	<a class="internalLink" href="<?php echo $task->getViewUrl() ?>">
<?php if($task->getAssignedTo() instanceof ApplicationDataObject) { ?>
    <span style="font-weight:bold"> <?php echo clean($task->getAssignedTo()->getObjectName()) ?>: </span><?php echo clean($task->getTitle()) ?>
<?php } else { ?>
    <?php echo clean($task->getTitle()) ?>
<?php } // if ?>
	</a></td>
    <td style="text-align:right"><?php echo lang('days late', $task->getLateInDays()) ?></td>
	</tr>
<?php } // foreach ?>
  </table></div>
<?php } // if ?>

<?php if($hasToday) { 
	$c = 1; ?>
  <div class="dashSubtitle" style="<?php echo $hasLate ? '': 'padding-top:0px' ?>"><?php echo lang('today') ?></div>
  <div>
  <table style="width:100%">
<?php 
	if (isset($today_milestones) && is_array($today_milestones) && count($today_milestones))
	foreach($today_milestones as $milestone) { 
	$c++;?>
    <tr class="<?php echo $c % 2 == 1? '':'dashAltRow' ?>"><td class="db-ico ico-milestone"></td>
    <td style="padding-left:5px;padding-bottom:2px">
    <?php 
		$dws = $milestone->getWorkspaces(logged_user()->getActiveProjectIdsCSV());
		$projectLinks = array();
		foreach ($dws as $ws) {
			$projectLinks[] = $ws->getId();
		}
		echo '<span class="project-replace">' . implode(',',$projectLinks) . '</span>';?>
    <a class="internalLink" href="<?php echo $milestone->getViewUrl() ?>">
<?php if($milestone->getAssignedTo() instanceof ApplicationDataObject) { ?>
    <span style="font-weight:bold"> <?php echo clean($milestone->getAssignedTo()->getObjectName()) ?>: </span><?php echo clean($milestone->getName()) ?>
<?php } else { ?>
    <?php echo clean($milestone->getName()) ?>
<?php } // if ?>
	</a></td></tr>
<?php } // foreach ?>
<?php 
	if (isset($today_tasks) && is_array($today_tasks) && count($today_tasks))
	foreach($today_tasks as $task) { 
	$c++;?>
    <tr class="<?php echo $c % 2 == 1? '':'dashAltRow' ?>"><td class="db-ico ico-task"></td>
    <td style="padding-left:5px;padding-bottom:2px">
    <?php 
		$dws = $task->getWorkspaces(logged_user()->getActiveProjectIdsCSV());
		$projectLinks = array();
		foreach ($dws as $ws) {
			$projectLinks[] =$ws->getId();
		}
		echo  '<span class="project-replace">' . implode(',',$projectLinks) . '</span>';?>
	<a class="internalLink" href="<?php echo $task->getViewUrl() ?>">
<?php if($task->getAssignedTo() instanceof ApplicationDataObject) { ?>
    <span style="font-weight:bold"> <?php echo clean($task->getAssignedTo()->getObjectName()) ?>: </span><?php echo clean($task->getTitle()) ?>
<?php } else { ?>
    <?php echo clean($task->getTitle()) ?>
<?php } // if ?>
	</a></td></tr>
<?php } // foreach ?>
  </table></div>
<?php } // if ?>
</div>