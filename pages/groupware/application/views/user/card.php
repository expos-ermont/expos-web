<?php 

  // Set page title and set crumbs to index
  if($user->canUpdateProfile(logged_user())) {
  	add_page_action(lang('update profile'),$user->getEditProfileUrl(), 'ico-edit');
  	add_page_action(lang('update avatar'), $user->getUpdateAvatarUrl(), 'ico-picture');
  	add_page_action(lang('change password'), $user->getEditPasswordUrl(), 'ico-password');
  	$contact = $user->getContact();
  	if($contact)  	 
  		add_page_action(lang('go to contact'),  get_url('contact','card',array('id'=>$contact->getId())), 'ico-contact');
  	else
  		add_page_action(lang('create contact from user'), "javascript:if(confirm('" . lang('confirm create contact from user') . "')) og.openLink('" . $user->getCreateContactFromUserUrl() ."');", 'ico-add');
  } // if
  if($user->getId() == logged_user()->getId()){
  	 add_page_action(lang('edit preferences'), $user->getEditPreferencesUrl(), 'ico-administration');
  }
  if($user->canUpdatePermissions(logged_user())) {
    add_page_action(lang('permissions'), $user->getUpdatePermissionsUrl(), 'ico-permissions');
  } // if

?>



<div style="padding:7px">
<div class="user">
<?php

	tpl_assign('title', clean($user->getDisplayName()));
	tpl_assign('show_linked_objects', false);
	tpl_assign('object', $user);
	tpl_assign('iconclass', 'ico-large-user');
	tpl_assign("content_template", array('user_card', 'user'));
	
	$this->includeTemplate(get_template_path('view', 'co'));
?>
</div>
</div>