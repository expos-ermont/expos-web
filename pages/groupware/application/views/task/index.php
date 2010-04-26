<div style="display:none">
<!--  this select box will be cloned by the quick-add task forms  -->
<?php echo assign_to_select_box("task[assigned_to]", null, "0:0", array("id" => "og-task-new-assigned-to")) ?>
</div>

<script>
var showNotificationCheck = <?php echo user_config_option('can notify from quick add')?'true':'false' ?>;
function filterTasks() {
	var to = Ext.getDom('og-task-filter-to').value;
	var status = Ext.getDom('og-task-filter-status').value;
	var prio = Ext.getDom('og-task-filter-priority').value;
	og.openLink(og.getUrl('task', 'index', {
		assigned_to: to,
		status: status,
		priority: prio
	}));
}
</script>

<div style="padding:7px">

<table style="width:100%">
	<col width=12/><col width=226/><col width=12/>
	<tr><td class="coViewHeader og-tasks-actions" colspan=2 rowspan=2><div class="coViewPropertiesHeader">
		<div class="og-tasks-actions">
	<table class="og-task-table-filter"><tr>
	<td class="td-buttons">
		<?php $butt_id = gen_id() ?>
		<div id="<?php echo $butt_id ?>" class="og-tasks-buttons">
		</div>
	</td>
	<td class="td-assigned-to"><?php
	 echo lang('show assigned to'). ':&nbsp;';
	 echo filter_assigned_to_select_box("assigned_to",active_project(), $assignedTo, array("id" => "og-task-filter-to", "onchange" => "filterTasks()")); ?></td>
	<?php
		$option = array();
		$attrs1 = array();
		$attrs2 = array();
		if ($status == 'all' || $status == null) {
			$attrs1["selected"] = "selected"; 
		} else {
			$attrs2["selected"] = "selected";
		}
		$option[] = option_tag(lang("all"), "all", $attrs1);
		$option[] = option_tag(lang("pending"), "pending", $attrs2);
		
	?>
	<td class="td-status"><?php 
	echo lang('show by status') . ':&nbsp;';
	echo select_box("status", $option, array("id" => "og-task-filter-status", "onchange" => "filterTasks()")) ?></td>
	<?php
		$option = array();
		$attrs0 = array();
		$attrs1 = array();
		$attrs2 = array();
		$attrs3 = array();
		if (!is_numeric($priority)) {
			$attrs0["selected"] = "selected";
		} else if ($priority <= 100) {
			$attrs1["selected"] = "selected"; 
		} else if ($priority >= 300) {
			$attrs3["selected"] = "selected";
		} else {
			$attrs2["selected"] = "selected";
		}
		$option[] = option_tag(lang("all"), "all", $attrs0);
		$option[] = option_tag(lang("low priority"), 100, $attrs1);
		$option[] = option_tag(lang("normal priority"), 200, $attrs2);
		$option[] = option_tag(lang("high priority"), 300, $attrs3);
		
	?>
	<td class="td-priority"><?php 
	echo lang('show by priority') . ':&nbsp;';
	echo select_box("status", $option, array("id" => "og-task-filter-priority", "onchange" => "filterTasks()")) ?></td>
	</tr>
	</table>
	</div></div></td>
	<td class="coViewTopRight"></td></tr>
		
	<tr><td class="coViewRight" rowspan=2></td></tr>
	<tr><td class="coViewBody" colspan=2>
	
		<div style="og-tasks-container">
		
		</div>
<script>
var butt = new Ext.Button({
	renderTo: '<?php echo $butt_id ?>',
	iconCls: 'ico-new',
	text: lang('new'),
	menu: {
		cls:'scrollable-menu',
		items: [
		{text: lang('new milestone'), iconCls: 'ico-milestone', handler: function() {
			var url = og.getUrl('milestone', 'add');
			og.openLink(url);
		}},
		{text: lang('new task'), iconCls: 'ico-task', handler: function() {
			var url = og.getUrl('task', 'add_task');
			og.openLink(url);
		}}
		<?php
			if (count($milestone_templates) > 0 || count($task_templates) > 0) echo ",'-'";
			foreach ($milestone_templates as $t) {
		?>
				,{text: '<?php echo str_replace("'", "\\'", $t->getName()) ?>',
				iconCls: 'ico-template-milestone',
				handler: function() {
					var url = og.getUrl('milestone', 'copy_milestone', {id: <?php echo $t->getId() ?>});
					og.openLink(url);
				}} 
		<?php
			}
			foreach ($task_templates as $t) {
		?>
				,{text: '<?php echo str_replace("'", "\\'", $t->getTitle()) ?>',
				iconCls: 'ico-template-task',
				handler: function() {
					var url = og.getUrl('task', 'copy_task', {id: <?php echo $t->getId() ?>});
					og.openLink(url);
				}} 
		<?php
			}
		?>
		,'-',{
			disabled: false,
			text: '<?php echo escape_single_quotes(lang('all')) ?>',
			iconCls: 'ico-template-task',
			cls: 'scrollable-menu',
			menu:{items: [
				<?php 
					$initial = true;
					foreach ($all_task_templates as $t) {	
						if($initial) $initial = false;
						else echo ',';
						?>
						{text: '<?php echo str_replace("'", "\\'", $t->getTitle()) ?>',
						iconCls: 'ico-template-task',
						handler: function() {
							var url = og.getUrl('task', 'copy_task', {id: <?php echo $t->getId() ?>});
							og.openLink(url);
						}} 
				<?php 
					}?>
			]}
		}
	]}
});
</script>
		
		<div style="padding:7px;">
		<div class="tpMilestoneHeader"><?php echo lang('milestones') ?></div>
		
		<div id="og-milestones" class="og-milestones">
		<?php include "view_milestones.php"; ?>
		</div>
		
		<div class="tpTaskHeader"><?php echo lang('tasks') ?> &nbsp;	<a class="coViewAction ico-print" title="<?php echo lang('print')?>" target="_blank"	
			href="<?php echo get_url('task','print_view_all', array(
					'active_project'=>active_project()?active_project()->getId():'',					
					'assigned_to' => array_var($_GET, 'assigned_to', ''),
					'status' => array_var($_GET, 'status', "pending"),
					'priority' => array_var($_GET, 'priority', "all")
				  )) ?>">
	  <?php //echo lang('print')?> </a>
	  </div>
		
		<div id="og-sub-tasks-0" class="og-tasks">
		<?php include "view_tasks.php"; ?>
		</div>
		</div>
	</td></tr>
	
	<tr><td class="coViewBottomLeft"></td>
	<td class="coViewBottom"></td>
	<td class="coViewBottomRight"></td></tr>
	</table>
	
</div>
