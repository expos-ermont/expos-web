<?php
  set_page_title(lang('backup menu'));
  if(can_manage_configuration(logged_user()) && can_manage_security(logged_user())) { ?>

<div class="adminClients" style="height:100%;background-color:white">
  <div class="adminHeader">
  	<div class="adminTitle"><?php echo lang('backup menu') ?></div>
  </div>
  <div class="adminSeparator"></div>
  <div class="adminMainBlock">
  
<div id="administrationTools">
	<div class="administrationToolDesc">
		<?php echo lang('backup process desc');?>
		<?php echo lang('backup config warning');?>
	</div><br>
    <div class="administrationToolName">
    	<?php 
    	 echo ($has_backup ? (lang('last backup') . ': <b>' . ($last_backup ? $last_backup : lang('unknown')).' </b>') : '<b>' .lang('no backups') . '</b>') ;
		?>
    </div>
<br>

  <div class="administrationTool">
    <div class="administrationToolName">
      <h2><a class="internalLink" href="<?php echo get_url('backup','launch') ?>"><?php echo lang('start backup') ?></a></h2>
    </div>
    <div class="administrationToolDesc"><?php echo lang('start backup desc') ?></div>
  </div>
  <div class="administrationTool">
    <div class="administrationToolName">
      <h2><?php if($has_backup) {
	      		echo '<a target="_blank" href="' . get_url('backup','download'). '">';
	      		echo lang('download backup') ;
	      		echo '</a>';
      		}
      		else 
      			echo '<br><br>';
      	?>
      	</h2>
    </div>
    <div class="administrationToolDesc"><?php echo lang('download backup desc') ?></div>
  </div>
  <div class="administrationTool">
  <?php if($has_backup) { ?>
    <div class="administrationToolName">
      <h2><a class="internalLink" href="<?php echo get_url('backup','delete') ?>"><?php echo lang('delete backup') ?></a></h2>
    </div>
    <div class="administrationToolDesc"><?php echo lang('delete backup desc') ?></div>
  <?php } ?>
  </div>
</div>
</div>
<?php } ?>
