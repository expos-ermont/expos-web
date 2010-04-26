<?php 
  set_page_title(lang('administration'));
?>
<div class="adminIndex" style="height:100%;background-color:white">
  <div class="adminHeader">
  	<div class="adminTitle"><?php echo lang('administration') ?></div>
  </div>
  <div class="adminSeparator"></div>
  <div class="adminMainBlock">
    <?php echo lang('welcome to administration info') ?>
    <br/>
    <br/>
    <?php 
		$show_help_option = user_config_option('show_context_help', 'until_close'); 
		if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_administration_context_help', true, logged_user()->getId()))) {?>
		<div id="administrationPanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
			<?php render_context_help($this, 'chelp administrator panel','administration'); ?>
		</div>
	<?php }?>
<div style="width:100%;max-width:700px; text-align:center">
    <table><tr>
<?php if(can_edit_company_data(logged_user())){ ?>
<td align="center">
    <div style="width:150px;display:block; margin-right:10px;margin-bottom:40px">
    <table width="100%" align="center"><tr><td align="center">
    	<a class="internalLink" href="<?php echo get_url('administration', 'company') ?>"><div class="coViewIconImage ico-large-company"></div></a>
    </td></tr><tr><td align="center"><b><a class="internalLink" href="<?php echo get_url('administration', 'company') ?>"><?php echo lang('owner company') ?></a></b>
    </td></tr></table>
    </div>
</td>
<?php } ?>

<?php if(can_manage_security(logged_user())){ ?>
<td align="center">
    <div style="width:150px;display:block; margin-right:10px;margin-bottom:40px">
    <table width="100%" align="center"><tr><td align="center">
    	<a class="internalLink" href="<?php echo get_url('administration', 'clients') ?>"><div class="coViewIconImage ico-large-company"></div></a>
    </td></tr><tr><td align="center"><b><a class="internalLink" href="<?php echo get_url('administration', 'clients') ?>"><?php echo lang('client companies') ?></a></b>
    	<br/><a class="internalLink coViewAction ico-add" href="<?php echo get_url('company', 'add_client') ?>"><?php echo lang('add client') ?></a>
    	</td></tr></table>
    </div>
</td>
<?php } ?>

<?php if(can_edit_company_data(logged_user())){ ?>
<td align="center">
    <div style="width:150px;display:block; margin-right:10px;margin-bottom:40px">
    <table width="100%" align="center"><tr><td align="center">
    	<a class="internalLink" href="<?php echo get_url('administration', 'members') ?>"><div class="coViewIconImage ico-large-user"></div></a>
    </td></tr><tr><td align="center"><b><a class="internalLink" href="<?php echo get_url('administration', 'members') ?>"><?php echo lang('users') ?></a></b>
    	<br/><a class="internalLink coViewAction ico-add" href="<?php echo owner_company()->getAddUserUrl() ?>"><?php echo lang('add user') ?></a>
    </td></tr></table>
    </div>
</td>
<?php } ?>

<?php if(can_manage_security(logged_user())){ ?>
<td align="center">
    <div style="width:150px;display:block; margin-right:10px;margin-bottom:40px">
    <table width="100%" align="center"><tr><td align="center">
    	<a class="internalLink" href="<?php echo get_url('administration', 'groups') ?>"><div class="coViewIconImage ico-large-group"></div></a>
    </td></tr><tr><td align="center"><b><a class="internalLink" href="<?php echo get_url('administration', 'groups') ?>"><?php echo lang('groups') ?></a></b>
    	<br/><a class="internalLink coViewAction ico-add" href="<?php echo owner_company()->getAddGroupUrl() ?>"><?php echo lang('add group') ?></a>
    </td></tr></table>
    </div>
</td>
<?php } ?>

</tr></table>


<table>
<tr>
<?php if(can_manage_workspaces(logged_user())){ ?>
<td align="center">
    <div style="width:150px;display:block; margin-right:10px;margin-bottom:40px">
    <table width="100%" align="center"><tr><td align="center">
    	<a class="internalLink" href="<?php echo get_url('administration', 'projects') ?>"><div class="coViewIconImage ico-large-workspace"></div></a>
    </td></tr><tr><td align="center"><b><a class="internalLink" href="<?php echo get_url('administration', 'projects') ?>"><?php echo lang('projects') ?></a></b>
    	<br/><a class="internalLink coViewAction ico-add" href="<?php echo get_url('project', 'add') ?>"><?php echo lang('add project') ?></a>
    </td></tr></table>
    </div>
</td>
<td align="center">
    <div style="width:150px;display:block; margin-right:10px;margin-bottom:40px">
    <table width="100%" align="center"><tr><td align="center">
    	<a class="internalLink" href="<?php echo get_url('template', 'index') ?>"><div class="coViewIconImage ico-large-template"></div></a>
    </td></tr><tr><td align="center"><b><a class="internalLink" href="<?php echo get_url('template', 'index') ?>"><?php echo lang('templates') ?></a></b>
    	<br/><a class="internalLink coViewAction ico-add" href="<?php echo get_url('template','add') ?>"><?php echo lang('add template') ?></a>
    </td></tr></table>
    </div>
</td>
<?php } ?>
<?php if(can_manage_security(logged_user())){ ?>
<td align="center">
    <div style="width:150px;display:block; margin-right:10px;margin-bottom:40px">
    <table width="100%" align="center"><tr><td align="center">
    	<a class="internalLink" href="<?php echo get_url('billing', 'index') ?>"><div class="coViewIconImage ico-large-billing"></div></a>
    </td></tr><tr><td align="center"><b><a class="internalLink" href="<?php echo get_url('billing', 'index') ?>"><?php echo lang('billing') ?></a></b>
    	<br/><a class="internalLink coViewAction ico-add" href="<?php echo get_url('billing', 'add') ?>"><?php echo lang('add billing category') ?></a>
    </td></tr></table>
    </div>
</td>
<?php } ?>

