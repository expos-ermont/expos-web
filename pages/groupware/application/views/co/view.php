<?php 
	if($object instanceof ProjectDataObject && $object->canView(logged_user())) 	{
		add_page_action(lang('view history'),$object->getViewHistoryUrl(),'ico-history');
		/*if (!$object->isTrashed())
			add_page_action(lang('share'), $object->getShareUrl(), 'ico-share');
		*/
	}
	Hook::fire("render_page_actions", $object, $ret = 0);
	$coId = $object->getId() . get_class($object->manager()); 
	if (!isset($iconclass))
		$iconclass = "ico-large-" . $object->getObjectTypeName();
		
	$genid = gen_id();
	$date_format = user_config_option('date_format', 'd/m/Y');
?>

<table style="width:100%" id="<?php echo $genid ?>-co"><tr>
<td>
	<table style="width:100%;border-collapse:collapse;">
		<col width="12px"/><col width="36px"/><col/><col width="12px"/>
		<tr><td></td><td></td><td></td><td style="width:12px"></td></tr>
		<tr>
			<td class="coViewIcon" colspan=2 rowspan=2>
				<?php if (isset($image)) { echo $image; } else {?>
				<div id="<?php echo $coId; ?>_iconDiv" class="coViewIconImage <?php echo $iconclass ?>"></div>
				<?php } ?>
			</td>
			
			<td class="coViewHeader" rowspan=2>
				<div class="coViewTitle">
					<table><tr><td>
					<?php echo isset($title)? $title : lang($object->getObjectTypeName()) . ": " . clean($object->getObjectName()) ?>
					</td>
					
					</tr></table>
				</div>
				<div>
					<?php if (!isset($description)) $description = "";
					Hook::fire("render_object_description", $object, $description);
					echo $description;
					?>
				</div>
				
				<a class="internalLink" href="#" onclick="og.closeView(); return false;" title="<?php echo lang('close') ?>" ><div class="coViewClose" style="cursor:pointer"><?php echo lang('close') ?>&nbsp;&nbsp;X</div></a>
			</td>
			
			<td class="coViewTopRight" style="width:12px"></td>
		</tr>
		<tr><td class="coViewRight" rowspan=2 style="width:12px"></td></tr>
		
		<tr>
			<td class="coViewBody" colspan=3>
			<div style="padding-bottom:15px">
				<?php 
				if (isset($content_template) && is_array($content_template)){
					tpl_assign('object', $object);
					if (isset($variables))
						tpl_assign('variables', $variables);
					$this->includeTemplate(get_template_path($content_template[0], $content_template[1]));
				}
				else if (isset($content)) echo $content;
				?>
			</div>
			<?php if (isset($internalDivs)){
				foreach ($internalDivs as $idiv)
					echo $idiv;
			}		
			
			if ($object instanceof ApplicationDataObject)
				echo render_custom_properties($object);
			
			if ($object instanceof ProjectDataObject && $object->allowsTimeslots())
				echo render_object_timeslots($object, $object->getViewUrl());
			
			if ($object instanceof ProjectDataObject && $object->isCommentable())
				echo render_object_comments($object, $object->getViewUrl());
			?>
			</td>
		</tr>
		<tr>
			<td class="coViewBottomLeft"></td>
			<td class="coViewBottom" colspan=2></td>
			<td class="coViewBottomRight" style="width:12px">&nbsp;</td>
		</tr>
	</table>
</td>


