<div id="userbox">
	<?php echo lang('welcome back', clean($_userbox_user->getDisplayName())) ?> (<a href="<?php echo get_url('access', 'logout') ?>"><?php echo lang('logout') ?></a>) :
	<?php foreach ($_userbox_extra_crumbs as $crumb) {
		echo '<a class="internalLink"';
		if (isset($crumb['target'])) echo ' target="' . $crumb['target'] .'"';
		echo ' href="' . $crumb['url'] . '">';
		echo $crumb['text'];
		echo '</a> | ';
	} ?> 
	<?php if (logged_user()->isMemberOfOwnerCompany() && logged_user()->isAdministrator()) { ?>
		<a class="internalLink" target="administration" href="<?php echo get_url('administration', 'index') ?>"><?php echo lang('administration') ?></a> |
	<?php } ?>
	<a class="internalLink" target="account" href="<?php echo logged_user()->getAccountUrl() ?>"><?php echo lang('account') ?></a> |
	<a target="_blank" href="http://wiki.opengoo.org"><?php echo lang('help') ?></a>
</div>