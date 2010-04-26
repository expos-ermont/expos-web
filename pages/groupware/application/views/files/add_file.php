<?php
require_javascript("modules/addFileForm.js");
require_javascript('modules/addMessageForm.js');
if ($file->isNew()) {
	$submit_url = get_url('files', 'add_file');
} else if (isset($checkin) && $checkin) {
	$submit_url = $file->getCheckinUrl();
} else {
	$submit_url = $file->getEditUrl();
}

$project = active_or_personal_project();
$projects =  active_projects();

$enableUpload = $file->isNew()
|| (isset($checkin) && $checkin) || ($file->getCheckedOutById() == 0) || ($file->getCheckedOutById() != 0 && logged_user()->isAdministrator())
|| ($file->getCheckedOutById() == logged_user()->getId());
$genid = gen_id();
$object = $file;
$comments_required = config_option('file_revision_comments_required');
?>

<form class="internalForm" style="height: 100%; background-color: white" id="<?php echo $genid ?>addfile" name="<?php echo $genid ?>addfile" action="<?php echo $submit_url ?>" method="post">
	<input id="<?php echo $genid ?>hfFileIsNew" type="hidden" value="<?php echo $file->isNew()?>">
	<input id="<?php echo $genid ?>hfAddFileAddType" name='file[add_type]' type="hidden" value="regular">
	<input id="<?php echo $genid ?>hfFileId" name='file[file_id]' type="hidden" value="<?php echo array_var($file_data, 'file_id') ?>">
	<input id="<?php echo $genid ?>hfEditFileName" name='file[edit_name]' type="hidden" value="<?php echo clean(array_var($file_data, 'edit_name')) ?>">
	<input id="<?php echo $genid ?>hfType" name='file[type]' type="hidden" value="<?php echo $file->isNew() ? "" : $file->getType() ?>">
	<input name="file[upload_id]" type="hidden" value="<?php echo $genid ?>" />

<div class="file">

<div class="coInputHeader">

<div class="coInputHeaderUpperRow">
<div class="coInputTitle">
<table style="width: 535px">
	<tr>
		<td><?php echo $file->isNew() ? lang('upload file') : (isset($checkin) ? lang('checkin file') : lang('edit file properties')) ?>
		</td>
		<td style="text-align: right">
			<?php echo submit_button($file->isNew() ? lang('add file') : (isset($checkin) ? lang('checkin file') : lang('save changes')),'s',array("onclick" => 'og.fileCheckSubmit(\'' . $genid .'\'); return false;', 'style'=>'margin-top:0px;margin-left:10px','id' => $genid.'add_file_submit1', 'tabindex' => '210')) ?>
		</td>
	</tr>
</table>
</div>
</div>

<?php if ($enableUpload) {
	if ($file->isNew()) {?>
		<div id="<?php echo $genid ?>selectFileControlDiv">
			<?php 
				$show_help_option = user_config_option('show_context_help', 'until_close'); 
				if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_file_context_help', true, logged_user()->getId()))) {?>
				<div id="weblinkFileContextHelp" style="margin-bottom:3px;">
					<?php render_context_help($this, 'chelp addfile', 'add_file'); ?>
				</div>
			<?php }?>
	    	<?php echo radio_field($genid.'_rg', true, array('id' => $genid.'fileRadio', 'onchange' => 'og.addDocumentTypeChanged(0, "'.$genid.'")', 'value' => '0')) . ' ' . lang('file')?>
	    	<?php echo radio_field($genid.'_rg', false, array('id' => $genid.'weblinkRadio', 'onchange' => 'og.addDocumentTypeChanged(1, "'.$genid.'")', 'value' => '1')) . ' ' . lang('weblink')?>
	        <div id="<?php echo $genid ?>fileUploadDiv">
			<?php echo label_tag(lang('file'), $genid . 'fileFormFile', true) ?>
			<?php 
				Hook::fire('render_upload_control', array(
					"genid" => $genid,
					"attributes" => array(
						"id" => $genid . "fileFormFile",
						"class" => "title",
						"size" => "88",
						"tabindex" => "10",
						"onchange" => "javascript:og.updateFileName('" . $genid .  "', this.value);"
					)
				), $ret);
			?>
			<p><?php echo lang('upload file desc', format_filesize(get_max_upload_size())) ?></p>
			</div>
	    	<div id="<?php echo $genid ?>weblinkDiv" style="display:none;">
	        <?php echo label_tag(lang('weblink'), 'file[url]', true, array('id' => $genid.'weblinkLbl', 'type' => 'text')) ?>
	    	<?php echo text_field('file[url]', '', array('id' => 'url', 'style' => 'width:500px;', "onblur" => "javascript:og.updateFileName('" . $genid .  "', this.value);")) ?>
	    	</div>
		</div>
	<?php } ?>
