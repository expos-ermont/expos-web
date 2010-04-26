<?php
  require_javascript('modules/addMessageForm.js'); 
  set_page_title($webpage->isNew() ? lang('add webpage') : lang('edit webpage'));
  if ($webpage->isNew()) {
  	$project = active_or_personal_project();
  } else {
  	$project = $webpage->getProject();
  }
  $genid = gen_id();
  $object = $webpage;
  if (!$webpage->isNew()) { 
  		if ($webpage->isTrashed()) {
    		add_page_action(lang('restore from trash'), "javascript:if(confirm(lang('confirm restore objects'))) og.openLink('" . $webpage->getUntrashUrl() ."');", 'ico-restore');
    		add_page_action(lang('delete permanently'), "javascript:if(confirm(lang('confirm delete permanently'))) og.openLink('" . $webpage->getDeletePermanentlyUrl() ."');", 'ico-delete');
    	} else {
    		add_page_action(lang('move to trash'), "javascript:if(confirm(lang('confirm move to trash'))) og.openLink('" . $webpage->getTrashUrl() ."');", 'ico-trash');
    	}
  }
?>

<form style='height:100%;background-color:white' class="internalForm" action="<?php echo $webpage->isNew() ? get_url('webpage', 'add') : $webpage->getEditUrl() ?>" method="post">

<div class="webpage">
<div class="coInputHeader">
	<div class="coInputHeaderUpperRow">
	<div class="coInputTitle"><table style="width:535px">
	<tr><td><?php echo $webpage->isNew() ? lang('new webpage') : lang('edit webpage') ?>
	</td><td style="text-align:right"><?php echo submit_button($webpage->isNew() ? lang('add webpage') : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px', 'tabindex' => '20')) ?></td></tr></table>
	</div>
	
	</div>
	<div>
		<?php echo label_tag(lang('title'), 'webpageFormTitle', true) ?>
   		<?php echo text_field('webpage[title]', array_var($webpage_data, 'title'), array('class' => 'title', 'tabindex' => '1', 'id' => 'webpageFormTitle')) ?>
  	</div>
  	
  	<?php $categories = array(); Hook::fire('object_edit_categories', $object, $categories); ?>
	
	<div style="padding-top:5px">
		<a href="#" class="option" tabindex=0 onclick="og.toggleAndBolden('add_webpage_select_workspace_div', this)"><?php echo lang('workspace') ?></a> - 
		<a href="#" class="option" tabindex=0 onclick="og.toggleAndBolden('add_webpage_tags_div', this)"><?php echo lang('tags') ?></a> - 
		<a href="#" class="option" tabindex=0 onclick="og.toggleAndBolden('add_webpage_description_div', this)"><?php echo lang('description') ?></a> - 
		<a href="#" class="option" tabindex=0 onclick="og.toggleAndBolden('add_custom_properties_div', this)"><?php echo lang('custom properties') ?></a> -
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
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_webpage_context_help', true, logged_user()->getId()))) {?>
			<div id="webpagePanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add webpage','add_webpage'); ?>
			</div>
		<?php }?>
    <div id="add_webpage_select_workspace_div" style="display:none">
	<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_webpage_workspace_context_help', true, logged_user()->getId()))) {?>
			<div id="webpagePanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add webpage workspace','add_webpage_workspace'); ?>
			</div>
		<?php }?>
	<legend><?php echo lang('workspace') ?></legend>
		<?php echo select_project2('webpage[project_id]', ($project instanceof Project)? $project->getId():active_or_personal_project()->getId(), $genid) ?>
	</fieldset>
    </div>
  
    <div id="add_webpage_tags_div" style="display:none">
    <fieldset>
    <?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
						if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_webpage_tags_context_help', true, logged_user()->getId()))) {?>
			<div id="webpagePanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add webpage tags','add_webpage_tags'); ?>
			</div>
		<?php }?>
    <legend>
    	<?php echo lang('tags') ?></legend>
    	<?php echo autocomplete_tags_field("webpage[tags]", array_var($webpage_data, 'tags'), null, 30); ?>
	</fieldset>
	</div>

	<div id="add_webpage_description_div" style="display:none">
	<fieldset>
	<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
					if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_webpage_description_context_help', true, logged_user()->getId()))) {?>
			<div id="webpagePanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add webpage description','add_webpage_description'); ?>
			</div>
		<?php }?>
	<legend>
		<?php echo label_tag(lang('description'), 'webpageFormDesc') ?> </legend>
    	<?php echo textarea_field('webpage[description]', array_var($webpage_data, 'description'), array('class' => 'long', 'id' => 'webpageFormDesc', 'tabindex' => '40')) ?>
    </fieldset>
	</div>
  
	<div id='add_custom_properties_div' style="display:none">
	<fieldset>
	<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_webpage_custom_properties_context_help', true, logged_user()->getId()))) {?>
			<div id="webpagePanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add webpage custom properties','add_webpage_custom_properties'); ?>
			</div>
		<?php }?>
	<legend><?php echo lang('custom properties') ?></legend>
		<?php echo render_object_custom_properties($object, 'ProjectWebPages', false) ?><br/><br/>
		<?php echo render_add_custom_properties($object); ?>
	</fieldset>
	</div>
  
  
 	<div id="<?php echo $genid ?>add_subscribers_div" style="display:none">
		<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_webpage_subscribers_context_help', true, logged_user()->getId()))) {?>
			<div id="webpagePanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add webpage subscribers','add_webpage_subscribers'); ?>
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
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_webpage_linked_objects_context_help', true, logged_user()->getId()))) {?>
			<div id="webpagePanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp add webpage linked objects','add_webpage_linked_objects'); ?>
			</div>
		<?php }?>
		<legend><?php echo lang('linked objects') ?></legend>
		<?php echo render_object_link_form($object) ?>
	</fieldset>	
	</div>
	<?php } // if ?>

  <div>
    <?php echo label_tag(lang('url'), 'webpageFormURL', true) ?>
    <?php echo text_field('webpage[url]', array_var($webpage_data, 'url'), array('class' => 'title', 'tabindex' => '10', 'id' => 'webpageFormURL')) ?>
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
		<?php echo render_object_custom_properties($object, 'ProjectWebPages', true) ?>
	</div><br/>
  
  <?php echo submit_button($webpage->isNew() ? lang('add webpage') : lang('save changes'), 's', 
  	array('tabindex' => '200')) ?>
  </div>
 </div>
</form>

<script type="text/javascript">
	Ext.get('webpageFormTitle').focus();
</script>