<?php
require_javascript('modules/addMessageForm.js'); 
?>
<script type="text/javascript">

	function cal_hide(id) {
		document.getElementById(id).style.display = "none";
	}

	function cal_show(id) {
		document.getElementById(id).style.display = "block";
	}
	
	function toggleDiv(div_id){
		var theDiv = document.getElementById(div_id);
		dis = !theDiv.disabled;
	    var theFields = theDiv.getElementsByTagName('*');
	    for (var i=0; i < theFields.length;i++) theFields[i].disabled=dis;
	    theDiv.disabled=dis;
	}
	
	function changeRepeat() {
		cal_hide("cal_extra1");
		cal_hide("cal_extra2");
		cal_hide("cal_extra3");
		if(document.getElementById("daily").selected){
			document.getElementById("word").innerHTML = '<?php echo escape_single_quotes(lang("days"))?>';
			cal_show("cal_extra1");
			cal_show("cal_extra2");
		} else if(document.getElementById("weekly").selected){
			document.getElementById("word").innerHTML =  '<?php echo escape_single_quotes(lang("weeks"))?>';
			cal_show("cal_extra1");
			cal_show("cal_extra2");
		} else if(document.getElementById("monthly").selected){
			document.getElementById("word").innerHTML =  '<?php echo escape_single_quotes(lang("months"))?>';
			cal_show("cal_extra1");
			cal_show("cal_extra2");
		} else if(document.getElementById("yearly").selected){
			document.getElementById("word").innerHTML =  '<?php echo escape_single_quotes(lang("years"))?>';
			cal_show("cal_extra1");
			cal_show("cal_extra2");
		} else if(document.getElementById("holiday").selected){
			cal_show("cal_extra3");
		}
	}
</script>


