<?php
	require_javascript('modules/addMessageForm.js');
	require_javascript('og/tasks/main.js');
	require_javascript('og/tasks/addTask.js');
	$genid = gen_id();
	$object = $task;
	if ($task->isNew()) {
		$project = Projects::findById(array_var($task_data, 'project_id'));
	} else {
		$project = $task->getProject();
	}
?>

<form style='height:100%;background-color:white' class="internalForm" action="<?php echo $task->isNew() ? get_url('task', 'add_task', array("copyId" => array_var($task_data, 'copyId'))) : $task->getEditListUrl() ?>" method="post">

<div class="task">
<div class="coInputHeader">
	<div class="coInputHeaderUpperRow">
	<div class="coInputTitle"><table style="width:535px">
	<tr><td><?php
		if ($task->isNew()) {
			if (array_var($task_data, 'is_template', false)) {
				echo lang('new task template');
			} else if (isset($base_task) && $base_task instanceof ProjectTask) {
				echo lang('new task from template');
			} else {
				echo lang('new task list');
			}
		} else if ($task->getIsTemplate()) {
			echo lang('edit task template');
		} else {
			echo lang('edit task list');
		}
	?>
	</td><td style="text-align:right"><?php echo submit_button($task->isNew() ? (array_var($task_data, 'is_template', false) ? lang('save template') : lang('add task list')) : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px', 'tabindex' => '10')) ?></td></tr></table>
	</div>
	
	</div>
	<div>
		<?php echo label_tag(lang('name'), $genid . 'taskListFormName', true) ?>
    	<?php echo text_field('task[title]', array_var($task_data, 'title'), 
    		array('class' => 'title', 'id' => $genid . 'taskListFormName', 'tabindex' => '1')) ?>
    </div>
	
	<?php $categories = array(); Hook::fire('object_edit_categories', $object, $categories); ?>
	
	<div style="padding-top:5px">
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_task_select_workspace_div', this)"><?php echo lang('workspace') ?></a> - 
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_task_tags_div', this)"><?php echo lang('tags') ?></a> - 
		<a href="#" class="option" style="font-weight:bold" onclick="og.toggleAndBolden('<?php echo $genid ?>add_task_more_div', this)"><?php echo lang('task data') ?></a> -  
		<?php /*<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_task_handins_div', this)"><?php echo lang('handins') ?></a> - */ ?>
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_reminders_div',this)"><?php echo lang('object reminders') ?></a>  -
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_custom_properties_div', this)"><?php echo lang('custom properties') ?></a> -
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_subscribers_div',this)"><?php echo lang('object subscribers') ?></a>
		<?php if($object->isNew() || $object->canLinkObject(logged_user(), $project)) { ?> - 
			<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_linked_objects_div',this)"><?php echo lang('linked objects') ?></a>
		<?php } ?>
		<?php foreach ($categories as $category) { ?>
			- <a href="#" class="option" <?php if ($category['visible']) echo 'style="font-weight: bold"'; ?> onclick="og.toggleAndBolden('<?php echo $genid . $category['name'] ?>', this)"><?php echo lang($category['name'])?></a>
		<?php } ?>
	</div>
</div>
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">

		<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_task_context_help', true, logged_user()->getId()))) {?>
			<div id="tasksPanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add new task','add_task'); ?>
			</div>
		<?php }?>
		

	<div id="<?php echo $genid ?>add_task_select_workspace_div" style="display:none">
	<fieldset>
	 	<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_task_workspace_context_help', true, logged_user()->getId()))) {?>
			<div id="tasksPanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add new task workspace','add_task_workspace'); ?>
			</div>
		<?php }?>
	<legend><?php echo lang('workspace') ?></legend>
		<?php echo select_project2('task[project_id]', $project->getId(), $genid) ?>
	</fieldset>
	</div>

	<div id="<?php echo $genid ?>add_task_tags_div" style="display:none">
	<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_task_tags_context_help', true, logged_user()->getId()))) {?>
			<div id="tasksPanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add new task tag','add_task_tags'); ?>
			</div>
		<?php }?>
	<legend><?php echo lang('tags') ?></legend>
		<?php echo autocomplete_tags_field("task[tags]", array_var($task_data, 'tags'), null, 30); ?>
	</fieldset>
	</div>

	
	<div id="<?php echo $genid ?>add_task_more_div" style="display:block">
  	<fieldset>
	<legend><?php echo lang('task data') ?></legend>
    
	    <label><?php echo lang('milestone') ?>: <span class="desc">(<?php echo lang('assign milestone task list desc') ?>)</span></label>
    	<?php echo select_milestone('task[milestone_id]', null, array_var($task_data, 'milestone_id'), array('id' => $genid . 'taskListFormMilestone', 'tabindex' => '40')) ?>
    	
    	<div style="padding-top:4px">
    		<?php echo label_tag(lang('parent task'), $genid . 'addTaskTaskList') ?>
			<?php echo select_task_list('task[parent_id]', $project, array_var($task_data, 'parent_id'), false, array('id' => $genid . 'addTaskTaskList', 'tabindex' => '50')) ?>
    	</div>
    	
    	<div style="padding-top:4px">	
    	<?php /*echo label_tag(lang('dates'))*/ ?>
    	<table><tbody><tr><td style="padding-right: 10px">
    	<?php echo label_tag(lang('start date')) ?>
    	</td><td>
		<?php echo pick_date_widget2('task_start_date', array_var($task_data, 'start_date'),$genid, 60) ?>
		</td></tr><tr><td style="padding-right: 10px">
		<?php echo label_tag(lang('due date')) ?>
    	</td><td>
		<?php echo pick_date_widget2('task_due_date', array_var($task_data, 'due_date'),$genid, 70) ?>
		</td></tr></tbody></table>
		</div>
		
		<div id='<?php echo $genid ?>add_task_time_div' style="padding-top:6px">
		<?php echo label_tag(lang('time estimate')) ?>
      	<?php $totalTime = array_var($task_data, 'time_estimate', 0); 
      		$minutes = $totalTime % 60;
			$hours = ($totalTime - $minutes) / 60;
      		?>
      		<table>
		<tr>
			<td align="right"><?php echo lang("hours") ?>:&nbsp;</td>
			<td align='left'><?php echo text_field("task[time_estimate_hours]", $hours, array('style' => 'width:30px', 'tabindex' => '80')) ?></td>
			<td align="right" style="padding-left:10px"><?php echo lang("minutes") ?>:&nbsp;</td>
			<td align='left'><select name="task[time_estimate_minutes]" size="1" tabindex="85">
			<?php
				$minutes = ($totalTime % 60);
				$minuteOptions = array(0,5,10,15,20,30,45);
				for($i = 0; $i < 7; $i++) {
					echo "<option value=\"" . $minuteOptions[$i] . "\"";
					if($minutes == $minuteOptions[$i]) echo ' selected="selected"';
					echo ">" . $minuteOptions[$i] . "</option>\n";
				}
			?></select>
			</td>
		</tr></table>
 	</div>
		
		<div style="padding-top:4px">
		<?php echo label_tag(lang('task priority')) ?>
		<?php echo select_task_priority('task[priority]', array_var($task_data, 'priority', ProjectTasks::PRIORITY_NORMAL), array('tabindex' => '90')) ?>
		</div>
  	</fieldset>
  	</div>

