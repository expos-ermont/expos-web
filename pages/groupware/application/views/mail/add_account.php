<?php
  $genid = gen_id(); 
  set_page_title($mailAccount->isNew() ? lang('add mail account') : lang('edit mail account'));
  if (!$mailAccount->isNew() && $mailAccount->canDelete(logged_user()))
  	add_page_action(lang('delete mail account'),  "javascript:og.promptDeleteAccount(".$mailAccount->getId().");", 'ico-delete');
?>

<form style="height:100%;background-color:white" class="internalForm" action="<?php echo $mailAccount->isNew() ? get_url('mail', 'add_account') : $mailAccount->getEditUrl()?>" method="post">

<div class="adminAddMailAccount">
  <div class="adminHeader">
  	<div class="adminHeaderUpperRow">
  		<div class="adminTitle"><table style="width:535px"><tr><td>
  			<?php echo $mailAccount->isNew() ? lang('new mail account') : lang('edit mail account') ?>
  		</td><td style="text-align:right">
  			<?php echo submit_button($mailAccount->isNew() ? lang('add mail account') : lang('save changes'), '', 
  			array('style'=>'margin-top:0px;margin-left:10px', 'tabindex'=>'301')) ?>
  		</td></tr></table>
  		</div>
  	</div>
  
  <div>
    <label for='mailAccountFormName'><?php echo lang('mail account name')?> <span class="label_required">*</span>  
    <span class="desc"><?php echo lang('mail account name description') ?></span></label>
    <?php echo text_field('mailAccount[name]', array_var($mailAccount_data, 'name'), 
    	array('class' => 'title', 'tabindex'=>'10', 'id' => 'mailAccountFormName')) ?>
  </div>
  
  </div>
  <div class="adminSeparator"></div>
  <div class="adminMainBlock">
  
   <div>
    <label for="mailAccountFormEmail"><?php echo lang('mail address')?> <span class="label_required">*</span>
    <span class="desc"><?php echo lang('mail address description') ?></span></label>
    <?php echo text_field('mailAccount[email_addr]', array_var($mailAccount_data, 'email_addr'), 
    array('id' => 'mailAccountFormEmail', 'tabindex'=>'20')) ?>
  </div>

  <div>
    <label for="<?php echo $genid ?>email"><?php echo lang('mail account id')?> <span class="label_required">*</span>
    <span class="desc"><?php echo lang('mail account id description') ?></span></label>
    <?php echo text_field('mailAccount[email]', array_var($mailAccount_data, 'email'), 
    array('id' => $genid.'email', 'tabindex'=>'30')) ?>
  </div>

  <div>
    <label for="<?php echo $genid?>password"><?php echo lang('password')?> <span class="label_required">*</span>
    <span class="desc"><?php echo lang('mail account password description') ?></span></label>
    <?php echo password_field('mailAccount[password]', array_var($mailAccount_data, 'password'), 
    array('id' => $genid.'password', 'tabindex'=>'40')) ?>
  </div>

  <div>
    <label for="<?php echo $genid ?>server"><?php echo lang('server address')?> <span class="label_required">*</span>
    <span class="desc"><?php echo lang('mail account server description') ?></span></label>
    <?php echo text_field('mailAccount[server]', array_var($mailAccount_data, 'server'), 
    array('id' => $genid.'server', 'tabindex'=>'50')) ?>
  </div>

  <div>
    <label for="mailAccountDelMailFromServer"><?php echo lang('delete mails from server')?>
    <span class="desc"><?php echo lang('mail account delete mails from server description') ?></span></label>
    <?php echo yes_no_widget('mailAccount[del_mails_from_server]', 'mailAccountDelMailFromServer', array_var($mailAccount_data, 'del_from_server', 0) > 0, lang('yes'), lang('no'), 60) ?>
    <?php echo '<br>' . lang('after')?>
    <?php echo text_field('mailAccount[del_from_server]', array_var($mailAccount_data, 'del_from_server', 0), array('id' => 'mailAccountDelFromServer', 'tabindex'=>'70', 'style'=>'width:25px')) ?>
    <?php echo lang('days'); ?>
  </div>
  
