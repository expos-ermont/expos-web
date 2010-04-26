<?php
require_javascript("modules/addTaskForm.js");
$task_list = $object;
?>
<script type="text/javascript">
  if(App.modules.addTaskForm) {
    App.modules.addTaskForm.task_lists[<?php echo $task_list->getId() ?>] = {
      id               : <?php echo $task_list->getId() ?>,
	      can_add_task     : <?php echo ($task_list->canAddSubTask(logged_user()) && !$task_list->isTrashed()) ? 'true' : 'false' ?>,
      add_task_link_id : 'addTaskForm<?php echo $task_list->getId() ?>ShowLink',
      task_form_id     : 'addTaskForm<?php echo $task_list->getId() ?>',
      text_id          : 'addTaskText<?php echo $task_list->getId() ?>',
      assign_to_id     : 'addTaskAssignTo<?php echo $task_list->getId() ?>',
      submit_id        : 'addTaskSubmit<?php echo $task_list->getId() ?>'
    };
  } // if
</script>

<?php if ($task_list->getStartDate() instanceof DateTimeValue) { ?>
	<?php if ($task_list->getStartDate()->getYear() > DateTimeValueLib::now()->getYear()) { ?> 
	  <div class="startDate"><b><?php echo lang('start date') ?>: </b><?php echo format_date($task_list->getDueDate(), null, 0) ?></div>
	<?php } else { ?> 
	  <div class="startDate"><b><?php echo lang('start date') ?>: </b><?php echo format_descriptive_date($task_list->getDueDate(), 0) ?></div>
	<?php } // if ?>
<?php } // if ?>

<?php if ($task_list->getDueDate() instanceof DateTimeValue) { ?>
	<?php if ($task_list->getDueDate()->getYear() > DateTimeValueLib::now()->getYear()) { ?> 
	  <div class="dueDate"><b><?php echo lang('due date') ?>: </b><?php echo format_date($task_list->getDueDate(), null, 0) ?></div>
	<?php } else { ?> 
	  <div class="dueDate"><b><?php echo lang('due date') ?>: </b><?php echo format_descriptive_date($task_list->getDueDate(), 0) ?></div>
	<?php } // if ?>
<?php } // if ?>
		<?php 
				$show_help_option = user_config_option('show_context_help', 'until_close');
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_list_task_context_help', true, logged_user()->getId()))) {?>
					<div id="tasksCardContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
						<?php render_context_help($this, 'chelp task card','list_task'); ?>
					</div>
		<?php }?>

<?php if($task_list->getText()) { ?>
  <div style="padding-bottom:20px;color:#333"><table><tr><td style="padding-right:5px;"><b>
  <?php echo lang('description') ?>:</b></td><td style="padding-left:5px;border-left:0px solid #CCC"><?php echo convert_to_links(nl2br(clean($task_list->getText())))?></td></tr></table></div>
<?php } // if ?>


  <table style="border:1px solid #717FA1;width:100%; padding-left:10px;"><tr><th style="padding-left:10px;padding-top:4px;padding-bottom:4px;background-color:#E8EDF7;font-size:120%;font-weight:bolder;color:#717FA1;width:100%;"><?php echo lang("view open tasks") ?></th></tr><tr><td style="padding-left:10px;">
  <div class="openTasks">