<!-- Actions Panel -->
<td style="width:250px; padding-left:10px">
<table style="width:240px;border-collapse:collapse">
	<col width=12/><col width=216/><col width=12/>
	<tr>
		<td class="coViewHeader" colspan=2 rowspan=2><div class="coViewPropertiesHeader"><?php echo lang("actions") ?></div></td>
		<td class="coViewTopRight"></td>
	</tr>
		
	<tr><td class="coViewRight" rowspan=2></td></tr>
	
	<tr>
		<td class="coViewBody" colspan=2>
		<?php if(count(PageActions::instance()->getActions()) > 0 ) {?>
			<div>
			<?php
				$pactions = PageActions::instance()->getActions();
				foreach ($pactions as $action) { 
					if ($action->getTarget() != '') {
					?>
					<a style="display:block" class="coViewAction <?php echo $action->getName()?>" href="<?php echo $action->getURL()?>" target="<?php echo $action->getTarget()?>">
					<?php } else { ?>
					<a style="display:block" class="<?php $attribs = $action->getAttributes(); echo $attribs["download"] ? '':'internalLink' ?> coViewAction <?php echo $action->getName()?>" href="<?php echo $action->getURL()?>">
				<?php } echo $action->getTitle() ?></a>
			<?php } ?>
			</div>
		<?php } ?>
		</td>
	</tr>
	
	<tr>
		<td class="coViewBottomLeft" style="width:12px;">&nbsp;</td>
		<td class="coViewBottom" style="width:216px;"></td>
		<td class="coViewBottomRight" style="width:12px;">&nbsp;&nbsp;</td>
	</tr>
</table>