<fieldset><legend><?php echo lang('smtp settings')?></legend>
  <div>
    <label for="mailSmtpServer"><?php echo lang('smtp server')?> <span class="label_required">*</span>
    <span class="desc"><?php echo lang('mail account smtp server description') ?></span></label>
    <?php echo text_field('mailAccount[smtp_server]', array_var($mailAccount_data, 'smtp_server'), 
    array('id' => 'mailSmtpServer', 'tabindex'=>'80')) ?>
  </div>

  <div>
    <label for="mailSmtpPort"><?php echo lang('smtp port')?> <span class="label_required">*</span>
    <span class="desc"><?php echo lang('mail account smtp port description') ?></span></label>
    <?php echo text_field('mailAccount[smtp_port]', array_var($mailAccount_data, 'smtp_port',25), 
    array('id' => 'mailSmtpPort', 'tabindex'=>'90')) ?>
  </div>

  <div>
    <label for="mailSmtpUseAuth"><?php echo lang('smtp use auth')?> <span class="label_required">*</span>
    <span class="desc"><?php echo lang('mail account smtp use auth description') ?></span></label>
    <?php 
    $use_auth = array_var($mailAccount_data, 'smtp_use_auth',1);
	$options = array(
			option_tag(lang('no smtp auth'), 0, ($use_auth==0)?array('selected' => 'selected'):null),
			option_tag(lang('same as incoming'), 1, ($use_auth==1)?array('selected' => 'selected'):null),
			option_tag(lang('smtp specific'), 2, ($use_auth==2)?array('selected' => 'selected'):null)
		);
	echo select_box('mailAccount[smtp_use_auth]', $options, 
		array('id' => 'mailSmtpUseAuth', 'tabindex'=>'100',
		'onchange' => "if(document.getElementById('mailSmtpUseAuth').selectedIndex ==2) document.getElementById('smtp_specific_auth').style.display = 'block'; else document.getElementById('smtp_specific_auth').style.display = 'none';"));
    ?>
  </div>

  <div id = 'smtp_specific_auth' style='<?php if(array_var($mailAccount_data, 'smtp_use_auth',1)!=2) echo 'display:none';?>'>
  <div>
    <label for="mailSmtpUsername"><?php echo lang('smtp username')?> <span class="label_required"></span>
    <span class="desc"><?php echo lang('mail account smtp username description') ?></span></label>
    <?php echo text_field('mailAccount[smtp_username]', array_var($mailAccount_data, 'smtp_username'), 
    array('id' => 'mailSmtpUsername', 'tabindex'=>'110')) ?>
  </div>

  <div>
    <label for="mailSmtpPassword"><?php echo lang('smtp password')?> <span class="label_required"></span>
    <span class="desc"><?php echo lang('mail account smtp password description') ?></span></label>
    <?php echo password_field('mailAccount[smtp_password]', array_var($mailAccount_data, 'smtp_password'), 
    array('id' => 'mailSmtpPassword', 'tabindex'=>'120')) ?>
  </div>
  </div>
  
  <div>
    <label for="mailOutgoingTransportType"><?php echo lang('outgoing transport type')?> <span class="label_required">*</span>
    <span class="desc"><?php echo lang('mail account outgoing transport type description') ?></span></label>
    <?php 
    $ottype = array_var($mailAccount_data, 'outgoing_transport_type', '');
	$t_options = array(
			option_tag(lang('no'), '', ($ottype=='')?array('selected' => 'selected'):null),
			option_tag('SSL', 'ssl', ($ottype=='ssl')?array('selected' => 'selected'):null),
			option_tag('TLS', 'tls', ($ottype=='tls')?array('selected' => 'selected'):null)
		);
	echo select_box('mailAccount[outgoing_transport_type]', $t_options, 
		array('id' => 'mailOutgoingTransportType', 'tabindex'=>'130', 'onchange' => ""));
    ?>
  </div>
