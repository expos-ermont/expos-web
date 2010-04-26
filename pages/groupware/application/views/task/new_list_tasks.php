<?php
	require_javascript("og/CSVCombo.js");
	require_javascript("og/DateField.js");
	require_javascript('og/tasks/main.js');
	require_javascript('og/tasks/addTask.js');
	require_javascript('og/tasks/drawing.js');
	require_javascript('og/tasks/TasksTopToolbar.js');
	require_javascript('og/tasks/TasksBottomToolbar.js');
	require_javascript('og/tasks/print.js');
	
	$genid = gen_id();
	
	$all_templates_array = array();
	$project_templates_array = array();
	$templates_array = array();
	$project_templates_array = array();
	$tasks_array = array();
	$internal_milestones_array = array();
	$external_milestones_array = array();
	$users_array = array();
	$companies_array = array();
	
	
	if (isset($all_templates) && !is_null($all_templates))
		foreach($all_templates as $template)
			$all_templates_array[] = $template->getArrayInfo();
	if (isset($project_templates) && !is_null($project_templates))
		foreach($project_templates as $template)
			$project_templates_array[] = $template->getArrayInfo();
	if (isset($tasks))
		foreach($tasks as $task)
			$tasks_array[] = $task->getArrayInfo();
	foreach($internalMilestones as $milestone)
		$internal_milestones_array[] = $milestone->getArrayInfo();
	foreach($externalMilestones as $milestone)
		$external_milestones_array[] = $milestone->getArrayInfo();
	foreach($users as $user)
		$users_array[] = $user->getArrayInfo();
	foreach($companies as $company)
		$companies_array[] = $company->getArrayInfo();
?>
<script>
og.noOfTasks = <?php echo user_config_option('noOfTasks', 8) ?>;
</script>
<div id="taskPanelHiddenFields">
	<input type="hidden" id="hfProjectTemplates" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($project_templates_array)))) ?>"/>
	<input type="hidden" id="hfAllTemplates" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($all_templates_array)))) ?>"/>
	<input type="hidden" id="hfTasks" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($tasks_array)))) ?>"/>
	<input type="hidden" id="hfIMilestones" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($internal_milestones_array)))) ?>"/>
	<input type="hidden" id="hfEMilestones" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($external_milestones_array)))) ?>"/>
	<input type="hidden" id="hfUsers" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($users_array)))) ?>"/>
	<input type="hidden" id="hfCompanies" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($companies_array)))) ?>"/>
	<input type="hidden" id="hfUserPreferences" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($userPreferences)))) ?>"/>
</div>

<div id="tasksPanel" class="ogContentPanel" style="background-color:white;background-color:#F0F0F0;height:100%;width:100%">
	<div id="tasksPanelTopToolbar" class="x-panel-tbar" style="width:100%;height:30px;display:block;background-color:#F0F0F0;"></div>
	<div id="tasksPanelBottomToolbar" class="x-panel-tbar" style="width:100%;height:30px;display:block;background-color:#F0F0F0;border-bottom:1px solid #CCC;"></div>
	<div id="tasksPanelContent" style="background-color:white;padding:7px;padding-top:0px;overflow-y:scroll;">
		
		<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close');
			//$task = ProjectTasks::findOne(array('conditions'=>'created_by_id='.logged_user()->getId()));
			//si no hay task para este usuario muestra ayuda para crear tasks
			if (!isset($task)){
				if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_tasks_context_help', true, logged_user()->getId()))) {?>
					<div id="tasksPanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
						<?php render_context_help($this, 'chelp tasks list', 'tasks', 'tasks'); ?>
					</div>
			<?php }//if
			}//if
			else
			{
				//si ya hay tasks mustra la ayuda de filtros
				if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_tasks_context_help', true, logged_user()->getId()))) {?>
					<div id="tasksPanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
						<?php render_context_help($this, 'chelp tasks filter list','tasks'); ?>
					</div>
			<?php }
			}?>
		
	<?php if (isset($displayTooManyTasks) && $displayTooManyTasks){ ?>
	<div class="tasksPanelWarning ico-warning32" style="font-size:10px;color:#666;background-repeat:no-repeat;padding-left:40px;max-width:920px; margin:20px;border:1px solid #E3AD00;background-color:#FFF690;background-position:4px 4px;">
		<div style="font-weight:bold;width:99%;text-align:center;padding:4px;color:#AF8300;"><?php echo lang('too many tasks to display') ?></div>
	</div>
	<?php } ?>
		<div id="tasksPanelContainer" style="background-color:white;padding:7px;padding-top:0px;">
	<?php if(!(isset($tasks) || $userPreferences['groupBy'] == 'milestone')) { ?>
		<div style="font-size:130%;width:100%;text-align:center;padding-top:10px;color:#777"><?php echo lang('no tasks to display') ?></div>
	<?php } ?>
	</div>
	</div>
</div>



<script type="text/javascript">
	ogTasks.userPreferences = Ext.util.JSON.decode(document.getElementById('hfUserPreferences').value);
	var ogTasksTT = new og.TasksTopToolbar({
		projectTemplatesHfId:'hfProjectTemplates',
		allTemplatesHfId:'hfAllTemplates',
		usersHfId:'hfUsers',
		companiesHfId:'hfCompanies',
		internalMilestonesHfId:'hfIMilestones',
		externalMilestonesHfId:'hfEMilestones',
		renderTo:'tasksPanelTopToolbar'
		});
	var ogTasksBT = new og.TasksBottomToolbar({
		renderTo:'tasksPanelBottomToolbar'
		});

	function resizeTasksPanel(e, id) {
		var tpc = document.getElementById('tasksPanelContent');
		if (tpc) {
			tpc.style.height = (document.getElementById('tasksPanel').clientHeight - 68) + 'px';
		} else {
			og.removeDomEventHandler(window, 'resize', id);
		}
	}
	if (Ext.isIE) {
		og.addDomEventHandler(document.getElementById('tasksPanelContent'), 'resize', resizeTasksPanel);
	} else {
		og.addDomEventHandler(window, 'resize', resizeTasksPanel);
	}
	resizeTasksPanel();
	ogTasks.loadDataFromHF();
<?php if(isset($tasks) || $userPreferences['groupBy'] == 'milestone') {?>
	ogTasks.draw();
<?php } ?>

</script>