<?php
	$object = $user;
    set_page_title(lang('update profile'));

	$genid = gen_id();
?>
<form style="height:100%;background-color:white" class="internalForm" action="<?php echo $user->getEditProfileUrl($redirect_to) ?>" method="post">


<div class="adminEditProfile">
  <div class="adminHeader">
  	<div class="adminHeaderUpperRow">
  		<div class="adminTitle"><table style="width:535px"><tr><td>
  			<?php echo lang('update profile') ?>
  		</td><td style="text-align:right">
  			<?php echo submit_button(lang('save changes'), 's', array('style'=>'margin-top:0px;margin-left:10px', 'tabindex' => '1100')) ?>
  		</td></tr></table></div>
  	</div>
  
    <div>
      <?php echo label_tag(lang('display name'), 'profileFormDisplayName') ?>
      <?php echo text_field('user[display_name]', array_var($user_data, 'display_name'), 
    	  array('id' => 'profileFormDisplayName', 'tabindex' => '1000', 'class' => 'title')) ?>
    </div>
  
  	<?php $categories = array(); Hook::fire('object_edit_categories', $object, $categories); ?>
  
  	<div style="padding-top:5px">
		<?php if(logged_user()->isAdministrator()) { ?>
			<a href="#" class="option" tabindex=1010 onclick="og.toggleAndBolden('<?php echo $genid ?>update_profile_administrator_options',this)"><?php echo lang('administrator options') ?></a> - 
		<?php } // if ?>
		<?php if (logged_user()->isAdministrator() && isset($billing_categories) && count($billing_categories) > 0) {?>
			<a href="#" class="option" tabindex=1010 onclick="og.toggleAndBolden('<?php echo $genid ?>update_profile_billing',this)"><?php echo lang('billing') ?></a> - 
		<?php } // if ?>
		<a href="#" class="option" tabindex=1020 onclick="og.toggleAndBolden('<?php echo $genid ?>update_profile_timezone',this)"><?php echo lang('timezone') ?></a>
		<?php foreach ($categories as $category) { ?>
			- <a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid . $category['name'] ?>', this)"><?php echo lang($category['name'])?></a>
		<?php } ?>
	</div>
  
  </div>
  <div class="adminSeparator"></div>
  <div class="adminMainBlock">

<?php if(logged_user()->isAdministrator()) { ?>

  <div id="<?php echo $genid ?>update_profile_administrator_options" style="display:none">
  <fieldset>
    <legend><?php echo lang('administrator update profile notice') ?></legend>
    <div class="content">
      <div>
        <?php echo label_tag(lang('username'), 'profileFormUsername', true) ?>
        <?php echo text_field('user[username]', array_var($user_data, 'username'), 
        array('id' => 'profileFormUsername', 'tabindex' => '2000')) ?>
      </div>
      
      <div>
        <?php echo label_tag(lang('company'), 'userFormCompany', true) ?>
        <?php echo select_company('user[company_id]', array_var($user_data, 'company_id'), 
        array('id' => 'userFormCompany', 'tabindex' => '2100')) ?>
      </div>
      
<?php if($company->isOwner()) { ?>
     <?php if($user->getId() != 1) /* System admin cannot change admin status */ {?>
      <fieldset>
        <legend><?php echo lang('options') ?></legend>
        
	        <div>
	          <?php echo label_tag(lang('is administrator'), null, true) ?>
	          <?php echo yes_no_widget('user[is_admin]', 'userFormIsAdmin', array_var($user_data, 'is_admin'), lang('yes'), lang('no'), '2200') ?>
	        </div>     
	         </fieldset>
        <?php } ?>
        
        <!-- div>
          <?php echo label_tag(lang('is auto assign'), null, true) ?>
          <?php echo yes_no_widget('user[auto_assign]', 'userFormAutoAssign', array_var($user_data, 'auto_assign'), lang('yes'), lang('no'), '2300') ?>
        </div -->

<?php } else { ?>
      <input type="hidden" name="user[is_admin]" value="0" />
      <input type="hidden" name="user[auto_assign]" value="0" />
<?php } // if ?>
    </div>
    </fieldset>
  </div>
<?php } else { ?>
  <div>
    <?php echo label_tag(lang('username')) ?>
    <?php echo clean(array_var($user_data, 'username')) ?>
    <input type="hidden" name="user[username]" value="<?php echo clean(array_var($user_data, 'username')) ?>" />
  </div>
<?php } // if ?>

<?php if (logged_user()->isAdministrator() && isset($billing_categories) && count($billing_categories) > 0) {?>
  <div id="<?php echo $genid ?>update_profile_billing" style="display:none">
<fieldset>
	<legend><?php echo lang('billing') ?></legend>
<?php 
	$options = array(option_tag(lang('select billing category'),0,($user->getDefaultBillingId() == 0?array('selected' => 'selected'):null)));
	foreach ($billing_categories as $category){
		$options[] = option_tag($category->getName(),$category->getId(),($category->getId()==$user->getDefaultBillingId())?array('selected' => 'selected'):null);	
	}
    echo label_tag(lang('billing category'), null, false);
	echo select_box('user[default_billing_id]',$options,array('id' => 'userDefaultBilling'))
?>
</fieldset>
</div>
<?php } //if ?>

   
  <div id="<?php echo $genid ?>update_profile_timezone" style="display:none">
  <fieldset>
  	<legend><?php echo lang('timezone')?></legend>
   	<?php echo select_timezone_widget('user[timezone]', array_var($user_data, 'timezone'), array('id' => 'profileFormTimezone', 'class' => 'title', 'tabindex' => '2500')) ?>
  </fieldset>
  </div>
  
  <?php foreach ($categories as $category) { ?>
	<div style="display:none" id="<?php echo $genid . $category['name'] ?>">
	<fieldset>
		<legend><?php echo lang($category['name'])?></legend>
		<?php echo $category['content'] ?>
	</fieldset>
	</div>
	<?php } ?>

  <div>
    <?php echo label_tag(lang('email address'), 'profileFormEmail', true) ?>
    <?php echo text_field('user[email]', array_var($user_data, 'email'), 
    	array('id' => 'profileFormEmail', 'tabindex' => '2700', 'class' => 'long')) ?>
  </div>
  
  
  <div>
    <?php echo label_tag(lang('user title'), 'profileFormTitle') ?>
    <?php echo text_field('user[title]', array_var($user_data, 'title'), 
    	array('id' => 'profileFormTitle', 'tabindex' => '2800')) ?>
  </div>
  
  <?php echo submit_button(lang('save changes'),'s',array('tabindex' => '3000')) ?>

</div>
</div>
</form>

<script type="text/javascript">
	Ext.get('profileFormDisplayName').focus();
</script>