<?php
if (isset($object) && $object instanceof ProjectWebpage) {
	add_page_action(lang('open weblink'), clean($object->getUrl()), "ico-open-link", "_blank");
	if (!$object->isTrashed()){
		if($object->canEdit(logged_user())) {
			add_page_action(lang('edit'), $object->getEditUrl(), 'ico-edit');
		} // if
	}
	if($object->canDelete(logged_user())) {
		if ($object->isTrashed()) {
			add_page_action(lang('restore from trash'), "javascript:if(confirm(lang('confirm restore objects'))) og.openLink('" . $object->getUntrashUrl() ."');", 'ico-restore');
			add_page_action(lang('delete permanently'), "javascript:if(confirm(lang('confirm delete permanently'))) og.openLink('" . $object->getDeletePermanentlyUrl() ."');", 'ico-delete');
		} else {
			add_page_action(lang('move to trash'), "javascript:if(confirm(lang('confirm move to trash'))) og.openLink('" . $object->getTrashUrl() ."');", 'ico-trash');
		}
	} // if
?>

<div style="padding:7px">
<div class="weblink">
	<?php
		$description = convert_to_links(nl2br(clean($object->getDescription())));
		$url = clean($object->getUrl());
		tpl_assign("description", "<b>".lang("url").": </b><a target=\"_blank\" href=\"$url\">$url</a>");
		tpl_assign("desc", $description);
		tpl_assign("content_template", array('view_content', 'webpage'));
		tpl_assign("object", $object);
		tpl_assign('iconclass', $object->isTrashed()? 'ico-large-weblink-trashed' :  'ico-large-weblink');
		
		$this->includeTemplate(get_template_path('view', 'co'));
	?>
</div>
</div>
<?php } //if isset ?>