<!-- Properties Panel -->
<table style="width:240px">
	<col width=12/><col width=216/><col width=12/>
	<tr>
		<td class="coViewHeader" colspan=2 rowspan=2><div class="coViewPropertiesHeader"><?php echo lang("properties") ?></div></td>
		<td class="coViewTopRight"></td>
	</tr>
		
	<tr><td class="coViewRight" rowspan=2></td></tr>
	
	<tr>
		<td class="coViewBody" colspan=2>
			<div class="prop-col-div" style="width:200;">
				<span style="color:333333;font-weight:bolder;"><?php echo lang('unique id') ?>:&nbsp;</span><?php echo $object->getUniqueObjectId() ?>
			</div>
		<?php 
		if ($object instanceof ProjectDataObject)
			$user_object_workspaces = $object->getWorkspaces(logged_user()->getActiveProjectIdsCSV());
		
		$has_wss = $object instanceof ProjectDataObject && (is_array($user_object_workspaces) && count($user_object_workspaces) > 0);
		if ($has_wss || $object->isTaggable()) { ?>
			<div class="prop-col-div" style="width:200;">
			<?php if ($has_wss) {?>
			<span style="color:333333;font-weight:bolder;"><?php echo lang('workspace') ?>:</span>
		<?php
			$projectLinks = array();
			foreach ($user_object_workspaces as $ws) {
				$projectLinks[] = '<span class="project-replace">' . $ws->getId() . '</span>';
			}
			echo '<br/>' . implode('<br/>',$projectLinks);
		}
		
		if ($object->isTaggable() && ($tags = project_object_tags2($object)) && $tags != '--') {?>
			<br/>
			<div style="color:333333;font-weight:bolder;"><?php echo lang('tags') ?>:</div><?php echo $tags ?>
		<?php } ?>
		</div>
	<?php } // if ?>
	
	<?php if($object->isLinkableObject() && !$object->isTrashed()) { ?>
		<div class="prop-col-div" style="width:200;"><?php echo render_object_links($object, $object->canEdit(logged_user()))?></div>
	<?php } ?>
	
	<?php if ($object instanceof ProjectDataObject) { ?>
		<div class="prop-col-div" style="width:200;"><?php echo render_object_subscribers($object)?></div>
	<?php } ?>

	<div class="prop-col-div" style="border:0px;width:200;">
    	<?php if($object->getCreatedBy() instanceof User) { ?>
    		<span style="color:#333333;font-weight:bolder;">
    			<?php echo lang('created by') ?>:
			</span><br/><div style="padding-left:10px">
			<?php 
			if ($object->getCreatedBy() instanceof User){
				if (logged_user()->getId() == $object->getCreatedBy()->getId())
					$username = lang('you');
				else
					$username = clean($object->getCreatedBy()->getDisplayName());
					
				if ($object->getObjectCreationTime() && $object->getCreatedOn()->isToday()){
					$datetime = format_time($object->getCreatedOn());
					echo lang('user date today at', $object->getCreatedBy()->getCardUrl(), $username, $datetime, clean($object->getCreatedBy()->getDisplayName()));
				} else {
					$datetime = format_datetime($object->getCreatedOn(), $date_format, logged_user()->getTimezone());
					echo lang('user date', $object->getCreatedBy()->getCardUrl(), $username, $datetime, clean($object->getCreatedBy()->getDisplayName()));
				}
			} ?></div>
    	<?php } // if ?>
    	
    	<?php if($object->getObjectUpdateTime() && $object->getUpdatedBy() instanceof User && $object->getCreatedOn() != $object->getUpdatedOn()) { ?>
    		<span style="color:#333333;font-weight:bolder;">
    			<?php echo lang('modified by') ?>:
			</span><br/><div style="padding-left:10px">
			<?php 
			if ($object->getUpdatedBy() instanceof User){
					
				if (logged_user()->getId() == $object->getUpdatedBy()->getId())
					$username = lang('you');
				else
					$username = clean($object->getUpdatedBy()->getDisplayName());

				if ($object->getUpdatedOn()->isToday()){
					$datetime = format_time($object->getUpdatedOn());
					echo lang('user date today at', $object->getUpdatedBy()->getCardUrl(), $username, $datetime, clean($object->getUpdatedBy()->getDisplayName()));
				} else {
					$datetime = format_datetime($object->getUpdatedOn(), $date_format, logged_user()->getTimezone());
					echo lang('user date', $object->getUpdatedBy()->getCardUrl(), $username, $datetime, clean($object->getUpdatedBy()->getDisplayName()));
				}
			}?></div>
		<?php } // if ?>
		
		<?php
		if ($object instanceof ProjectDataObject && $object->isTrashable() && $object->getTrashedById() != 0) { ?>
    		<span style="color:#333333;font-weight:bolder;">
    			<?php echo lang('deleted by') ?>:
			</span><br/><div style="padding-left:10px">
			<?php
			$trash_user = Users::findById($object->getTrashedById());
			if ($trash_user instanceof User){
				if (logged_user()->getId() == $trash_user->getId())
					$username = lang('you');
				else
					$username = clean($trash_user->getDisplayName());

				if ($object->getTrashedOn()->isToday()){
					$datetime = format_time($object->getTrashedOn());
					echo lang('user date today at', $trash_user->getCardUrl(), $username, $datetime, clean($trash_user->getDisplayName()));
				} else {
					$datetime = format_datetime($object->getTrashedOn(), $date_format, logged_user()->getTimezone());
					echo lang('user date', $trash_user->getCardUrl(), $username, $datetime, clean($trash_user->getDisplayName()));
				}
			}
			 ?></div>
		<?php } // if ?>
		
		<?php
		if ($object instanceof ProjectFile) { ?>
			<span style="color:#333333;font-weight:bolder;">
    			<?php echo lang('mime type') ?>:
			</span><br/><div style="padding-left:10px">
				<?php echo $object->getLastRevision()->getTypeString(); ?>
			</div>
		<?php if ($object->isCheckedOut()) { ?>
	    		<span style="color:#333333;font-weight:bolder;">
	    			<?php echo lang('checked out by') ?>:
				</span><br/><div style="padding-left:10px">
				<?php
				$checkout_user = Users::findById($object->getCheckedOutById());
				if ($checkout_user instanceof User){
					if (logged_user()->getId() == $checkout_user->getId())
						$username = lang('you');
					else
						$username = clean($checkout_user->getDisplayName());
	
					if ($object->getCheckedOutOn()->isToday()){
						$datetime = format_time($object->getCheckedOutOn());
						echo lang('user date today at', $checkout_user->getCardUrl(), $username, $datetime, clean($checkout_user->getDisplayName()));
					} else {
						$datetime = format_datetime($object->getCheckedOutOn(), $date_format, logged_user()->getTimezone());
						echo lang('user date', $checkout_user->getCardUrl(), $username, $datetime, clean($checkout_user->getDisplayName()));
					}
				}
			 ?></div>
		<?php }
			} // if ?>
	</div>
	
	<?php Hook::fire("render_object_properties", $object, $ret = 0);?>
		</td>
	</tr>
	
	<tr>
		<td class="coViewBottomLeft" style="width:12px;">&nbsp;&nbsp;</td>
		<td class="coViewBottom" style="width:216px;"></td>
		<td class="coViewBottomRight" style="width:12px;">&nbsp;&nbsp;</td>
	</tr>
	</table>
</td>
</tr></table>
<script type="text/javascript">
og.showWsPaths('<?php echo $genid ?>-co',null,true);
</script>