<?php if($task->isNew()) { ?>
	<?php if (isset($base_task) && $base_task instanceof ProjectTask && $base_task->getIsTemplate()) { ?>
		<input type="hidden" name="task[from_template_id]" value="<?php echo $base_task->getId() ?>" />
	<?php } ?>
<?php } // if ?>
  
	<?php /*<div style="display:none" id="<?php echo $genid ?>add_task_handins_div">
	<fieldset>
    <legend><?php echo lang('handins') ?></legend>
	  <table class="blank">
	    <tr>
	      <th><?php echo lang('handin') ?>:</th>
	      <th><?php echo lang('assign to') ?>:</th>
	    </tr>
		<?php for($i = 0; $i < 4; $i++) { ?>
	    <tr style="background: <?php echo $i % 2 ? '#fff' : '#e8e8e8' ?>">
	      <td>      
	        <?php 
	        if($task->isNew())
	        	$attr =  array('class' => 'long');
	        else 
	        	 $attr = array('class' => 'long', 'disabled' => 'true');
	        echo text_field("task[handin$i][title]", array_var($task_data["handin$i"], 'title'), $attr) ?>
	      </td>
	      <td>
	        <?php echo assign_to_select_box("task[handin$i][assigned_to]", active_or_personal_project(), array_var($task_data["handin$i"], 'assigned_to'), $task->isNew()?null:array( 'disabled' => 'true')) ?>
	      </td>
	    </tr>
		<?php } // for ?>
	  </table>
	</fieldset>
  	</div> */ ?>
  
	<div id="<?php echo $genid ?>add_reminders_div" style="display:none">
		<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_task_reminders_context_help', true, logged_user()->getId()))) {?>
			<div id="tasksPanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add new task reminders','add_task_reminders'); ?>
			</div>
		<?php }?>
		<legend><?php echo lang('object reminders') ?></legend>
		<label><?php echo lang("due date")?>:</label>
		<div id="<?php echo $genid ?>add_reminders_content">
			<?php echo render_add_reminders($object, 'due_date', array(
				'type' => 'reminder_email',
				'duration' => 1,
				'duration_type' => 1440
			)); ?>
		</div>
		</fieldset>
	</div>
	
	<div id='<?php echo $genid ?>add_custom_properties_div' style="display:none">
	<fieldset>
	<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_task_custom_properties_context_help', true, logged_user()->getId()))) {?>
			<div id="tasksPanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add new task custom properties','add_task_custom_properties'); ?>
			</div>
		<?php }?>
    <legend><?php echo lang('custom properties') ?></legend>
      <?php echo render_object_custom_properties($object, 'ProjectTasks', false) ?><br/><br/>
      <?php echo render_add_custom_properties($object); ?>
  	</fieldset>
 	</div>
  
    <div id="<?php echo $genid ?>add_subscribers_div" style="display:none">
		<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_task_subscribers_context_help', true, logged_user()->getId()))) {?>
			<div id="tasksPanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add new task subscribers','add_task_subscribers'); ?>
			</div>
		<?php }?>
		<legend><?php echo lang('object subscribers') ?></legend>
		<div id="<?php echo $genid ?>add_subscribers_content">
			<?php echo render_add_subscribers($object, $genid); ?>
		</div>
		</fieldset>
	</div>
	
	<script>
	var wsTree = Ext.get('<?php echo $genid ?>wsSel');
	wsTree.previousValue = <?php echo $project->getId() ?>;
	wsTree.on("click", function(ws) {
		var uids = App.modules.addMessageForm.getCheckedUsers('<?php echo $genid ?>');
		var wsid = Ext.get('<?php echo $genid ?>wsSelValue').getValue();
		if (wsid != this.previousValue) {
			this.previousValue = wsid;
			Ext.get('<?php echo $genid ?>add_subscribers_content').load({
				url: og.getUrl('object', 'render_add_subscribers', {
					workspaces: wsid,
					users: uids,
					genid: '<?php echo $genid ?>',
					object_type: '<?php echo get_class($object->manager()) ?>'
				}),
				scripts: true
			});
		}
	}, wsTree);
	</script>

	<?php if($object->isNew() || $object->canLinkObject(logged_user(), $project)) { ?>
	<div style="display:none" id="<?php echo $genid ?>add_linked_objects_div">
	<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_task_linked_objects_context_help', true, logged_user()->getId()))) {?>
			<div id="tasksPanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add new task linked object','add_task_linked_objects'); ?>
			</div>
		<?php }?>
		<legend><?php echo lang('linked objects') ?></legend>
		<?php echo render_object_link_form($object) ?>
	</fieldset>	
	</div>
	<?php } // if ?>
		
	<div><?php echo label_tag(lang('description'), $genid . 'taskListFormDescription') ?>
	<?php echo textarea_field('task[text]', array_var($task_data, 'text'), array('class' => 'long', 'id' => $genid . 'taskListFormDescription', 'tabindex' => '140')) ?>
	</div>

	<?php foreach ($categories as $category) { ?>
	<div <?php if (!$category['visible']) echo 'style="display:none"' ?> id="<?php echo $genid . $category['name'] ?>">
	<fieldset>
		<legend><?php echo lang($category['name'])?><?php if ($category['required']) echo ' <span class="label_required">*</span>'; ?></legend>
		<?php echo $category['content'] ?>
	</fieldset>
	</div>
	<?php } ?>

	<div>
		<?php $defaultNotifyValue = user_config_option('can notify from quick add'); ?>
		<label><?php echo lang('assign to') ?>:</label> 
		<table><tr><td>
			<input type="hidden" id="<?php echo $genid ?>taskFormAssignedTo" name="task[assigned_to]"></input>
			<div id="<?php echo $genid ?>assignto_div">
				<div id="<?php echo $genid ?>assignto_container_div"></div>
			</div>
		</td><td style="padding-left:10px"><div  id="<?php echo $genid ?>taskFormSendNotificationDiv" style="display:none">
			<?php echo checkbox_field('task[send_notification]', array_var($task_data, 'send_notification'), array('id' => $genid . 'taskFormSendNotification')) ?>
			<label for="<?php echo $genid ?>taskFormSendNotification" class="checkbox"><?php echo lang('send task assigned to notification') ?></label>
		</div>
		</td></tr></table>
		
	</div>
	
	<div>
		<?php echo render_object_custom_properties($object, 'ProjectTasks', true) ?>
	</div><br/>
	<?php echo input_field("task[is_template]", array_var($task_data, 'is_template', false), array("type" => "hidden", 'tabindex' => '160')); ?>
  <?php echo submit_button($task->isNew() ? (array_var($task_data, 'is_template', false) ? lang('save template') : lang('add task list')) : lang('save changes'), 's', array('tabindex' => '170')) ?>
