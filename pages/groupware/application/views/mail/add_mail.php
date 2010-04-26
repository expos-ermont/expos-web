<?php
	require_javascript('modules/linkToObjectForm.js'); 
	require_javascript('og/ObjectPicker.js'); 
	
include("public/assets/javascript/fckeditor/fckeditor.php");
 
  set_page_title( lang('write mail'));
  
  $genid = gen_id();
 
  $instanceName = "fck" . $genid;
  $type = array_var($mail_data, 'type','plain');
  $object = $mail;
?>
<script type="text/javascript">
function setBody(iname) {
	var form = Ext.getDom(iname);
	if (Ext.getDom('format_html').checked){
		form['mail[body]'].value = FCKeditorAPI.GetInstance(iname).GetHTML();
	}
	else {
		form['mail[body]'].value = Ext.getDom('mailBody').value;
	}
	return true;
}

function FCKeditor_OnComplete(fck) {
	fck.ResetIsDirty();
	fck.Events.AttachEvent('OnSelectionChange', function(fck) {
		var p = og.getParentContentPanel(Ext.get(fck.Name));
		if (fck.IsDirty()) {
			Ext.getCmp(p.id).setPreventClose(true);
		} else {
			Ext.getCmp(p.id).setPreventClose(false);
		}
	});
}

function alertFormat(iname, opt) {
	var oEditor = FCKeditorAPI.GetInstance(iname);
	if(opt == 'plain'){
		Ext.MessageBox.confirm('Warning', '<?php echo escape_single_quotes(lang('switch format warn'))?>', function(btn){
			if (btn == 'yes') {
				var mailBody = Ext.getDom('mailBody')
				mailBody.style.display = 'block';				
				Ext.getDom('fck_editor').style.display= 'none';
				var oDOM = oEditor.EditorDocument ;
			    var iText ;
			
			    // The are two diffent ways to get the text (without HTML markups).
			    // It is browser specific.
			
			    if ( document.all ) {     // If Internet Explorer.			    
			      iText = oDOM.body.innerText ;
			    }
			    else{               // If Gecko.			    
			      var r = oDOM.createRange() ;
			      r.selectNodeContents( oDOM.body ) ;
			      iText = r.toString() ;
			    }	
				mailBody.value = iText;	
			}
			else{
				Ext.getDom('format_html').checked = true;
				Ext.getDom('format_plain').checked = false;
				Ext.getDom('mailBody').style.display= 'none';
				Ext.getDom('fck_editor').style.display= 'block';	
			}			
		});	
	} else {
		var mailBody = Ext.getDom('mailBody')
		mailBody.style.display= 'none';			
		Ext.getDom('fck_editor').style.display= 'block';			
		oEditor.SetHTML(mailBody.value);	
	}
}


function setDraft(val){
	var isDraft = Ext.getDom('isDraft');
	isDraft.value = val;
}

function setUpload(val){
	var isUpload = Ext.getDom('isUpload');
 	isUpload.value = val;
 }
 
function setDiscard(val){
	var the_id = Ext.getDom('id').value;
	document.frmMail.action = og.getUrl('mail', 'delete', {id:the_id, ajax:'true'});
}

</script>
<div id="main_div" style="height:100%;">
<form style="height:100%;background-color:white;" id="<?php echo $instanceName ?>" name="frmMail"  class="internalForm" action="<?php echo $mail->getSendMailUrl()?>" method="post"  onsubmit="return setBody('<?php echo $instanceName ?>')">
<input type="hidden" name="instanceName" value="<?php echo $instanceName ?>" />
<input type="hidden" name="mail[body]" value="" />
<input type="hidden" name="mail[isDraft]" id="isDraft" value="false" />
<input type="hidden" name="mail[id]" id="id" value="<?php echo  array_var($mail_data, 'id') ?>" />
<input type="hidden" name="mail[isUpload]" id="isUpload" value="false" />
<?php 

	tpl_display(get_template_path('form_errors'));
	$contacts = Contacts::instance()->getAllowedContacts();
    $allEmails = array();
    foreach ($contacts as $contact) {
    	if (trim($contact->getEmail()) != "") {
    		$allEmails[] = str_replace(",", " ", $contact->getFirstname() . ' ' . $contact->getLastname() . ' <' . $contact->getEmail() . '>');
    	}
    } 
?>


