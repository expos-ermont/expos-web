<?php
if (isset($message) && $message instanceof ProjectMessage) {
	if (!$message->isTrashed()){
		if($message->canEdit(logged_user())) {
			add_page_action(lang('edit'), $message->getEditUrl(), 'ico-edit');
		} // if
	}
	if($message->canDelete(logged_user())) {
		if ($message->isTrashed()) {
			add_page_action(lang('restore from trash'), "javascript:if(confirm(lang('confirm restore objects'))) og.openLink('" . $message->getUntrashUrl() ."');", 'ico-restore');
			add_page_action(lang('delete permanently'), "javascript:if(confirm(lang('confirm delete permanently'))) og.openLink('" . $message->getDeletePermanentlyUrl() ."');", 'ico-delete');
		} else {
			add_page_action(lang('move to trash'), "javascript:if(confirm(lang('confirm move to trash'))) og.openLink('" . $message->getTrashUrl() ."');", 'ico-trash');
		}
	} // if
	add_page_action(lang('print view'), $message->getPrintViewUrl(), "ico-print", "_blank");
?>

<div style="padding:7px">
<div class="message">
	<?php 
		$content = convert_to_links(nl2br(clean($message->getText())));
		if(trim($message->getAdditionalText())) {
    		$content .= '<div class="messageSeparator">' . lang('message separator') . '</div>' 
    			. nl2br(clean($message->getAdditionalText()));
		}
		
		tpl_assign("content", $content);
		tpl_assign("object", $message);
		tpl_assign('iconclass', $message->isTrashed()? 'ico-large-message-trashed' :  'ico-large-message');
		
		$this->includeTemplate(get_template_path('view', 'co'));
	?>
</div>
</div>
<?php } //if isset ?>
