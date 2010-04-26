<div id="top">
	<h1><span><a href="<?php echo context::global_filter($core->blog->url,0,0,0,0,0,'BlogURL'); ?>"><img src="<?php echo context::global_filter($core->blog->settings->themes_url."/".$core->blog->settings->theme,0,0,0,0,0,'BlogThemeURL'); ?>/../../../../picts/logo_expos.png" alt="<?php echo context::global_filter($core->blog->name,1,0,0,0,0,'BlogName'); ?>" /></a></span></h1>
  
	<ul>
		<li><a href="<?php echo context::global_filter($core->blog->settings->themes_url."/".$core->blog->settings->theme,0,0,0,0,0,'BlogThemeURL'); ?>/../../../actus.php" title="Actualités">Actus</a></li>
		<li><a href="<?php echo context::global_filter($core->blog->settings->themes_url."/".$core->blog->settings->theme,0,0,0,0,0,'BlogThemeURL'); ?>/../../../club.php" title="Club">Club</a></li>
		<li><a href="<?php echo context::global_filter($core->blog->settings->themes_url."/".$core->blog->settings->theme,0,0,0,0,0,'BlogThemeURL'); ?>/../../../boutique.php" title="Boutique">Boutique</a></li>
		<li><a href="<?php echo context::global_filter($core->blog->settings->themes_url."/".$core->blog->settings->theme,0,0,0,0,0,'BlogThemeURL'); ?>/../../../equipes.php" title="Equipes">Equipes</a></li>
		<li><a href="<?php echo context::global_filter($core->blog->settings->themes_url."/".$core->blog->settings->theme,0,0,0,0,0,'BlogThemeURL'); ?>/../../../medias.php" title="Photos">Photos</a></li>
		<li><a href="<?php echo context::global_filter($core->blog->settings->themes_url."/".$core->blog->settings->theme,0,0,0,0,0,'BlogThemeURL'); ?>/../../../blog/" title="Blog">Blog</a></li>
		<li><a href="<?php echo context::global_filter($core->blog->settings->themes_url."/".$core->blog->settings->theme,0,0,0,0,0,'BlogThemeURL'); ?>/../../../liens.php" title="Liens">Liens</a></li>
		<li><a href="<?php echo context::global_filter($core->blog->settings->themes_url."/".$core->blog->settings->theme,0,0,0,0,0,'BlogThemeURL'); ?>/../../../contact.php" title="Contact">Contact</a></li>
	</ul>

  <?php if ($core->hasBehavior('publicTopAfterContent')) { $core->callBehavior('publicTopAfterContent',$core,$_ctx);} ?>
</div>

<!--<p id="prelude"><a href="#main"><?php echo __('To content'); ?></a> |
<a href="#blognav"><?php echo __('To menu'); ?></a> |
<a href="#search"><?php echo __('To search'); ?></a></p>-->