</div>
</div>
</form>

<script type="text/javascript">
	var wsSelector = Ext.get('<?php echo $genid ?>wsSel');
	var prevWsValue = -1;
	var assigned_user = '<?php echo array_var($task_data, 'assigned_to', 0) ?>';
	
	og.drawNotificationsInnerHtml = function(companies) {
		var htmlStr = '';
		htmlStr += '<div id="<?php echo $genid ?>notify_companies"></div>';
		htmlStr += '<script type="text/javascript">';
		htmlStr += 'var div = Ext.getDom(\'<?php echo $genid ?>notify_companies\');';
		htmlStr += 'div.notify_companies = {};';
		htmlStr += 'var cos = div.notify_companies;';
		htmlStr += '<\/script>';
		if (companies != null) {
			for (i = 0; i < companies.length; i++) {
				comp_id = companies[i].id;
				comp_name = companies[i].name;
				htmlStr += '<script type="text/javascript">';
				htmlStr += 'cos.company_' + comp_id + ' = {id:\'<?php echo $genid ?>notifyCompany' + comp_id + '\', checkbox_id : \'notifyCompany' + comp_id + '\',users : []};';
				htmlStr += '\<\/script>';
					
				htmlStr += '<div class="companyDetails">';
				htmlStr += '<div class="companyName">';
				
				htmlStr += '<input type="checkbox" class="checkbox" name="task[notify_company_'+comp_id+']" id="<?php echo $genid ?>notifyCompany'+comp_id+'" onclick="App.modules.addMessageForm.emailNotifyClickCompany('+comp_id+',\'<?php echo $genid ?>\',\'notify_companies\', \'notification\')"></input>'; 
				htmlStr += '<label for="<?php echo $genid ?>notifyCompany'+comp_id+'" class="checkbox">'+og.clean(comp_name)+'</label>';
				
				htmlStr += '</div>';
				htmlStr += '<div class="companyMembers">';
				htmlStr += '<ul>';
				
				for (j = 0; j < companies[i].users.length; j++) {
					usr = companies[i].users[j];
					htmlStr += '<li><input type="checkbox" class="checkbox" name="task[notify_user_'+usr.id+']" id="<?php echo $genid ?>notifyUser'+usr.id+'" onclick="App.modules.addMessageForm.emailNotifyClickUser('+comp_id+','+usr.id+',\'<?php echo $genid ?>\',\'notify_companies\', \'notification\')"></input>'; 
					htmlStr += '<label for="<?php echo $genid ?>notifyUser'+usr.id+'" class="checkbox">'+og.clean(usr.name)+'</label>';
					htmlStr += '<script type="text/javascript">';
					htmlStr += 'cos.company_' + comp_id + '.users.push({ id:'+usr.id+', checkbox_id : \'notifyUser' + usr.id + '\'});';
					htmlStr += '\<\/script></li>';
				}
				htmlStr += '</ul>';
				htmlStr += '</div>';
				htmlStr += '</div>';
			}
		}
		return htmlStr;
	}
	
	og.drawAssignedToSelectBox = function(companies) {
		usersStore = ogTasks.buildAssignedToComboStore(companies);
		var assignCombo = new Ext.form.ComboBox({
			renderTo:'<?php echo $genid ?>assignto_container_div',
			name: 'taskFormAssignedToCombo',
			id: '<?php echo $genid ?>taskFormAssignedToCombo',
			value: assigned_user,
			store: usersStore,
			displayField:'text',
	        typeAhead: true,
	        mode: 'local',
	        triggerAction: 'all',
	        selectOnFocus:true,
	        width:160,
	        tabIndex: '150',
	        valueField: 'value',
	        emptyText: (lang('select user or group') + '...'),
	        valueNotFoundText: ''
		});
		assignCombo.on('select', og.onAssignToComboSelect);

		assignedto = document.getElementById('<?php echo $genid ?>taskFormAssignedTo');
		if (assignedto){
			assignedto.value = assigned_user;
			og.addTaskUserChanged('<?php echo $genid ?>', '<?php echo logged_user()->getId() ?>');
		}
	}
	
	og.onAssignToComboSelect = function() {
		combo = Ext.getCmp('<?php echo $genid ?>taskFormAssignedToCombo');
		assignedto = document.getElementById('<?php echo $genid ?>taskFormAssignedTo');
		if (assignedto) assignedto.value = combo.getValue();
		assigned_user = combo.getValue();
		
		og.addTaskUserChanged('<?php echo $genid ?>', '<?php echo logged_user()->getId() ?>');
	}

	og.addTaskUserChanged = function(genid, logged_user_id){
		var ddUser = document.getElementById(genid + 'taskFormAssignedTo');
		var chk = document.getElementById(genid + 'taskFormSendNotification');
		if (ddUser && chk){
			var values = ddUser.value.split(':');
			var user = values[1];
			var nV = <?php echo $defaultNotifyValue?>;
			chk.checked = (user > 0 && nV != 0 && user != logged_user_id);
			document.getElementById(genid + 'taskFormSendNotificationDiv').style.display = user > 0 ? 'block':'none';
		}
	}
	
	og.drawUserLists = function(success, data) {
		companies = data.companies;
	
		var assign_div = Ext.get('<?php echo $genid ?>assignto_container_div');
		if (assign_div != null) {
			assign_div.remove();
			assign_div = Ext.get('<?php echo $genid ?>assignto_div');
			if (assign_div != null) {
				assign_div.insertHtml('beforeEnd', '<div id="<?php echo $genid ?>assignto_container_div"></div>');
				og.drawAssignedToSelectBox(companies);
			}
		}

		var inv_div = Ext.get('<?php echo $genid ?>inv_companies_div');
		if (inv_div != null) inv_div.remove();
		inv_div = Ext.get('emailNotification');

		if (inv_div != null) {
			inv_div.insertHtml('beforeEnd', '<div id="<?php echo $genid ?>inv_companies_div">' + og.drawNotificationsInnerHtml(companies) + '</div>');	
			inv_div.repaint();
		}
	}
	
	og.redrawUserLists = function(){
		var wsVal = Ext.get('<?php echo $genid ?>wsSelValue').getValue();
		
		if (wsVal != prevWsValue) {
			og.openLink(og.getUrl('task', 'allowed_users_to_assign', {ws_id:wsVal}), {callback:og.drawUserLists});
			prevWsValue = wsVal;
		}
	}
	wsSelector.addListener('click', og.redrawUserLists);
	og.redrawUserLists();

	Ext.get('<?php echo $genid ?>taskListFormName').focus();
</script>