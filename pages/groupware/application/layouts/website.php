<?php header ("Content-Type: text/html; charset=utf-8", true); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
	<!-- script src="http://www.savethedevelopers.org/say.no.to.ie.6.js"></script -->
	<title><?php echo clean(CompanyWebsite::instance()->getCompany()->getName()) . ' - ' . PRODUCT_NAME ?></title>
	<?php echo link_tag(with_slash(ROOT_URL)."favicon.ico", "rel", "shortcut icon") ?>
	<?php echo add_javascript_to_page("app.js") // loaded first because it's needed for translating?>
	<?php echo add_javascript_to_page(get_url("access", "get_javascript_translation")); ?>
	<?php //echo add_javascript_to_page(with_slash(ROOT_URL) . 'language/' . Localization::instance()->getLocale() . "/lang.js") ?>
	<?php echo meta_tag('content-type', 'text/html; charset=utf-8', true) ?>
<?php

	if (defined('COMPRESSED_CSS') && COMPRESSED_CSS) {
		echo stylesheet_tag('ogmin.css');
	} else {
		echo stylesheet_tag('website.css');
	}
	echo stylesheet_tag('custom.css');
	$css = array();
	Hook::fire('autoload_stylesheets', null, $css);
	foreach ($css as $c) {
		echo stylesheet_tag($c);
	}

	if (defined('COMPRESSED_JS') && COMPRESSED_JS) {
		$jss = array('ogmin.js');
	} else {
		$jss = include "javascripts.php";
	}
	Hook::fire('autoload_javascripts', null, $jss);
	if (defined('USE_JS_CACHE') && USE_JS_CACHE) {
		echo add_javascript_to_page(with_slash(ROOT_URL).'public/tools/combine.php?type=javascript&files='.implode(',', $jss));
	} else {
		foreach ($jss as $onejs) {
			echo add_javascript_to_page($onejs);
		}
	}
	?>
	<?php if (config_option("show_feed_links")) { ?>
		<link rel="alternate" type="application/rss+xml" title="<?php echo clean(owner_company()->getName()) ?> RSS Feed" href="<?php echo logged_user()->getRecentActivitiesFeedUrl() ?>" />
	<?php } ?>
</head>
<body id="body" <?php echo render_body_events() ?>>

<div id="loading">
	<img src="<?php echo get_image_url("layout/loading.gif") ?>" width="32" height="32" style="margin-right:8px;" align="absmiddle"/><?php echo lang("loading") ?>...
</div>

<div id="subWsExpander" onmouseover="clearTimeout(og.eventTimeouts['swst']);" onmouseout="og.eventTimeouts['swst'] = setTimeout('og.HideSubWsTooltip()', 2000);" style="display:none;top:10px"></div>

<?php echo render_page_javascript() ?>
<?php echo render_page_inline_js() ?>

<!-- header -->
<div id="header">
	<div id="headerContent">
	    <table class="headerLogoAndWorkspace"><tr><td style="width:60px">
			<div id="logodiv"></div>
		</td><td>
			<div id="wsCrumbsWrapper">
				<table><tr><td>
					<div id="wsCrumbsDiv">
						<div style="font-size:150%;display:inline;">
							<a href="#" style="display:inline;line-height:28px" onmouseover="og.expandSubWsCrumbs(0)"><?php echo lang('all') ?></a>
						</div>
					</div>
				</td><td>
					<div id="wsTagCrumbs"></div>
				</td></tr></table>
			</div>
		</td></tr></table>
		<div id="userboxWrapper"><?php echo render_user_box(logged_user()) ?></div>
		<div id="searchbox">
			<form class="internalForm" action="<?php echo ROOT_URL . '/index.php' ?>" method="get">
				<table><tr><td>
				<?php
				$search_field_default_value = lang('search') . '...';
				$search_field_attrs = array(
				'onfocus' => 'if (value == \'' . $search_field_default_value . '\') value = \'\'',
				'onblur' => 'if (value == \'\') value = \'' . $search_field_default_value . '\''); ?>
				<?php echo input_field('search_for', $search_field_default_value, $search_field_attrs) ?>
				</td><td id="searchboxSearch">
				<button type="submit"><?php echo lang('search button caption') ?></button>
				</td><td style="padding-left:10px"><div id="quickAdd" style="padding-top:1px"></div></td></tr></table>
				<input type="hidden" name="c" value="search" />
				<input type="hidden" name="a" value="search" />
				<input type="hidden" name="current" value="search" />
				<input type="hidden" id="hfVars" name="vars" value="dashboard" />
			</form>
		</div>
		<?php Hook::fire('render_page_header', null, $ret) ?>
	</div>