<?php if(can_manage_configuration(logged_user())){ ?>
<td align="center">
    <div style="width:150px;display:block; margin-right:10px;margin-bottom:40px">
    <table width="100%" align="center"><tr><td align="center">
    	<a class="internalLink" href="<?php echo get_url('administration', 'custom_properties') ?>"><div class="coViewIconImage ico-large-custom-properties"></div></a>
    </td></tr><tr><td align="center"><b><a class="internalLink" href="<?php echo get_url('administration', 'custom_properties') ?>"><?php echo lang('custom properties') ?></a></b>
    	
    </td></tr></table>
    </div>
</td>
<?php } ?>

</tr></table>

<table>
<tr>
<?php if(can_manage_configuration(logged_user())){ ?>
<td align="center">
    <div style="width:150px;display:block; margin-right:10px;margin-bottom:40px">
    <table width="100%" align="center"><tr><td align="center">
    	<a class="internalLink" href="<?php echo get_url('administration', 'configuration') ?>"><div class="coViewIconImage ico-large-configuration"></div></a>
    </td></tr><tr><td align="center"><b><a class="internalLink" href="<?php echo get_url('administration', 'configuration') ?>"><?php echo lang('configuration') ?></a></b>
    </td></tr></table>
    </div>
</td>
<td align="center">
    <div style="width:150px;display:block; margin-right:10px;margin-bottom:40px">
    <table width="100%" align="center"><tr><td align="center">
    	<a class="internalLink" href="<?php echo get_url('administration', 'tools') ?>"><div class="coViewIconImage ico-large-tools"></div></a>
    </td></tr><tr><td align="center"><b><a class="internalLink" href="<?php echo get_url('administration', 'tools') ?>"><?php echo lang('administration tools') ?></a></b>
    </td></tr></table>
    </div>
</td>
<td align="center">
    <div style="width:150px;display:block; margin-right:10px;margin-bottom:40px">
    <table width="100%" align="center"><tr><td align="center">
    	<a class="internalLink" href="<?php echo get_url('administration', 'upgrade') ?>"><div class="coViewIconImage ico-large-upgrade"></div></a>
    </td></tr><tr><td align="center"><b><a class="internalLink" href="<?php echo get_url('administration', 'upgrade') ?>"><?php echo lang('upgrade') ?></a></b>
    </td></tr></table>
    </div>
</td>
<td align="center">
    <div style="width:150px;display:block; margin-right:10px;margin-bottom:40px">
    <table width="100%" align="center"><tr><td align="center">
    	<a class="internalLink" href="<?php echo get_url('administration', 'cron_events') ?>"><div class="coViewIconImage ico-large-cron"></div></a>
    </td></tr><tr><td align="center"><b><a class="internalLink" href="<?php echo get_url('administration', 'cron_events') ?>"><?php echo lang('cron events') ?></a></b>
    </td></tr></table>
    </div>
</td>

<?php if(logged_user()->isAccountOwner()){ ?>
<td align="center">
    <div style="width:150px;display:block; margin-right:10px;margin-bottom:40px">
    <table width="100%" align="center"><tr><td align="center">
    	<a class="internalLink" href="<?php echo get_url('backup') ?>"><div class="coViewIconImage ico-large-backup"></div></a>
    </td></tr><tr><td align="center"><b><a class="internalLink" href="<?php echo get_url('backup') ?>"><?php echo lang('backup') ?></a></b>
    </td></tr></table>
    </div>
</td>
<?php } } ?>
</tr></table>

<?php $icons = array();
Hook::fire('render_administration_icons', null, $icons);
if (count($icons > 0)) {?>
<table><tr>
<?php $count = 0;
foreach ($icons as $icon) { 
	$count++;
	if ($count % 5 == 0) { ?>
		</tr><tr>
	<?php } ?>
<td align="center">
    <div style="width:150px;display:block; margin-right:10px;margin-bottom:40px">
    <table width="100%" align="center"><tr><td align="center">
    	<a class="internalLink" href="<?php echo $icon['url'] ?>"><div class="coViewIconImage <?php echo $icon['ico']?>"></div></a>
        </td></tr><tr><td align="center"><b><a class="internalLink" href="<?php echo $icon['url'] ?>"><?php echo $icon['name'] ?></a></b>
    <?php if (isset($icon['extra'])) { ?>
    </td></tr><tr><td align="center"><?php echo $icon['extra']; ?>
    <?php } ?>
    </td></tr></table>
    </div>
</td>
<?php } ?>
</tr></table>
<?php } ?>

</div>
    
  </div>
</div>