

<?php if(isset($users) && is_array($users) && count($users)) { ?>
<div id="usersList">
<?php $counter = 0; 
  foreach($users as $user) {
	$counter++; ?>
  <div class="listedUser <?php echo $counter % 2 ? 'even' : 'odd' ?>">
    <div class="userAvatar"><img src="<?php echo $user->getAvatarUrl() ?>" alt="<?php echo clean($user->getDisplayName()) ?> <?php echo lang('avatar') ?>" /></div>
    <div class="userDetails">
      <div class="userName"><a class="internalLink" href="<?php echo $user->getCardUrl() ?>"><?php echo clean($user->getDisplayName()) ?></a></div> 
      
<?php if(isset($company) && $company && $company->isOwner()) { ?>
	<?php if ($user->isAdministrator()) { ?>
      	<div class="userIsAdmin"><span><?php echo lang('administrator') ?></span></div>
    <?php } ?>
      <!-- div class="userAutoAssign"><span><?php echo lang('auto assign') ?>:</span> <?php echo $user->getAutoAssign() ? lang('yes') : lang('no') ?></div -->
<?php } // if  ?>
<?php
  $options = array();
  //if($user->canEdit(logged_user())) $options[] = '<a class="internalLink" href="' . $user->getEditUrl() . '">' . lang('edit') . '</a>';
  if($user->canUpdateProfile(logged_user())) {
    $options[] = '<a class="internalLink" href="' . $user->getEditProfileUrl(/*$company->getViewUrl()*/) . '">' . lang('update profile') . '</a>';
    $options[] = '<a class="internalLink" href="' . $user->getEditPasswordUrl(/*$company->getViewUrl()*/) . '">' . lang('change password') . '</a>';
    $options[] = '<a class="internalLink" href="' . $user->getUpdateAvatarUrl(/*$company->getViewUrl()*/) . '">' . lang('update avatar') . '</a>';
  } // if
  if($user->canUpdatePermissions(logged_user())) {
    $options[] = '<a class="internalLink" href="' . $user->getUpdatePermissionsUrl(/*$company->getViewUrl()*/) . '">' . lang('permissions') . '</a>';
  } // if
  if($user->canDelete(logged_user())) {
    $options[] = '<a class="internalLink" href="' . $user->getDeleteUrl() . '" onclick="return confirm(\'' . escape_single_quotes(lang('confirm delete user')) . '\')">' . lang('delete') . '</a>';
  } // if
?>
      <div class="userOptions"><?php echo implode(' | ', $options) ?></div>
      <div class="clear"></div>
    </div>
  </div>  
<?php } // foreach ?>
</div>

<?php } else { ?>
<p><?php echo lang('no users in company') ; ?></p>
<?php } // if 
 	if(isset($company) && $company){
		echo  "<div style='padding:10px'><a href='" . $company->getAddUserUrl() . "' class='internalLink coViewAction ico-add'>" . lang('add user') . "</a></div>";
 	} ?>