<div class="mail" id="mail_div" style="height:100%;">
<div class="coInputHeader" id="header_div">
	<div class="coInputHeaderUpperRow">
  		<div class="coInputTitle"><table style="width:535px"><tr><td>
  			<?php echo lang('send mail') ?>
  		</td><td style="text-align:right">
  			<?php echo submit_button(lang('send mail'), '', 
  			array('style'=>'margin-top:0px;margin-left:10px','onclick'=>"setDraft(false)"))?>
  		</td>
  		<td style="text-align:right">
  			<?php echo submit_button(lang('save')." ".lang('draft'), '', 
  			array('style'=>'margin-top:0px;margin-left:10px','onclick'=>"setDraft(true)")) ?>
  		</td>
  		<td style="text-align:right">
  			<?php
  			$strDisabled = array_var($mail_data, 'id') == ''?'disabled':'';
  			echo submit_button(lang('discard'), '', 
  			array('style'=>'margin-top:0px;margin-left:10px','onclick'=>"setDiscard(true)",$strDisabled=>'')) ?>
  		</td>
  		</tr></table>
  		</div>
  	</div>
  
	<div>
    	<label for='mailTo'><?php echo lang('mail to')?> <span class="label_required">*</span>  
    	</label>
    	<?php echo autocomplete_emailfield('mail[to]', array_var($mail_data, 'to'), $allEmails, '', 
    		array('class' => 'title', 'tabindex'=>'10', 'id' => 'mailTo'), false); ?>
	</div>
  
 	<div id="add_mail_CC" style="<?php  array_var($mail_data, 'cc')==''? print('display:none;'):print('')?>">
    	<label for="mailCC"><?php echo lang('mail CC')?> </label>
    	<?php echo autocomplete_emailfield('mail[CC]', array_var($mail_data, 'cc'), $allEmails, '', 
    		array('class' => 'title', 'tabindex'=>'20', 'id' => 'mailCC'), false); ?>
 	</div>
 	
 	<div id="add_mail_BCC" style="display:none;">
	    <label for="mailBCC"><?php echo lang('mail BCC')?></label>
	    <?php echo autocomplete_emailfield('mail[BCC]', array_var($mail_data, 'BCC'), $allEmails, '', 
    		array('class' => 'title', 'tabindex'=>'30', 'id' => 'mailBCC'), false); ?>
	</div>
 	
	<div>
    	<label for='mailSubject'><?php echo lang('mail subject')?> 
    	</label>
    	<?php echo text_field('mail[subject]', array_var($mail_data, 'subject'), 
    		array('class' => 'title', 'tabindex'=>'40', 'id' => 'mailSubject')) ?>
	</div>
		
	<div>
		<?php echo render_object_custom_properties($object, 'MailContents', true) ?>
	</div>
	
	<?php $categories = array(); Hook::fire('object_edit_categories', $object, $categories); ?>
	
	<div style="padding-top:5px">
		<a href="#" class="option" onclick="og.toggleAndBolden('add_mail_account', this);resizeMailDiv();"><?php echo lang('mail account') ?></a> - 
		<a href="#" class="option" onclick="og.toggleAndBolden('add_mail_CC', this);resizeMailDiv();"><?php echo lang('mail CC') ?></a> - 
		<a href="#" class="option" onclick="og.toggleAndBolden('add_mail_BCC', this);resizeMailDiv();"><?php echo lang('mail BCC') ?></a> - 
		<a href="#" class="option" onclick="og.toggleAndBolden('add_mail_options', this);resizeMailDiv();"><?php echo lang('mail options') ?></a> -
 		<?php if (defined('EMAIL_ATTACHMENTS') && EMAIL_ATTACHMENTS) {?><a href="#" class="option" onclick="og.toggleAndBolden('add_mail_attachments', this);resizeMailDiv();"><?php echo lang('mail attachments') ?></a> -<?php } // Disabled?>
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_custom_properties_div',this);resizeMailDiv();"><?php echo lang('custom properties') ?></a>
		<?php foreach ($categories as $category) { ?>
			- <a href="#" class="option" <?php if ($category['visible']) echo 'style="font-weight: bold"'; ?> onclick="og.toggleAndBolden('<?php echo $genid . $category['name'] ?>', this)"><?php echo lang($category['name'])?></a>
		<?php } ?>	
	</div>

	<div id="add_mail_account" style="display:none;">
	    <label for="mailAccount"><?php echo lang('mail account')?> 
	    <span class="desc"><?php echo lang('mail account desc') ?></span></label>
	    <?php echo render_select_mail_account('mail[account_id]',  $mail_accounts, '1',
	    array('id' => 'mailAccount', 'tabindex'=>'44')) ?>
	</div>
  
	<div id="add_mail_options" style="display:none;">
	    <label><?php echo lang('mail options')?></label>
	    <label><?php echo radio_field('mail[format]',$type=='html', array('id' => 'format_html','value' => 'html', 'tabindex'=>'45','onchange'=>"alertFormat('$instanceName','html')")) ." ".lang('format html') ?></label>
	    <label><?php echo radio_field('mail[format]',$type=='plain', array('id' => 'format_plain','value' => 'plain', 'tabindex'=>'46', 'onchange'=>"alertFormat('$instanceName','plain')"))." ".lang('format plain')  ?></label>
	</div>
	
	<?php if (defined('EMAIL_ATTACHMENTS') && EMAIL_ATTACHMENTS){?>
	<div id="add_mail_attachments" style="display:none;">
 	<fieldset>
 	    <legend><?php echo lang('mail attachments')?></legend>
 	<a id="<?php echo $genid ?>before" href="#" onclick="App.modules.linkToObjectForm.pickObject(this, {types:{Documents:true}})"><?php echo lang('attach from workspace') ?></a>
 	</fieldset>
 	</div>
 	<?php } // Disabled ?>

	<?php foreach ($categories as $category) { ?>
	<div <?php if (!$category['visible']) echo 'style="display:none"' ?> id="<?php echo $genid . $category['name'] ?>">
	<fieldset>
		<legend><?php echo lang($category['name'])?><?php if ($category['required']) echo ' <span class="label_required">*</span>'; ?></legend>
		<?php echo $category['content'] ?>
	</fieldset>
	</div>
	<?php } ?>
	
	<div id='<?php echo $genid ?>add_custom_properties_div' style="display:none">
		<fieldset>
			<legend><?php echo lang('custom properties') ?></legend>
			<?php echo render_object_custom_properties($object, 'MailContents', false) ?>
		</fieldset>
	</div>	
  
