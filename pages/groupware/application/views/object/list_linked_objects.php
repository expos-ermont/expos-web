<?php 
if ($linked_objects_object->isNew()){
	$linked_objects = array();
	echo render_link_to_new_object(lang('link more objects'));
} else if (isset($linked_objects) && is_array($linked_objects) && count($linked_objects)) { ?>
	<div class="objectFiles">
	<div class="objectFilesTitle"><span><?php echo lang('linked objects') ?>:</span></div>
	<table style="width:100%;margin-left:2px;margin-right:3px">
	<?php $counter = 0;
	foreach ($linked_objects as $linked_object) {
		if( !$linked_object instanceof ApplicationDataObject ) continue ; //check that it is a valid object
		if  ($linked_object instanceof Contact){ // if it is a contact
			if (!can_manage_contacts(logged_user() ) ) continue; // check permissions on contacts 			
		} else { // not a contact
			if (!can_read(logged_user(), $linked_object ) )  //check permissions on other COs
					continue; 
		}
		$counter++;?>
		<tr class="linkedObject<?php echo $counter % 2 ? 'even' : 'odd' ?>">
		<td rowspan=2 style="padding-left:1px;vertical-align:middle;width:22px">
		<?php $attr = 'class="internalLink"'; ?>
		<a <?php echo $attr ?> href="<?php echo $linked_object->getObjectUrl() ?>">
		<div class="db-ico unknown ico-<?php echo clean($linked_object->getObjectTypeName()) ?>" title="<?php echo clean($linked_object->getObjectTypeName()) ?>"></div>
		</a></td><td><b><a <?php echo $attr ?> href="<?php echo $linked_object->getObjectUrl() ?>">
		<span><?php echo clean($linked_object->getObjectName()) ?></span> </a></b> </td></tr>
		<tr class="linkedObject<?php echo $counter % 2 ? 'even' : 'odd' ?>"><td>
		<?php if ($linked_object instanceof ProjectFile){ ?>
			<a target="_blank" href="<?php echo $linked_object->getDownloadUrl() ?>"><?php echo lang('download') . ' (' . format_filesize($linked_object->getFilesize()) . ')'?></a> | 
		<?php }
		if ($linked_object instanceof ProjectWebpage) { ?>
			<a class="internalLink" href="<?php echo $linked_object->getUrl() ?>"><?php echo lang('open weblink')?></a> |
		<?php }
		if ($linked_objects_object->canUnlinkObject(logged_user(), $linked_object)) { 
			echo '<a class="internalLink" href="' . $linked_objects_object->getUnlinkObjectUrl($linked_object) . '" onclick="return confirm(\'' . escape_single_quotes(lang('confirm unlink object')) . '\')" title="' . lang('unlink object') . '">' . lang('unlink') . '</a>';
		} ?>
		</td></tr>
	<?php 	} // foreach ?>
	</table>
	<?php 		
	if ($linked_objects_object->canLinkObject(logged_user()) && $enableAdding) { ?>
		<p><?php echo render_link_to_object($linked_objects_object,lang('link more objects')); ?> </p>
		<!--a class="internalLink" href="<?php echo $linked_objects_object->getLinkObjectUrl() ?>">&raquo; <?php echo lang('link more objects') ?></a-->
	<?php } // if ?>
	</div>
<?php } else {
	//echo $shortDisplay ? '' : lang('no linked objects').'.';
	if ((!($linked_objects_object->isNew())) && $linked_objects_object->canLinkObject(logged_user()) && $enableAdding) {
		echo render_link_to_object($linked_objects_object,lang('link objects'));
	} // if
} // if ?>