<?php if(is_array($task_list->getOpenSubTasks())) { ?>
    <table class="blank">
<?php foreach($task_list->getOpenSubTasks() as $task) { ?>
      <tr>
      
<!-- Checkbox -->
<?php if($task->canChangeStatus(logged_user()) && !$task_list->isTrashed()) { ?>
    <td class="taskCheckbox"><?php echo checkbox_link($task->getCompleteUrl(rawurlencode(get_url('task', 'view_task', array('id' => $task_list->getId())))), false, lang('mark task as completed')) ?></td>
<?php } else { ?>
        <td class="taskCheckbox"><img src="<?php echo icon_url('not-checked.jpg') ?>" alt="<?php echo lang('open task') ?>" /></td>
<?php } // if?>

<!-- Task text and options -->
        <td class="taskText">
<?php if($task->getAssignedTo()) { ?>
          <span class="assignedTo"><?php echo clean($task->getAssignedTo()->getObjectName()) ?>:</span> 
<?php } // if{ ?>
          <a class="internalLink" href="<?php echo $task->getObjectUrl() ?>"><?php echo ($task->getTitle() && $task->getTitle()!='' )?clean($task->getTitle()):clean($task->getText()) ?></a> 
          <?php if($task->canEdit(logged_user()) && !$task->isTrashed()) { ?>
          	<a class="internalLink blank" href="<?php echo $task->getEditListUrl() ?>" title="<?php echo lang('edit task') ?>">
          	<img src="<?php echo icon_url('edit.gif') ?>" alt="" /></a>
          <?php } // if ?>
          <?php if($task->canDelete(logged_user()) && !$task->isTrashed()) { ?>
          	<a class="internalLink blank" href="<?php echo $task->getDeleteUrl() ?>&taskview=true" onclick="return confirm('<?php echo escape_single_quotes(lang('confirm delete task')) ?>')" title="<?php echo lang('delete task') ?>">
          	<img src="<?php echo icon_url('cancel_gray.gif') ?>" alt="" /></a>
          <?php } // if ?>
        </td>
      </tr>
<?php } // foreach ?>
   </table>
<?php } else { ?>
  <?php echo lang('no open task in task list') ?>
<?php } // if ?></div>
  
  
    
  <div class="addTask">
<?php if($task_list->canAddSubTask(logged_user()) && !$task_list->isTrashed()) { ?>
    <div id="addTaskForm<?php echo $task_list->getId() ?>ShowLink"><a class="internalLink" href="<?php echo $task_list->getAddTaskUrl(false) ?>" onclick="App.modules.addTaskForm.showAddTaskForm(<?php echo $task_list->getId() ?>); return false"><?php echo lang('add sub task') ?></a></div>
  
    <div id="addTaskForm<?php echo $task_list->getId() ?>" style="display:none">
      <form class="internalForm" action="<?php echo $task_list->getAddTaskUrl(false) ?>" method="post">
        <div class="taskListAddTaskText">
          <label for="addTaskText<?php echo $task_list->getId() ?>"><?php echo lang('name') ?>:</label>
          <?php echo textarea_field("task[title]", null, array('class' => 'short', 'id' => 'addTaskText' . $task_list->getId())) ?>
        </div>
        <div class="taskListAddTaskAssignedTo">
          <label for="addTaskAssignTo<?php echo $task_list->getId() ?>"><?php echo lang('assign to') ?>:</label>
          <?php echo assign_to_select_box("task[assigned_to]", $task_list->getProject(), null, array('id' => 'addTaskAssignTo' . $task_list->getId())) ?>
        </div>
		<input type="hidden" id="addTaskMilestoneId<?php echo $task_list->getId() ?>" name="task[milestone_id]" value="<?php echo $task_list->getMilestoneId() ?>"/>
		<input type="hidden" id="addTaskProjectId<?php echo $task_list->getId() ?>" name="task[project_id]" value="<?php echo $task_list->getProjectId() ?>"/>
		<input type="hidden" id="addTaskTags<?php echo $task_list->getId() ?>" name="task[tags]" value="<?php echo implode(',',$task_list->getTagNames()) ?>"/>
		<input type="hidden" id="addTaskPriority<?php echo $task_list->getId() ?>" name="task[priority]" value="<?php echo $task_list->getPriority() ?>"/>
		<input type="hidden" id="addTaskInputType<?php echo $task_list->getId() ?>" name="task[inputtype]" value="taskview"/>
        <?php echo submit_button(lang('add sub task'), 's', array('id' => 'addTaskSubmit' . $task_list->getId(), 'fromTaskView' => 'true')) ?> <?php echo lang('or') ?> <a href="#" onclick="App.modules.addTaskForm.hideAddTaskForm(<?php echo $task_list->getId() ?>); return false;"><?php echo lang('cancel') ?></a>
      </form>
    </div>
<?php } else { ?>
	<?php if($on_list_page) { ?>
	<?php echo lang('completed tasks') ?>:
	<?php } else { ?>
	<?php echo lang('recently completed tasks') ?>:
	<?php } // if ?>
<?php } // if ?>
  </div>