</fieldset>

<fieldset style="display:block">
	<legend><?php echo lang('email connection method')?></legend>
  <div><?php
    $options = array();
    $attributes = array();
    if (array_var($mailAccount_data, 'is_imap', false)) {
    	$attributes['selected'] = "selected";
    }
  	$options[] = option_tag(lang('imap'), '1', $attributes);
  	$attributes = array();
    if (!array_var($mailAccount_data, 'is_imap', false)) {
    	$attributes['selected'] = "selected";
    }
  	$options[] = option_tag(lang('pop3'), '0', $attributes);
  	$onchange = "var fld = document.getElementById('$genid' + 'folders'); var ssl = document.getElementById('$genid' + 'sslport'); if (this.value == 1) { fld.style.display = 'block'; ssl.value = '993'; } else { fld.style.display = 'none'; ssl.value = '995'; }"; 
    echo select_box('mailAccount[is_imap]', $options, array("onchange" => $onchange, 'tabindex' => '145'));
    ?>
  </div><br/>
  <div> <?php
  	$onchange = "var div = document.getElementById('$genid' + 'sslportdiv');if(this.checked) div.style.display='block';else div.style.display='none';";
    echo checkbox_field('mailAccount[incoming_ssl]', array_var($mailAccount_data, 'incoming_ssl'), array('id' => $genid.'ssl', 'tabindex'=>'150', 'onchange' => $onchange)) ?>
    <label for="<?php echo $genid ?>ssl" class="yes_no"><?php echo lang('incoming ssl') ?></label>
  </div>
  <div id="<?php echo $genid ?>sslportdiv" <?php if (!array_var($mailAccount_data, 'incoming_ssl')) echo 'style="display:none"'; ?>>
    <?php echo label_tag(lang('incoming ssl port'), 'mailAccountFormIncomingSslPort') ?>
    <?php echo text_field('mailAccount[incoming_ssl_port]', array_var($mailAccount_data, 'incoming_ssl_port', array_var($mailAccount_data, 'is_imap', false) ? 993 : 995), array('id' => $genid.'sslport', 'tabindex'=>'160')) ?>
  </div>
  <div id="<?php echo $genid ?>folders" style="padding:5px;<?php if (!array_var($mailAccount_data, 'is_imap', false)) echo 'display:none'; ?>">
	  
	  <div id="<?php echo $genid ?>imap_folders">
	    <?php
	    tpl_assign('imap_folders', isset($imap_folders) ? $imap_folders : array());
	    tpl_assign('genid', $genid);
	    tpl_display(get_template_path("fetch_imap_folders", "mail"))
	    ?>
	  </div>
  </div>
</fieldset>
  <?php echo submit_button($mailAccount->isNew() ? lang('add mail account') : lang('save changes'), 's', array('tabindex'=>'300')) ?>

</div>
</div>
</form>

<script type="text/javascript">
	Ext.get('mailAccountFormName').focus();
	
	account_id = 0;
	
	og.promptDeleteAccount = function(account_id) {
		var check_id = Ext.id();
		var config = {
			genid: Ext.id(),
			title: lang('confirm delete mail account'),
			height: 150,
			width: 250,
			labelWidth: 150,
			ok_fn: function() {
				var checked = Ext.getCmp(check_id).getValue();
				og.openLink(og.getUrl('mail', 'delete_account', {
					id: account_id,
					deleteMails: checked ? 1 : 0
				}));
				og.ExtendedDialog.hide();
			},
			dialogItems: {
				xtype: 'checkbox',
				fieldLabel: lang('delete account emails'),
				id: check_id,
				value: false
			}
		};
		og.ExtendedDialog.show(config);
	}
</script>

