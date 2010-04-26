<?php
require_javascript("og/ObjectPicker.js");
require_javascript("modules/addFileForm.js");
?>

<script type="text/javascript">
	og.pickObjectToZip = function(zip_id) {
		og.ObjectPicker.show(function(objs) {
				if (objs.length < 1) return;
				if (objs[0].data.manager != 'ProjectFiles') {
					og.msg(lang("error"), lang("must choose a file"));
					return;
				}
				obj_ids = '';
				for(i=0; i<objs.length; i++) {
					obj_ids += (obj_ids == '' ? '' : ',') + objs[i].data.object_id;
				}
				og.openLink(og.getUrl('files', 'zip_add', {id:zip_id, objects:obj_ids})); 
			}, this, {});
	}
</script>

<?php
if (isset($file) && $file instanceof ProjectFile) {
	$options = array();

	if (!$file->isTrashed()){
		if ($file && strcmp($file->getTypeString(), 'prsn')==0) {
			add_page_action(lang('slideshow'), "javascript:og.slideshow(".$file->getId().")", 'ico-slideshow');
		}
		
		if ($file && strcmp($file->getTypeString(), 'audio/mpeg')==0) {
			$songname = $file->getProperty("songname");
			$artist = $file->getProperty("songartist");
			$album = $file->getProperty("songalbum");
			$track = $file->getProperty("songtrack");
			$year = $file->getProperty("songyear");
			$duration = $file->getProperty("songduration");
			$songdata = "['" . str_replace("'", "\\'", $songname) . "','" . str_replace("'", "\\'", $artist) . "','" . str_replace("'", "\\'", $album) . "','" . str_replace("'", "\\'", $track) . "','" . str_replace("'", "\\'", $year) . "','" . str_replace("'", "\\'", $duration) . "','" . $file->getDownloadUrl() ."','" . str_replace("'", "\\'", $file->getFilename()) . "'," . $file->getId() . "]";
			add_page_action(lang('play'), "javascript:og.playMP3(" . $songdata . ")", 'ico-play');
			add_page_action(lang('queue'), "javascript:og.queueMP3(" . $songdata . ")", 'ico-queue');
		} else if ($file && strcmp($file->getTypeString(), 'application/xspf+xml')==0) {
			add_page_action(lang('play'), "javascript:og.playXSPF(" . $file->getId() . ")", 'ico-play');
		}
	}
	
	if($file->canDownload(logged_user()) && $file->getType() != ProjectFiles::TYPE_WEBLINK) { 
		$url = $file->getDownloadUrl();
		if (config_option('checkout_notification_dialog')) { 
			$checkedOutById = $file->getCheckedOutById();
			if($checkedOutById != 0){
				$checkedOutByName = ($checkedOutById == logged_user()->getId() ?  "self" : Users::findById($checkedOutById)->getUsername());
			}else{
				$checkedOutByName = '';
			}
			add_page_action(lang('download') . ' (' . format_filesize($file->getFilesize()) . ')', "javascript:og.checkDownload('$url',$checkedOutById,'$checkedOutByName');", 'ico-download', '', array("download" => true));
		} else {
			add_page_action(lang('download') . ' (' . format_filesize($file->getFilesize()) . ')', $url, 'ico-download', '_blank');
		}
	}
	
	if($file->getType() == ProjectFiles::TYPE_WEBLINK){
		add_page_action('Open weblink', clean($file->getUrl()), 'ico-open-link', '_blank');
	}
	
	if (!$file->isTrashed()){
		if ($file->isCheckedOut()){
			if ($file->canCheckin(logged_user()) && $file->getType() == ProjectFiles::TYPE_DOCUMENT){
				add_page_action(lang('checkin file'), $file->getCheckinUrl(), 'ico-checkin'); 
				add_page_action(lang('undo checkout'), $file->getUndoCheckoutUrl() . "&show=redirect", 'ico-undo'); 
			}
			
		} else {
			if ($file->canCheckout(logged_user()) && $file->getType() == ProjectFiles::TYPE_DOCUMENT) { 
				add_page_action(lang('checkout file'), $file->getCheckoutUrl(). "&show=redirect", 'ico-checkout');
			}
		}
		
		if($file->canEdit(logged_user())) {
			if ($file->isModifiable() && $file->getType() != ProjectFiles::TYPE_WEBLINK) { 
				add_page_action(lang('edit this file'), $file->getModifyUrl(), 'ico-edit');
			}
			if (file_is_zip($file->getTypeString(), get_file_extension($file->getFilename()))) {
				add_page_action(lang('extract'), get_url('files', 'zip_extract', array('id' => $file->getId())), 'ico-zip-extract');
				add_page_action(lang('add files to zip'), "javascript:og.pickObjectToZip({$file->getId()})", 'ico-zip-add');
			}
			
			add_page_action(lang('edit file properties'), $file->getEditUrl(), 'ico-properties');
		}
	}
		
	if($file->canDelete(logged_user())) {
		if ($file->isTrashed()) {
    		add_page_action(lang('restore from trash'), "javascript:if(confirm(lang('confirm restore objects'))) og.openLink('" . $file->getUntrashUrl() ."');", 'ico-restore');
    		add_page_action(lang('delete permanently'), "javascript:if(confirm(lang('confirm delete permanently'))) og.openLink('" . $file->getDeletePermanentlyUrl() ."');", 'ico-delete');
    	} else {
    		add_page_action(lang('move to trash'), "javascript:if(confirm(lang('confirm move to trash'))) og.openLink('" . $file->getTrashUrl() ."');", 'ico-trash');
    	}
	}
	
	if (can_add(logged_user(), active_or_personal_project(), 'ProjectFiles') && $file->getType() != ProjectFiles::TYPE_WEBLINK) {
		add_page_action(lang('copy file'), $file->getCopyUrl(), 'ico-copy');
	}

?>


<div style="padding:7px">
<div class="files">

<?php 
	$description = '';
  	if($last_revision instanceof ProjectFileRevision) { 
  		$description .= '<div id="fileLastRevision"><span class="propertyName">' . lang('last revision') . ':</span>'; 
		if($last_revision->getCreatedBy() instanceof User) {
      		$description .= lang('file revision info long', $last_revision->getRevisionNumber(), $last_revision->getCreatedBy()->getCardUrl(), clean($last_revision->getCreatedBy()->getDisplayName()), format_descriptive_date($last_revision->getCreatedOn()));
		} else {
			$description .= lang('file revision info short', $last_revision->getRevisionNumber(), format_descriptive_date($last_revision->getCreatedOn()));
		}
		$description .= "</div>";
	} // if
	
	if($file->isCheckedOut()) {
		$description .= '<div id="fileCheckedOutBy" class="coViewAction ico-locked">';
		if($file->getCheckedOutBy() instanceof User) {
			$description .= lang('file checkout info long', $file->getCheckedOutBy()->getCardUrl(), clean($file->getCheckedOutBy()->getDisplayName()), format_descriptive_date($file->getCheckedOutOn()). ", " . format_time($file->getCheckedOutOn()));
		} else {
			$description .= lang('file checkout info short', format_descriptive_date($file->getCheckedOutOn()). ", " . format_time($file->getCheckedOutOn()));
		} // if
		$description .= "</div>";
	} // if
	
	if ($file->getType() == ProjectFiles::TYPE_WEBLINK){
		$description .= '<div id="urlDiv"><b>' . lang('url') . '</b>: <a href="' . clean($file->getUrl()) . '" class="internalLink" target="_blank">' . clean($file->getUrl()) . '</a>';
		
	}

	if (!$file->isTrashed() && $file->getType() != ProjectFiles::TYPE_WEBLINK)
		tpl_assign('image', '<div><img src="' . $file->getTypeIconUrl(false) .'" alt="' . clean($file->getFilename()) . '" /></div>');
	tpl_assign('iconclass', $file->isTrashed()? 'ico-large-files-trashed' : ($file->getType() != ProjectFiles::TYPE_WEBLINK? 'ico-large-files':'ico-large-webfile'));
	tpl_assign('description', $description);
	tpl_assign('title', clean($file->getFilename()));
	tpl_assign("content_template", array('file_details_content', 'files'));
	tpl_assign('object', $file);

	$this->includeTemplate(get_template_path('view', 'co'));
?>
</div>
</div>
<?php } //if isset ?>