</td></tr></table>

<br/>


  
<?php if(is_array($task_list->getCompletedSubTasks())) { ?>
  <table style="border:1px solid #717FA1;width:100%; padding-left:10px;"><tr><th style="padding-left:10px;padding-top:4px;padding-bottom:4px;background-color:#E8EDF7;font-size:120%;font-weight:bolder;color:#717FA1;width:100%;"><?php echo lang("completed tasks") ?></th></tr><tr><td style="padding-left:10px;">
  
  <div class="completedTasks">
    <table class="blank">
<?php $counter = 0; ?>
<?php foreach($task_list->getCompletedSubTasks() as $task) { ?>
<?php $counter++; ?>
<?php if($on_list_page || ($counter <= 5)) { ?>
      <tr>
<?php if($task->canChangeStatus(logged_user()) && !$task->isTrashed()) { ?>
    <td class="taskCheckbox"><?php echo checkbox_link($task->getOpenUrl(rawurlencode(get_url('task', 'view_task', array('id' => $task_list->getId())))), true, lang('mark task as open')) ?></td>
<?php } else { ?>
        <td class="taskCheckbox"><img src="<?php echo icon_url('checked.jpg') ?>" alt="<?php echo lang('completed task') ?>" /></td>
<?php } // if ?>
        <td class="taskText">
        	<a class="internalLink" href="<?php echo $task->getObjectUrl() ?>"><?php echo clean($task->getTitle()) ?></a> 
          <?php if($task->canEdit(logged_user()) && !$task->isTrashed()) { ?>
          	<a class="internalLink" href="<?php echo $task->getEditListUrl() ?>" class="blank" title="<?php echo lang('edit task') ?>">
          	<img src="<?php echo icon_url('edit.gif') ?>" alt="" /></a>
          <?php } // if ?> 
          <?php if($task->canDelete(logged_user()) && !$task->isTrashed()) { ?>
          	<a href="<?php echo $task->getDeleteUrl() ?>" class="blank internalLink" onclick="return confirm('<?php echo escape_single_quotes(lang('confirm delete task')) ?>')" title="<?php echo lang('delete task') ?>">
          	<img src="<?php echo icon_url('cancel_gray.gif') ?>" alt="" /></a>
          <?php } // if ?>
          <br />
          <span class="taskCompletedOnBy">(<?php echo lang('completed on by', format_date($task->getCompletedOn()), $task->getCompletedBy()->getCardUrl(), clean($task->getCompletedBy()->getDisplayName())) ?>)</span>
        </td>
        <td></td>
      </tr>
<?php } // if ?>
<?php } // foreach ?>
<?php if(!$on_list_page && $counter > 5) { ?>
      <tr>
        <td colspan="2"><a class="internalLink" href="<?php echo $task_list->getViewUrl() ?>"><?php echo lang('view all completed tasks', $counter) ?></a></td>
      </tr>
<?php } // if ?>
    </table>
  </div>
</td></tr></table>
<?php } // if ?>


<?php
	$time_estimate = $task_list->getTimeEstimate();
	$total_minutes = $task_list->getTotalMinutes();

	if ($time_estimate > 0 || $total_minutes > 0){?>
<br/>
<table>
<?php if ($time_estimate > 0) {?>
<tr><td>
	<div style="font-weight:bold"><?php echo lang('time estimate'). ':&nbsp;'?></div></td><td> 
		<?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($time_estimate * 60), 'hm', 60) ?></td></tr>
<?php } ?>

<?php if ($total_minutes > 0) {?>
	<tr><td><div style="font-weight:bold"><?php echo lang('total time'). ':&nbsp;' ?></div></td><td>
		<span style="font-size:120%;font-weight:bold;<?php echo ($time_estimate > 0 && $total_minutes > $time_estimate) ? 'color:#FF0000':'' ?>">
			<?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($total_minutes * 60), 'hm', 60) ?>
		</span></td></tr>
<?php } ?>
</table>
<?php } ?>
  