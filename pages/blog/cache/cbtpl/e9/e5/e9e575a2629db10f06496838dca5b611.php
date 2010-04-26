<div id="top">
  <h1><span><a href="<?php echo context::global_filter($core->blog->url,0,0,0,0,0,'BlogURL'); ?>"><?php echo context::global_filter($core->blog->name,1,0,0,0,0,'BlogName'); ?></a></span></h1>

  <?php if ($core->hasBehavior('publicTopAfterContent')) { $core->callBehavior('publicTopAfterContent',$core,$_ctx);} ?>
</div>

<p id="prelude"><a href="#main"><?php echo __('To content'); ?></a> |
<a href="#blognav"><?php echo __('To menu'); ?></a> |
<a href="#search"><?php echo __('To search'); ?></a></p>