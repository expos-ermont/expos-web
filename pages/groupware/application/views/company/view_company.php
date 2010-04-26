<?php
	if (!$company->isTrashed()){
		if(User::canAdd(logged_user(), $company)) {
			add_page_action(lang('add user'), $company->getAddUserUrl(), 'ico-add');
		} // if
		if(Contact::canAdd(logged_user(),active_or_personal_project())) {
			add_page_action(lang('add contact'), $company->getAddContactUrl(), 'ico-add');
		} // if
		if($company->canEdit(logged_user())) {
			add_page_action(lang('edit company'), $company->getEditUrl(), 'ico-edit');
			add_page_action(lang('edit company logo'), $company->getEditLogoUrl(), 'ico-picture');
			if(!$company->isOwner()) {
				add_page_action(lang('update permissions'), $company->getUpdatePermissionsUrl(), 'ico-properties');
			} // if
		} // if
	}
  
    if ($company->canDelete(logged_user())){
    	if ($company->isTrashed()) {
    		add_page_action(lang('restore from trash'), "javascript:if(confirm(lang('confirm restore objects'))) og.openLink('" . $company->getUntrashUrl() ."');", 'ico-restore');
    		add_page_action(lang('delete permanently'), "javascript:if(confirm(lang('confirm delete permanently'))) og.openLink('" . $company->getDeletePermanentlyUrl() ."');", 'ico-delete');
    	} else {
    		add_page_action(lang('move to trash'), "javascript:if(confirm(lang('confirm move to trash'))) og.openLink('" . $company->getTrashUrl() ."');", 'ico-trash');
    	}
    }
  

?>



<div style="padding:7px">
<div class="company">
<?php
	if(isset($title) && $title != '')
		tpl_assign('title', clean($title));
	tpl_assign('show_linked_objects', false);
	tpl_assign('object', $company);
	tpl_assign('iconclass', $company->isTrashed()? 'ico-large-company-trashed' :  'ico-large-company');
	tpl_assign("content_template", array('company_content', 'company'));
	
	$this->includeTemplate(get_template_path('view', 'co'));
?>
</div>
</div>