<?php } // if ?>

	<div id="<?php echo $genid ?>addFileFilename" style="<?php echo $file->isNew()? 'display:none' : '' ?>">
      	<?php echo label_tag(lang('new filename'), $genid .'fileFormFilename') ?>    
        <?php echo text_field('file[name]',$file->getFilename(), array("id" => $genid .'fileFormFilename', 'tabindex' => '20', 'class' => 'title', 
        	'onchange' => ($file->getType() == ProjectFiles::TYPE_DOCUMENT? 'javascript:og.checkFileName(\'' . $genid .  '\')' : ''))) ?>
        
    	<?php if ($file->getType() == ProjectFiles::TYPE_WEBLINK){?>
        <?php echo label_tag(lang('new weblink'), $genid .'fileFormFilename') ?>
        <?php echo text_field('file[url]',$file->getUrl(), array("id" => $genid .'fileFormUrl', 'class' => 'title', 'tabindex' => '21')) ?>   
        <?php } //else ?>
		<br/>
    </div>

	<?php $categories = array(); Hook::fire('object_edit_categories', $object, $categories); ?>

	<div style="padding-top: 5px">
	<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_file_select_workspace_div',this)"><?php echo lang('workspace') ?></a>
	- <a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_file_tags_div', this)"><?php echo lang('tags') ?></a>
	- <a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_file_description_div',this)"><?php echo lang('description') ?></a>
	<?php if(logged_user()->isMemberOfOwnerCompany()) { ?>
		- <a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_file_options_div',this)"><?php echo lang('options') ?></a>
	<?php } ?>
	- <a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_custom_properties_div',this)"><?php echo lang('custom properties') ?></a>
	- <a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_subscribers_div',this)"><?php echo lang('object subscribers') ?></a>
	<?php if($object->isNew() || $object->canLinkObject(logged_user(), $project)) { ?>
		- <a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_linked_objects_div',this)"><?php echo lang('linked objects') ?></a>
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
			if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_upload_file_context_help', true, logged_user()->getId())) {?>
			<div id="uploadFileContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp upload file','upload_file'); ?>
			</div>
		<?php }?>
