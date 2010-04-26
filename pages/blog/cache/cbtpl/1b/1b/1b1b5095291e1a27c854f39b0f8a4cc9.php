<div id="footer">
	<p>
		<?php printf(__("Powered by %s"),"<a href=\"http://dotclear.org/\">Dotclear</a>"); ?><br />
		expos ermont &copy; 2006-2008<br />
		Baseball club Val d'Oise - Animations sportives
	</p>
</div>

<?php if ($core->hasBehavior('publicFooterContent')) { $core->callBehavior('publicFooterContent',$core,$_ctx);} ?>