</div>
<div class="coInputSeparator"></div>

    <?php 
    $display=($type=='html')?'none':'block';
    $display_fck=($type=='html')?'block':'none';
    echo textarea_field('plain_body', array_var($mail_data, 'body'), 
    array('id' => 'mailBody', 'tabindex'=>'50','style'=>"display:".$display.";width:97%;height:94%;margin-left:1%;margin-right:1%;margin-top:1%;margin-bottom:1%;")) ?>
    <div id="fck_editor" style="display:<?php echo $display_fck ?>; width:100%; height:100%; padding:0px; margin:0px;">
		<?php
			$oFCKeditor = new FCKeditor($instanceName);
			$oFCKeditor->BasePath = 'public/assets/javascript/fckeditor/';
			$oFCKeditor->Width = '100%';
			$oFCKeditor->Height = '100%';
			$oFCKeditor->Config['SkinPath'] = get_theme_url('fckeditor/');
			$oFCKeditor->Value = nl2br(array_var($mail_data, 'body'));
			$oFCKeditor->ToolbarSet  = 'Basic' ;
			$oFCKeditor->Create();
		?>
	</div>
</div>
</form>
</div>

<script>
og.eventManager.addListener("email saved", function(obj) {
	var form = Ext.getDom(obj.instance);
	if (!form) return;
	form['mail[id]'].value = obj.id;
	var fck = FCKeditorAPI.GetInstance(obj.instance);
	if (fck) {
		fck.ResetIsDirty();
		var p = og.getParentContentPanel(Ext.get(fck.Name));
		Ext.getCmp(p.id).setPreventClose(false);
	}
}, null, {replace:true});

function resizeMailDiv() {
	maindiv = document.getElementById('main_div');
	headerdiv = document.getElementById('header_div');
	if (maindiv != null && headerdiv != null) {
		var divHeight = maindiv.offsetHeight - headerdiv.offsetHeight - 15;
		document.getElementById('mail_div').style.height = divHeight + 'px';
	}
}
resizeMailDiv();
window.onresize = resizeMailDiv;
</script>