<?php if ($enableUpload) { ?>

	<?php if($file->isNew()) { //----------------------------------------------------ADD   ?>

		<div class="content">
			<div id="<?php echo $genid ?>addFileFilenameCheck" style="display: none">
				<h2><?php echo lang("checking filename") ?></h2>
			</div>
			<div id="<?php echo $genid ?>addFileUploadingFile" style="display: none">
				<h2><?php echo lang("uploading file") ?></h2>
			</div>

			<div id="<?php echo $genid ?>addFileFilenameExists" style="display: none">
				<h2><?php echo lang("duplicate filename")?></h2>
				<p><?php echo lang("filename exists") ?></p>
				<div style="padding-top: 10px">
				<table>
					<tr>
						<td style="height: 20px; padding-right: 4px">
							<?php echo radio_field('file[upload_option]',true, array("id" => $genid . 'radioAddFileUploadAnyway', "value" => -1, 'tabindex' => '30')) ?>
						</td><td>
							<?php echo lang('upload anyway')?>
						</td>
					</tr>
				</table>
				<table id="<?php echo $genid ?>upload-table">
				</table>
				</div>
			</div>
		</div>
		<?php echo input_field('file[revision_comment]', array_var($file_data, 'revision_comment', lang('initial versions')), array('type' => 'hidden', 'id' => $genid.'fileFormRevisionComment')) ?>
		<input type='hidden' id ="<?php echo $genid ?>RevisionCommentsRequired" value="<?php echo $comments_required? '1':'0'?>"/>

	<?php }  else {//----------------------------------------------------------------EDIT?>

		<div class="content">
			<?php 
			if($file->getType() == ProjectFiles::TYPE_DOCUMENT){
			if (!isset($checkin)) {?>
				<div class="header">
					<?php echo checkbox_field('file[update_file]', array_var($file_data, 'update_file'), array('class' => 'checkbox', 'id' => $genid . 'fileFormUpdateFile', 'tabindex' => '60', 'onclick' => 'App.modules.addFileForm.updateFileClick(\'' . $genid .'\')')) ?>
					<?php echo label_tag(lang('update file'), $genid .'fileFormUpdateFile', false, array('class' => 'checkbox'), '') ?>
				</div>
				<div id="<?php echo $genid ?>updateFileDescription">
					<p><?php echo lang('replace file description') ?></p>
				</div>
			<?php } // if ?>
			<div id="<?php echo $genid ?>updateFileForm"  style="<?php echo isset($checkin) ? '': 'display:none' ?>">
				<p>
					<strong><?php echo lang('existing file') ?>:</strong>
					<a target="_blank" href="<?php echo $file->getDownloadUrl() ?>"><?php echo clean($file->getFilename()) ?></a>
					| <?php echo format_filesize($file->getFilesize()) ?>
				</p>
				<div id="<?php echo $genid ?>selectFileControlDiv">
					<?php echo label_tag(lang('new file'), $genid.'fileFormFile', true) ?>
					<?php
						Hook::fire('render_upload_control', array(
							"genid" => $genid,
							"attributes" => array(
								"id" => $genid . "fileFormFile",
								"tabindex" => "65",
							)
						), $ret);
					?>
				</div>
				<div id="<?php echo $genid ?>revisionControls">
					<div>
						<?php echo checkbox_field('file[version_file_change]', array_var($file_data, 'version_file_change', true), array('id' => $genid.'fileFormVersionChange', 'class' => 'checkbox', 'tabindex' => '70', 'onclick' => 'App.modules.addFileForm.versionFileChangeClick(\'' . $genid .'\')')) ?>
						<?php echo label_tag(lang('version file change'), 'fileFormVersionChange', false, array('class' => 'checkbox'), '') ?>
					</div>
					<div id="<?php echo $genid ?>fileFormRevisionCommentBlock">
						<?php echo label_tag(lang('revision comment'), $genid.'fileFormRevisionComment', $comments_required) ?>
						<?php echo textarea_field('file[revision_comment]', array_var($file_data, 'revision_comment'), array('class' => 'long', 'tabindex' => '75', 'id' => $genid.'fileFormRevisionComment')) ?>
						<input type='hidden' id ="<?php echo $genid ?>RevisionCommentsRequired" value="<?php echo $comments_required? '1':'0'?>"/>
					</div>
				</div>
			</div>
			<?php }else{ ?>
		    	<div id="<?php echo $genid ?>fileFormRevisionCommentBlock">
		   			<?php // echo textarea_field('file[revision_comment]', '', array('id' => $genid.'fileFormRevisionComment')) ?>
		   			<input type='hidden' id ="<?php echo $genid ?>RevisionCommentsRequired" value="0"/>
		        </div>
		   	<?php } ?>
			<?php if (!isset($checkin) && $file->getType() == ProjectFiles::TYPE_DOCUMENT) {?>
				<script type="text/javascript">
					App.modules.addFileForm.updateFileClick('<?php echo $genid ?>');
					App.modules.addFileForm.versionFileChangeClick('<?php echo $genid ?>');
				</script>
			<?php } // if ?>
		</div>
	<?php } // if type add / edit ?>
<?php } // if enableupload ?>



	<div id="<?php echo $genid ?>add_file_select_workspace_div" style="display: none">
		<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_upload_file_workspace_context_help', true, logged_user()->getId())) {?>
			<div id="uploadFileContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp upload file workspace','upload_file_workspace'); ?>
			</div>
		<?php }?>
			<legend><?php echo lang('workspace') ?></legend>
			<?php if ($file->isNew()) {
				echo select_workspaces('ws_ids', null, array($project), $genid.'ws_ids');
			} else {
				echo select_workspaces('ws_ids', null, $file->getWorkspaces(), $genid.'ws_ids');
			} ?>
			<?php if (!$file->isNew()) {?>
				<div id="<?php echo $genid ?>addFileFilenameCheck" style="display: none">
					<h2><?php echo lang("checking filename") ?></h2>
				</div>
				<div id="<?php echo $genid ?>addFileUploadingFile" style="display: none">
					<h2><?php echo lang("uploading file") ?></h2>
				</div>
				<div id="<?php echo $genid ?>addFileFilenameExists" style="display: none">
					<h2><?php echo lang("duplicate filename")?></h2>
					<?php echo lang("filename exists edit") ?>
				</div>
			<?php } // if ?>
		</fieldset>
	</div>

	<div id="<?php echo $genid ?>add_file_tags_div" style="display: none">
		<fieldset>
			<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
					if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_upload_file_tags_context_help', true, logged_user()->getId())) {?>
			<div id="uploadFileContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp upload file tags','upload_file_tags'); ?>
			</div>
		<?php }?>
			<legend><?php echo lang('tags') ?></legend>
			<?php echo autocomplete_tags_field("file[tags]", array_var($file_data, 'tags'), null, 85); ?>
		</fieldset>
	</div>

	<div id="<?php echo $genid ?>add_file_description_div" style="display: none">
		<fieldset>
			<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
						if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_upload_file_description_context_help', true, logged_user()->getId())) {?>
			<div id="uploadFileContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp upload file description','upload_file_description'); ?>
			</div>
		<?php }?>
		<legend><?php echo lang('description') ?></legend>
		<?php echo textarea_field('file[description]', array_var($file_data, 'description'), array('class' => 'long', 'id' => $genid.'fileFormDescription', 'tabindex' => '90')) ?>
		</fieldset>
	</div>

	<?php if(logged_user()->isMemberOfOwnerCompany()) { ?>
		<div id="<?php echo $genid ?>add_file_options_div" style="display: none">
		<fieldset>
			<legend><?php echo lang('options') ?></legend>
			<?php /*
			<div class="objectOption">
				<div class="optionLabel"><label><?php echo lang('private file') ?>:</label></div>
				<div class="optionControl"><?php echo yes_no_widget('file[is_private]', 'fileFormIsPrivate', array_var($file_data, 'is_private'), lang('yes'), lang('no')) ?></div>
				<div class="optionDesc"><?php echo lang('private file desc') ?></div>
			</div>

			<div class="objectOption">
				<div class="optionLabel"><label><?php echo lang('important file') ?>:</label></div>
				<div class="optionControl"><?php echo yes_no_widget('file[is_important]', 'fileFormIsImportant', array_var($file_data, 'is_important'), lang('yes'), lang('no')) ?></div>
				<div class="optionDesc"><?php echo lang('important file desc') ?></div>
			</div>
			*/?>
			<div class="objectOption">
				<div class="optionLabel"><label><?php echo lang('enable comments') ?>:</label></div>
				<div class="optionControl"><?php echo yes_no_widget('file[comments_enabled]', $genid.'fileFormEnableComments', array_var($file_data, 'comments_enabled', true), lang('yes'), lang('no'), 95) ?></div>
				<div class="optionDesc"><?php echo lang('enable comments desc') ?></div>
			</div>
			<div class="objectOption">
				<div class="optionLabel"><label><?php echo lang('enable anonymous comments') ?>:</label></div>
				<div class="optionControl"><?php echo yes_no_widget('file[anonymous_comments_enabled]', $genid.'fileFormEnableAnonymousComments', array_var($file_data, 'anonymous_comments_enabled', false), lang('yes'), lang('no'), 100) ?></div>
				<div class="optionDesc"><?php echo lang('enable anonymous comments desc') ?></div>
			</div>
		</fieldset>
		</div>
	<?php } // if ?>

	<div id="<?php echo $genid ?>add_custom_properties_div" style="display: none">
		<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
						if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_upload_file_custom_properties_context_help', true, logged_user()->getId())) {?>
			<div id="uploadFileContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp upload file custom properties','upload_file_custom_properties'); ?>
			</div>
		<?php }?>
			<legend><?php echo lang('custom properties') ?></legend>
			<?php echo render_object_custom_properties($object, 'ProjectFiles', false) ?><br/><br/>
      		<?php echo render_add_custom_properties($object); ?>
		</fieldset>
	</div>

	<div id="<?php echo $genid ?>add_subscribers_div" style="display: none">
		<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
					if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_upload_file_subscribers_context_help', true, logged_user()->getId())) {?>
			<div id="uploadFileContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp upload file subscribers','upload_file_subscribers'); ?>
			</div>
		<?php }?>
			<legend><?php echo lang('object subscribers') ?></legend>
			<div id="<?php echo $genid ?>add_subscribers_content">
				<?php echo render_add_subscribers($object, $genid); ?>
			</div>
		</fieldset>
	</div>

	<script>
		var wsch = Ext.getCmp('<?php echo $genid ?>ws_ids');
		wsch.on("wschecked", function(arguments) {
			var uids = App.modules.addMessageForm.getCheckedUsers('<?php echo $genid ?>');
			Ext.get('<?php echo $genid ?>add_subscribers_content').load({
				url: og.getUrl('object', 'render_add_subscribers', {
					workspaces: this.getValue(),
					users: uids,
					genid: '<?php echo $genid ?>',
					object_type: '<?php echo get_class($object->manager()) ?>'
				}),
				scripts: true
			});
		}, wsch);
	</script>

	<?php if($object->isNew() || $object->canLinkObject(logged_user(), $project)) { ?>
		<div style="display: none" id="<?php echo $genid ?>add_linked_objects_div">
		<fieldset>
			<?php 
			$show_help_option = user_config_option('show_context_help', 'until_close'); 
						if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_upload_file_linked_objects_context_help', true, logged_user()->getId())) {?>
			<div id="uploadFileContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp upload file linked objects','upload_file_linked_objects'); ?>
			</div>
		<?php }?>
			<legend><?php echo lang('linked objects') ?></legend>
			<?php echo render_object_link_form($object) ?>
		</fieldset>
		</div>
	<?php } // if ?>

	<?php foreach ($categories as $category) { ?>
		<div <?php if (!$category['visible']) echo 'style="display:none"' ?> id="<?php echo $genid . $category['name'] ?>">
		<fieldset>
			<legend><?php echo lang($category['name'])?><?php if ($category['required']) echo ' <span class="label_required">*</span>'; ?></legend>
			<?php echo $category['content'] ?>
		</fieldset>
		</div>
	<?php } ?>
	
	<div>
		<?php echo render_object_custom_properties($object, 'ProjectFiles', true) ?>
	</div><br/>

	<div id="<?php echo $genid ?>fileSubmitButton" style="display: inline">
		<input type="hidden" name="upload_id" value="<?php echo $genid ?>" />
		<?php
			if (!$file->isNew()) { //Edit file
				if (isset($checkin) && $checkin) {
					echo submit_button(lang('checkin file'),'s',array("id" => $genid.'add_file_submit2', 'tabindex' => '200', "onclick" => 'og.fileCheckSubmit(\'' . $genid .'\'); return false;'));
				} else {
					echo submit_button(lang('save changes'),'s',array("id" => $genid.'add_file_submit2', 'tabindex' => '200', "onclick" => 'og.fileCheckSubmit(\'' . $genid .'\'); return false;'));
				}
			} else { //New file
				echo submit_button(lang('add file'),'s',array("id" => $genid.'add_file_submit2', 'tabindex' => '200', "onclick" => 'og.fileCheckSubmit(\'' . $genid .'\'); return false;'));
			}
		?>
	</div>

</div>
</div>
</form>

<script type="text/javascript">
	var ctl = Ext.get('<?php echo $genid ?>fileFormFile');
	if (ctl) ctl.focus();
</script>