<?php
/*
	Copyright (c) Reece Pegues
	sitetheory.com

    Reece PHP Calendar is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or 
	any later version if you wish.

    You should have received a copy of the GNU General Public License
    along with this file; if not, write to the Free Software
    Foundation Inc, 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
$genid = gen_id();
$object = $event;

$active_projects = logged_user()->getActiveProjects();
if ($event->isNew()) {
	$project = active_or_personal_project();
} else {
	$project = $event->getProject();
}

$day =  array_var($event_data, 'day');
$month =  array_var($event_data, 'month');
$year =  array_var($event_data, 'year');

$filter_user = isset($_GET['user_id']) ? $_GET['user_id'] : logged_user()->getId();

$use_24_hours = user_config_option('time_format_use_24');

	// get dates
	$setlastweek='';
	$pm = 0;
	if($event->isNew()) { 
			
		
		$username = '';
		$desc = '';
		
		// if adding event to today, make the time current time.  Else just make it 6PM (you can change that)
		if( "$year-$month-$day" == date("Y-m-d") ) $hour = date('G') + 1;
		else $hour = 18;
		// organize time by 24-hour or 12-hour clock.
		$pm = 0;
		if(!$use_24_hours) {
			if($hour >= 12) {
				$hour = $hour - 12;
				$pm = 1;
			}
		}
		// set default minute and duration times.
		$minute = 0;
		$durhr = 1;
		$durday = 0;
		$durmin = 0;
		// set other defaults
		$rjump = 1;
		// set type of event to default of 1 (nothing)
		$typeofevent = 1;
	}
	?>

	<?php if($event->isNew()) { ?>
	<form style="height:100%;background-color:white" class="internalForm" action="<?php echo get_url('event', 'add')."&view=". array_var($_GET, 'view','month'); ?>" method="post">
	<?php } else { ?>
	<form style="height:100%;background-color:white" class="internalForm" action="<?php echo $event->getEditUrl()."&view=". array_var($_GET, 'view','month'); ?>" method="post">
	<?php } // if ?>

	<input type="hidden" id="event[pm]" name="event[pm]" value="<?php echo $pm?>">
	<div class="event">	
	<div class="coInputHeader">
		<div class="coInputHeaderUpperRow">
			<div class="coInputTitle">
				<table style="width:535px">
				<tr>
					<td>
					<?php echo $event->isNew() ? lang('new event') : lang('edit event') ?></td>
					<td style="text-align:right">
						<?php echo submit_button($event->isNew() ? lang('add event') : lang('save changes'),'e',array('style'=>'margin-top:0px;margin-left:10px', 'tabindex' => 200))?>
					</td>
				</tr>
				</table>
			</div>		
		</div>
		<div style="text-align:left;"><?php echo label_tag(lang('subject'), 'taskListFormName', true) . text_field('event[subject]', array_var($event_data, 'subject'), 
	    		array('class' => 'title', 'id' => 'eventSubject', 'tabindex' => '1', 'maxlength' => '100', 'tabindex' => '10')) ?>
	    </div>
	 
	 	<?php $categories = array(); Hook::fire('object_edit_categories', $object, $categories); ?>
	 	
	 	<div style="padding-top:5px;text-align:left;">
		<a href='#' class='option' onclick="og.ToggleTrap('trap1', 'fs1');og.toggleAndBolden('<?php echo $genid ?>add_event_select_workspace_div', this)"><?php echo lang('workspace')?></a> - 
		<a href='#' class='option' onclick="og.ToggleTrap('trap2', 'fs2');og.toggleAndBolden('<?php echo $genid ?>add_event_tags_div', this)"><?php echo lang('tags')?></a> - 
		<a href='#' class='option' onclick="og.ToggleTrap('trap3', 'fs3');og.toggleAndBolden('<?php echo $genid ?>add_event_description_div', this)"><?php echo lang('description')?></a> - 
		<a href='#' class='option' onclick="og.ToggleTrap('trap4', 'fs4');og.toggleAndBolden('<?php echo $genid ?>event_repeat_options_div', this)"><?php echo lang('CAL_REPEATING_EVENT')?></a> -
		<a href='#' class='option' onclick="og.ToggleTrap('trap5', 'fs5');og.toggleAndBolden('<?php echo $genid ?>add_reminders_div', this)"><?php echo lang('object reminders')?></a> - 
		<a href='#' class='option' onclick="og.ToggleTrap('trap6', 'fs6');og.toggleAndBolden('<?php echo $genid ?>add_custom_properties_div', this)"><?php echo lang('custom properties')?></a> - 
		<a href="#" class="option" onclick="og.ToggleTrap('trap7', 'fs7');og.toggleAndBolden('<?php echo $genid ?>add_subscribers_div',this)"><?php echo lang('object subscribers') ?></a>
		<?php if($object->isNew() || $object->canLinkObject(logged_user(), $project)) { ?> - 
			<a href="#" class="option" onclick="og.ToggleTrap('trap8', 'fs8');og.toggleAndBolden('<?php echo $genid ?>add_linked_objects_div',this)"><?php echo lang('linked objects') ?></a>
		<?php } ?> -
		<a href="#" class="option" onclick="og.ToggleTrap('trap9', 'fs9');og.toggleAndBolden('<?php echo $genid ?>add_event_invitation_div', this);"><?php echo lang('event invitations') ?></a>
		<?php foreach ($categories as $category) { ?>
			- <a href="#" class="option" <?php if ($category['visible']) echo 'style="font-weight: bold"'; ?> onclick="og.toggleAndBolden('<?php echo $genid . $category['name'] ?>', this)"><?php echo lang($category['name'])?></a>
		<?php } ?>
		</div>
		</div>
	
		<div class="coInputSeparator"></div>
		<div class="coInputMainBlock">	
		
		<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_add_event_context_help', true, logged_user()->getId())) {?>
			<div id="addEventPanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add event','add_event'); ?>
			</div>
		<?php }?>
		
		<div id="<?php echo $genid ?>add_event_select_workspace_div" style="display:none">
		<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_add_event_workspace_context_help', true, logged_user()->getId())) {?>
			<div id="addEventPanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add event workspace','add_event_workspace'); ?>
			</div>
		<?php }?>
		<legend><?php echo lang('workspace') ?></legend>
			<?php echo select_project2('event[project_id]', $project->getId(), $genid) ?>
		</fieldset>
		</div>
		<div id="trap1"><fieldset id="fs1" style="height:0px;border:0px;padding:0px;display:none"><span style="color:#FFFFFF;"></span></fieldset></div>
		
		<div id="<?php echo $genid ?>add_event_tags_div" style="display:none">
		<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_add_event_tag_context_help', true, logged_user()->getId())) {?>
			<div id="addEventPanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add event tag','add_event_tag'); ?>
			</div>
		<?php }?>
			<legend><?php echo lang('tags')?></legend>
			<?php echo autocomplete_tags_field("event[tags]", array_var($event_data, 'tags'), "event[tags]", 20); ?>
		</fieldset>
		</div>
		<div id="trap2"><fieldset id="fs2" style="height:0px;border:0px;padding:0px;display:none"><span style="color:#FFFFFF;"></span></fieldset></div>
		
		<div id="<?php echo $genid ?>add_event_description_div" style="display:none">
			<fieldset>
			<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_add_event_description_context_help', true, logged_user()->getId())) {?>
			<div id="addEventPanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add event description','add_event_description'); ?>
			</div>
		<?php }?>
			<legend><?php echo lang('description')?></legend>
				<?php echo textarea_field('event[description]',array_var($event_data, 'description'), array('id' => 'descriptionFormText', 'tabindex' => '30'));?>
			</fieldset>
		</div>
		<div id="trap3"><fieldset id="fs3" style="height:0px;border:0px;padding:0px;display:none"><span style="color:#FFFFFF;"></span></fieldset></div>
		
<?php $occ = array_var($event_data, 'occ'); 
	$rsel1 = array_var($event_data, 'rsel1'); 
	$rsel2 = array_var($event_data, 'rsel2'); 
	$rsel3 = array_var($event_data, 'rsel3'); 
	$rnum = array_var($event_data, 'rnum'); 
	$rend = array_var($event_data, 'rend');?>
		
		<div id="<?php echo $genid ?>event_repeat_options_div" style="display:none">
		<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_add_event_repeat_options_context_help', true, logged_user()->getId())) {?>
			<div id="addEventPanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add event repeat options','add_event_repeat_options'); ?>
			</div>
		<?php }?>
			<legend><?php echo lang('CAL_REPEATING_EVENT')?></legend>
		<?php
		// calculate what is visible given the repeating options
		$hide = '';
		$hide2 = (isset($occ) && $occ == 6)? '' : "display: none;";
		if((!isset($occ)) OR $occ == 1 OR $occ=="6" OR $occ=="") $hide = "display: none;";
		// print out repeating options for daily/weekly/monthly/yearly repeating.
		if(!isset($rsel1)) $rsel1=true;
		if(!isset($rsel2)) $rsel2="";
		if(!isset($rsel3)) $rsel3="";
		if(!isset($rnum) || $rsel2=='') $rnum="";
		if(!isset($rend) || $rsel3=='') $rend="";
		if(!isset($hide2) ) $hide2="";?>
<table border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td align="left" valign="top" style="padding-bottom:6px">
		
			<table><tr><td><?php echo lang('CAL_REPEAT')?> 
			<select name="event[occurance]" onChange="changeRepeat()" tabindex="40">
				<option value="1" id="today"<?php if(isset($occ) && $occ == 1) echo ' selected="selected"'?>><?php echo lang('CAL_ONLY_TODAY')?></option>
				<option value="2" id="daily"<?php if(isset($occ) && $occ == 2) echo ' selected="selected"'?>><?php echo lang('CAL_DAILY_EVENT')?></option>
				<option value="3" id="weekly"<?php if(isset($occ) && $occ == 3) echo ' selected="selected"'?>><?php echo lang('CAL_WEEKLY_EVENT')?></option>
				<option value="4" id="monthly"<?php if(isset($occ) && $occ == 4) echo ' selected="selected"'?>><?php echo lang('CAL_MONTHLY_EVENT') ?></option>
				<option value="5" id="yearly"<?php if(isset($occ) && $occ == 5) echo  ' selected="selected"'?>><?php echo lang('CAL_YEARLY_EVENT') ?></option>
				<option value="6" id="holiday"<?php if(isset($occ) && $occ == 6)  echo ' selected="selected"'?>><?php echo lang('CAL_HOLIDAY_EVENT') ?></option>
			</select>
			<?php if (isset($occ) && $occ > 1 && $occ < 6){ ?>
			<script type="text/javascript">
				changeRepeat();
			</script>
			<?php } ?>
			</td><td>
			<div id="cal_extra1" style="<?php echo $hide ?>">
				&nbsp;<?php echo lang('CAL_EVERY') . text_field('event[occurance_jump]',array_var($event_data, 'rjump'), array('class' => 'title','size' => '2', 'id' => 'eventSubject', 'tabindex' => '50', 'maxlength' => '100', 'style'=>'width:25px')) ?>
				<span id="word"></span>
			</div>
			</td></tr></table>
		</td>
		</tr><tr>
		<td>
			<div id="cal_extra2" style="width: 400px; align: center; text-align: left; <?php echo $hide ?>">
				<?php echo radio_field('event[repeat_option]',$rsel1,array('id' => 'cal_repeat_option','value' => '1', 'tabindex' => '60')) . lang('CAL_REPEAT_FOREVER')?>
				<br/>
				<?php echo radio_field('event[repeat_option]',$rsel2,array('id' => 'cal_repeat','value' => '2', 'tabindex' => '70')) .lang('CAL_REPEAT');
				echo "&nbsp;" . text_field('event[repeat_num]', $rnum, array('size' => '3', 'id' => 'repeat_num', 'maxlength' => '3', 'style'=>'width:25px', 'tabindex' => '80')) ."&nbsp;" . lang('CAL_TIMES') ?>
				<br/>
				<table><tr><td><?php echo radio_field('event[repeat_option]',$rsel3,array('id' => 'cal_repeat_until','value' => '3', 'tabindex' => '90')) .lang('CAL_REPEAT_UNTIL');?></td>
				<td><?php echo pick_date_widget2('event[repeat_end]', $rend, $genid, 95);?></td></tr></table>
				<br>
			</div>
			<div id="cal_extra3" style="width: 300px; align: center; text-align: left; <?php echo $hide2 ?>'">
				<?php
				// get the week number
				$tmp = 1;
				$week = 0;
				while($week < 5 AND $tmp <= $day){
					$week++;
					$tmp += 7;
				}
				// get days in month and day name
				$daysinmonth = date("t",mktime(0,0,1,$month,$day,$year));
				$dayname = date("l",mktime(0,0,1,$month,$day,$year));
				$dayname = "CAL_" .strtoupper  ($dayname);
				// use week number, and days in month to calculate if it's on the last week.
				if($day > $daysinmonth - 7) $lastweek = true;
				else $lastweek = false;
				// calculate the correct number endings
				if($week==1) $weekname = "1st";
				elseif($week==2) $weekname = "2nd";
				elseif($week==3) $weekname = "3rd";
				else $weekname = $week."th";
				// print out the data for holiday repeating
		
				echo lang('CAL_HOLIDAY_EXPLAIN'). $weekname." ". lang($dayname) ." ".lang('CAL_DURING')." ".cal_month_name($month)." ".lang('CAL_EVERY_YEAR');
		
				if($lastweek){// if it's the last week, add option to have event repeat on LAST week every month (holiday repeating only)
					echo "<br/><br/>". checkbox_field('event[cal_holiday_lastweek]',$setlastweek, array('value' => '1', 'id' => 'cal_holiday_lastweek', 'maxlength' => '10')) .lang('CAL_HOLIDAY_EXTRAOPTION') ." " . lang($dayname)." ".lang('CAL_IN')." ".cal_month_name($month)." ".lang('CAL_EVERY_YEAR');
				}
				?>
			</div>
		</td>
	</tr>
</table>
		</fieldset>
		</div>
	<div id="trap4"><fieldset id="fs4" style="height:0px;border:0px;padding:0px;display:none"><span style="color:#FFFFFF;"></span></fieldset></div>

	<div id="<?php echo $genid ?>add_reminders_div" style="display:none">
	<fieldset>
	<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_add_event_reminders_context_help', true, logged_user()->getId())) {?>
			<div id="addEventPanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add event reminders','add_event_reminders'); ?>
			</div>
		<?php }?>
	<legend><?php echo lang('object reminders')?></legend>
		<?php echo render_add_reminders($object, "start");?>
	</fieldset>
	</div>
	<div id="trap5"><fieldset id="fs5" style="height:0px;border:0px;padding:0px;display:none"><span style="color:#FFFFFF;"></span></fieldset></div>

	<div id="<?php echo $genid ?>add_custom_properties_div" style="display:none">
	<fieldset>
	<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_add_event_custom_properties_context_help', true, logged_user()->getId())) {?>
			<div id="addEventPanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add event custom properties','add_event_custom_properties'); ?>
			</div>
		<?php }?>
	<legend><?php echo lang('custom properties')?></legend>
		<?php echo render_object_custom_properties($object, 'ProjectEvents', false) ?><br/><br/>
		<?php echo render_add_custom_properties($object);?>
	</fieldset>
	</div>
	<div id="trap6"><fieldset id="fs6" style="height:0px;border:0px;padding:0px;display:none"><span style="color:#FFFFFF;"></span></fieldset></div>

	<div id="<?php echo $genid ?>add_subscribers_div" style="display:none">
		<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_add_event_subscribers_context_help', true, logged_user()->getId())) {?>
			<div id="addEventPanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add event subscribers','add_event_subscribers'); ?>
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
	<div id="trap7"><fieldset id="fs7" style="height:0px;border:0px;padding:0px;display:none"><span style="color:#FFFFFF;"></span></fieldset></div>

	<?php if($object->isNew() || $object->canLinkObject(logged_user(), $project)) { ?>

	<div style="display:none" id="<?php echo $genid ?>add_linked_objects_div">
	<fieldset>
	<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_add_event_linked_objects_context_help', true, logged_user()->getId())) {?>
			<div id="addEventPanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add event linked objects','add_event_linked_objects'); ?>
			</div>
		<?php }?>
		<legend><?php echo lang('linked objects') ?></legend>
		<?php echo render_object_link_form($object) ?>
	</fieldset>	
	</div>
	<div id="trap8"><fieldset id="fs8" style="height:0px;border:0px;padding:0px;display:none"><span style="color:#FFFFFF;"></span></fieldset></div>
	<?php } // if ?>

	<div id="<?php echo $genid ?>add_event_invitation_div" style="display:none" class="og-add-subscribers">
	<fieldset id="emailNotification">
	<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_add_event_invitation_context_help', true, logged_user()->getId())) {?>
			<div id="addEventPanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add event invitation','add_event_invitation'); ?>
			</div>
		<?php }?>
		<legend><?php echo lang('event invitations') ?></legend>
		<?php // ComboBox for Assistance confirmation 
			if (!$event->isNew()) {
				$event_invs = $event->getInvitations();
				if (isset($event_invs[$filter_user])) {
					$event_inv_state = $event_invs[$filter_user]->getInvitationState();
				} else {
					$event_inv_state = -1;
				}
				
				if ($event_inv_state != -1) {
					$options = array(
						option_tag(lang('yes'), 1, ($event_inv_state == 1)?array('selected' => 'selected'):null),
						option_tag(lang('no'), 2, ($event_inv_state == 2)?array('selected' => 'selected'):null),
						option_tag(lang('maybe'), 3, ($event_inv_state == 3)?array('selected' => 'selected'):null)
					);
					if ($event_inv_state == 0) {
						$options[] = option_tag(lang('decide later'), 0, ($event_inv_state == 0) ? array('selected' => 'selected'):null);
					}
					?>
					<table><tr><td style="padding-right: 6px;"><label for="eventFormComboAttendance" class="combobox"><?php echo lang('confirm attendance') ?></label></td><td>
					<?php echo select_box('event[confirmAttendance]', $options, array('id' => 'eventFormComboAttendance', 'tabindex' => '100'));?>
					</td></tr></table>	
			<?php	} //if			
			} // if ?>

			<p><?php echo lang('event invitations desc') ?></p>

			<?php echo checkbox_field('event[send_notification]', array_var($event_data, 'send_notification', $event->isNew()), array('id' => 'eventFormSendNotification', 'tabindex' => '110')) ?> 
			<label for="eventFormSendNotification" class="checkbox"><?php echo lang('send new event notification') ?></label>
	</fieldset>
	</div>	
	<div id="trap9"><fieldset id="fs9" style="height:0px;border:0px;padding:0px;display:none"><span style="color:#FFFFFF;"></span></fieldset></div>

<div>
<fieldset><legend><?php echo lang('CAL_TIME_AND_DURATION') ?></legend>
<table>
	<tr style="padding-bottom:4px">
		<td align="right" style="padding-right:10px;padding-bottom:6px;padding-top:2px"><?php echo lang('CAL_DATE') ?></td>
		<td align='left'><?php
				$dv_start = new DateTimeValue(time());
				$dv_start->setDay($day);
				$dv_start->setMonth($month);
				$dv_start->setYear($year);
				$event->setStart($dv_start);
				echo pick_date_widget2('event[start_value]', $event->getStart(), $genid, 120); ?>
		</td>
	</tr>

	<tr style="padding-bottom:4px">
		<td align="right" style="padding-right:10px;padding-bottom:6px;padding-top:2px">
			<?php echo lang('CAL_TIME') ?>
		</td>
		<td>
		<?php
			$hr = array_var($event_data, 'hour');
		 	$minute = array_var($event_data, 'minute');
			$is_pm = array_var($event_data, 'pm');
			$time_val = "$hr:" . str_pad($minute, 2, '0') . ($use_24_hours ? '' : ' '.($is_pm ? 'PM' : 'AM'));
			echo pick_time_widget2('event[start_time]', $time_val, $genid, 130);
		?>
		</td>
	</tr>
	<!--   begin printing the duration options-->
	<tr>
		<td align="right" style="padding-right:10px;padding-bottom:6px;padding-top:2px"><?php echo lang('CAL_DURATION') ?></td>
		<td align="left">
		<div id="<?php echo $genid ?>ev_duration_div">
			<select name="event[durationhour]" size="1" tabindex="150">
			<?php
			for($i = 0; $i < 24; $i++) {
				echo "<option value='$i'";
				if(array_var($event_data, 'durationhour')== $i) echo ' selected="selected"';
				echo ">$i</option>\n";
			}
			?>
			</select> <?php echo lang('CAL_HOURS') ?> <select
				name="event[durationmin]" size="1" tabindex="160">
				<?php
				// print out the duration minutes drop down
				$durmin = array_var($event_data, 'durationmin');
				for($i = 0; $i <= 59; $i = $i + 15) {
					echo "<option value='$i'";
					if($durmin >= $i && $i > $durmin - 15) echo ' selected="selected"';
					echo sprintf(">%02d</option>\n", $i);
				}
				?>
			</select> 
		</div>
		</td>
	</tr>
	<tr style="padding-bottom:4px">
		<td align="right" style="padding-right:10px;padding-bottom:6px;padding-top:2px">&nbsp;</td>
		<td align='left'>
			<?php
			echo checkbox_field('event[type_id]',array_var($event_data, 'typeofevent') == 2, array('id' => 'format_html','value' => '2', 'tabindex' => '170', 'onchange' => 'toggleDiv(\''.$genid.'event[start_time]\'); toggleDiv(\''.$genid.'ev_duration_div\');'));
			echo lang('CAL_FULL_DAY');
			?>
		</td>
	</tr>

	<!--   print extra time options-->
	
</table>
</fieldset>
</div>

<?php foreach ($categories as $category) { ?>
	<div <?php if (!$category['visible']) echo 'style="display:none"' ?> id="<?php echo $genid . $category['name'] ?>">
	<fieldset>
		<legend><?php echo lang($category['name'])?><?php if ($category['required']) echo ' <span class="label_required">*</span>'; ?></legend>
		<?php echo $category['content'] ?>
	</fieldset>
	</div>
	<?php } ?>

	<input type="hidden" name="cal_origday" value="<?php echo $day?>">
	<input type="hidden" name="cal_origmonth" value="<?php echo $month?>">
	<input type="hidden" name="cal_origyear" value="<?php echo $year?>">
	
	<div>
		<?php echo render_object_custom_properties($object, 'ProjectEvents', true) ?>
	</div><br/>
	
	<?php 
	// THIS IS HERE SO THAT THE DURATION CAN BE SET CORRECTLY ACCORDING TO THE EVENT'S ACTUAL START DATE.
	// otherwise, if you modify a repeating event, it can save the duration as a totally different date!
	echo  submit_button($event->isNew() ? lang('add event') : lang('save changes'),'e',array('style'=>'margin-top:0px;margin-left:10px', 'tabindex' => '180'));?>
	</div></div>
</form>



<script type="text/javascript">


var wsTree = Ext.get('<?php echo $genid ?>wsSel');
var filter_user = '<?php echo $filter_user ?>';
var prevWsVal = -1;

og.drawInnerHtml = function(companies) {
	var htmlStr = '';
	htmlStr += '<div id="<?php echo $genid ?>invite_companies"></div>';
	htmlStr += '<script type="text/javascript">';
	htmlStr += 'var div = Ext.getDom(\'<?php echo $genid ?>invite_companies\');';
	htmlStr += 'div.invite_companies = {};';
	htmlStr += 'var cos = div.invite_companies;';
	htmlStr += '<\/script>';
	if (companies != null) {
		for (i = 0; i < companies.length; i++) {
			comp_id = companies[i].object_id;
			comp_name = companies[i].name;
			htmlStr += '<script type="text/javascript">';
			htmlStr += 'cos.company_' + comp_id + ' = {id:\'<?php echo $genid ?>inviteCompany' + comp_id + '\', checkbox_id : \'inviteCompany' + comp_id + '\',users : []};';
			htmlStr += '\<\/script>';
				
			htmlStr += '<div class="companyDetails">';
			htmlStr += '<div class="companyName">';
			
			htmlStr += '<input type="checkbox" class="checkbox" name="event[invite_company_'+comp_id+']" id="<?php echo $genid ?>inviteCompany'+comp_id+'" onclick="App.modules.addMessageForm.emailNotifyClickCompany('+comp_id+',\'<?php echo $genid ?>\',\'invite_companies\', \'invitation\')"></input>'; 
			htmlStr += '<label for="<?php echo $genid ?>inviteCompany'+comp_id+'" class="checkbox">'+og.clean(comp_name)+'</label>';
			
			htmlStr += '</div>';
			htmlStr += '<div class="companyMembers">';
			htmlStr += '<ul>';
			
			for (j = 0; j < companies[i].users.length; j++) {
				usr = companies[i].users[j];
				htmlStr += '<li><input type="checkbox" class="checkbox" name="event[invite_user_'+usr.id+']" id="<?php echo $genid ?>inviteUser'+usr.id+'" onclick="App.modules.addMessageForm.emailNotifyClickUser('+comp_id+','+usr.id+',\'<?php echo $genid ?>\',\'invite_companies\', \'invitation\')"></input>'; 
				htmlStr += '<label for="<?php echo $genid ?>inviteUser'+usr.id+'" class="checkbox">'+og.clean(usr.name)+'</label>';
				htmlStr += '<script type="text/javascript">';
				htmlStr += 'cos.company_' + comp_id + '.users.push({ id:'+usr.id+', checkbox_id : \'inviteUser' + usr.id + '\'});';
				htmlStr += '\<\/script></li>';
			}
			htmlStr += '</ul>';
			htmlStr += '</div>';
			htmlStr += '</div>';
		}
	}
	return htmlStr;
}

og.drawUserList = function(success, data) {
	companies = data.companies;

	var inv_div = Ext.get('<?php echo $genid ?>inv_companies_div');
	if (inv_div != null) inv_div.remove();
	inv_div = Ext.get('emailNotification');
	
	if (inv_div != null) {
		inv_div.insertHtml('beforeEnd', '<div id="<?php echo $genid ?>inv_companies_div">' + og.drawInnerHtml(companies) + '</div>');	
		inv_div.repaint();
	}
}

og.redrawUserList = function(){
	var wsVal = Ext.get('<?php echo $genid ?>wsSelValue').getValue();
	
	if (wsVal != prevWsVal) {
		og.openLink(og.getUrl('event', 'allowed_users_view_events', {ws_id:wsVal, user:filter_user}), {callback:og.drawUserList});
		prevWsVal = wsVal;
	}
}
wsTree.addListener('click', og.redrawUserList);
og.redrawUserList();

Ext.get('eventSubject').focus();
<?php if (array_var($event_data, 'typeofevent') == 2) echo 'toggleDiv(\''.$genid.'event[start_time]\'); toggleDiv(\''.$genid.'ev_duration_div\');'; ?>

</script>