<?php
	require_javascript("modules/addFileForm.js");
	include("public/assets/javascript/fckeditor/fckeditor.php");
	$genid = gen_id();
	$comments_required = config_option('file_revision_comments_required');
	$instanceName = "fck" . $genid;
?>

<form class="internalForm" style="height:100%" id="<?php echo $instanceName ?>" action="<?php echo get_url('files', 'save_document') ?>" method="post" enctype="multipart/form-data" onsubmit="return og.addDocumentSubmit('<?php echo $genid ?>');">
<input type="hidden" name="instanceName" value="<?php echo $instanceName ?>" />
<input type="hidden" id="<?php echo $genid ?>commentsRequired" value="<?php echo config_option('file_revision_comments_required')? '1':'0'?>"/>
<?php
	tpl_display(get_template_path('form_errors'));
	$oFCKeditor = new FCKeditor($instanceName);
	$oFCKeditor->BasePath = 'public/assets/javascript/fckeditor/';
	$oFCKeditor->Width = '100%';
	$oFCKeditor->Height = '100%';
	$oFCKeditor->Config['SkinPath'] = get_theme_url('fckeditor/');
	if($file->isNew()) {
		$oFCKeditor->Value = '';
	} else {
		$oFCKeditor->Value = $file->getFileContent();
	}
	$oFCKeditor->Create();
	
	if (config_option('checkout_for_editing_online')) {
		ajx_on_leave("og.openLink('" . get_url('files', 'release_file', array('id' => $file->getId())) . "')");
		add_page_action(lang("checkin file"), "javascript:(function(){ var form = document.getElementById('$instanceName'); form.checkin.value = '1'; form.new_revision_document.value = 'checked'; form.rename = false; form.onsubmit(); })()", "ico-checkin");
	}

	add_page_action(lang("save"), "javascript:(function(){ var form = document.getElementById('$instanceName'); form.new_revision_document.value = 'checked'; form.rename = false; form.onsubmit(); })()", "save");
	add_page_action(lang("save as"), "javascript:(function(){ var form = document.getElementById('$instanceName'); form.new_revision_document.value = 'checked'; form.rename = true; form.onsubmit(); })()", "save_as");
?>

 	<div>
		<input type="hidden" id="fileContent" name="fileContent" value="" />
		<input type="hidden" id="fileid" name="file[id]" value="<?php if (!$file->isNew()) echo $file->getId(); ?>" />
		<input type="hidden" id="filename" name="file[name]" value="<?php if (!$file->isNew()) echo clean($file->getFilename()); ?>" />
		<input type="hidden" id ="<?php echo $genid ?>comment" name="file[comment]" value="" />
		<input type="hidden" name="new_revision_document" value="" />
		<input type="hidden" name="checkin" value="" />
	</div>
</form>

<script>
function FCKeditor_OnComplete(fck) {
	fck.ResetIsDirty();
	fck.Events.AttachEvent('OnSelectionChange', function(fck) {
		var p = og.getParentContentPanel(Ext.get(fck.Name));
		Ext.getCmp(p.id).setPreventClose(fck.IsDirty());
	});
}

og.eventManager.addListener("document saved", function(obj) {
	var form = Ext.getDom(obj.instance);
	if (!form) return;
	form['file[id]'].value = obj.id;
	form['file[comment]'].value = '';
	var fck = FCKeditorAPI.GetInstance(obj.instance);
	if (fck) {
		fck.ResetIsDirty();
		var p = og.getParentContentPanel(Ext.get(fck.Name));
		Ext.getCmp(p.id).setPreventClose(false);
	}
}, null, {replace:true});
</script>