</div>
<!-- /header -->

<!-- footer -->
<div id="footer">
	<div id="copy">
		<?php if(is_valid_url($owner_company_homepage = owner_company()->getHomepage())) { ?>
			<?php echo lang('footer copy with homepage', date('Y'), $owner_company_homepage, clean(owner_company()->getName())) ?>
		<?php } else { ?>
			<?php echo lang('footer copy without homepage', date('Y'), clean(owner_company()->getName())) ?>
		<?php } // if ?>
	</div>
	<div id="productSignature"><?php echo product_signature() ?></div>
</div>
<!-- /footer -->

<script>
// OG config options
og.pageSize = <?php echo config_option('files_per_page', 10)?>;
og.timeFormat24 = <?php echo config_option('time_format_use_24', 0) ? 1 : 0 ?>;
og.hostName = '<?php echo ROOT_URL ?>';
og.maxUploadSize = <?php echo get_max_upload_size() ?>;
og.rememberGUIState = <?php echo user_config_option("rememberGUIState", 0) ? 1 : 0 ?>;
<?php if (user_config_option("rememberGUIState", 0)) { ?>
og.initialGUIState = <?php echo json_encode(GUIController::getState()) ?>;
<?php } ?>
<?php $initialWS = user_config_option('initialWorkspace', 0);
if ($initialWS === "remember") {
	$initialWS = user_config_option('lastAccessedWorkspace', 0);
}
?>
og.initialWorkspace = '<?php echo $initialWS ?>';
og.initialURL = '<?php echo ROOT_URL . "/?active_project=$initialWS&" . $_SERVER['QUERY_STRING'] ?>';
og.loggedUser = {
	id: <?php echo logged_user()->getId() ?>,
	username: <?php echo json_encode(logged_user()->getUsername()) ?>,
	displayName: <?php echo json_encode(logged_user()->getDisplayName()) ?>,
	isAdmin: <?php echo logged_user()->isAdministrator() ? 'true' : 'false' ?>
};
og.hasNewVersions = <?php
	if (config_option('upgrade_last_check_new_version', false) && logged_user()->isAdministrator()) {
		echo json_encode(lang('new OpenGoo version available', "#", "og.openLink(og.getUrl('administration', 'upgrade'))"));
	} else {
		echo "false";
	}
?>;
og.enableNotesModule = <?php echo config_option("enable_notes_module", 1) ? 1 : 0 ?>;
og.enableEmailModule = <?php echo config_option("enable_email_module", defined('SHOW_MAILS_TAB') && SHOW_MAILS_TAB) ? 1 : 0 ?>;
og.enableContactsModule = <?php echo config_option("enable_contacts_module", 1) ? 1 : 0 ?>;
og.enableCalendarModule = <?php echo config_option("enable_calendar_module", 1) ? 1 : 0 ?>;
og.enableDocumentsModule = <?php echo config_option("enable_documents_module", 1) ? 1 : 0 ?>;
og.enableTasksModule = <?php echo config_option("enable_tasks_module", 1) ? 1 : 0 ?>;
og.enableWeblinksModule = <?php echo config_option("enable_weblinks_module", 1) ? 1 : 0 ?>;
og.enableTimeModule = <?php echo config_option("enable_time_module", 1) ? 1 : 0 ?>;
og.enableReportingModule = <?php echo config_option("enable_reporting_module", 1) ? 1 : 0 ?>;
og.daysOnTrash = <?php echo config_option("days_on_trash", 0) ?>;
og.showCheckoutNotification  = <?php echo config_option('checkout_notification_dialog', 0) ? 1 : 0 ?>;
Ext.Ajax.timeout = <?php echo get_max_execution_time()*1100 // give a 10% margin to PHP's timeout ?>;
og.musicSound = new Sound();
og.systemSound = new Sound();

var quickAdd = new og.QuickAdd({renderTo:'quickAdd'});

setInterval(function() {
	og.openLink(og.getUrl('object', 'popup_reminders'), {
		hideLoading: true,
		hideErrors: true,
		preventPanelLoad: true
	});
}, 60000);

og.date_format = '<?php echo user_config_option('date_format', 'd/m/Y') ?>';
og.calendar_start_day = <?php echo user_config_option('start_monday') ? '1' : '0' ?>;

</script>
<?php include_once(Env::getLayoutPath("listeners"));?>
</body>
</html>
