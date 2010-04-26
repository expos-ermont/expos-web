<?php
  set_page_title(lang('search results'));
  $showContext = false; //TODO Implement context support before setting this to true
  $genid = gen_id();
  $search_all_projects = array_var($_GET, 'search_all_projects', 'false');
  $has_search_results = isset($search_results) && is_array($search_results) && count($search_results);
?>
<div id="<?php echo $genid; ?>Search" style='height:100%;background-color:white'>
<div style='background-color:white'>
<div id="searchForm">
  <form class="internalForm" action="<?php echo get_url('search','search') ?>" method="get">
    <?php echo input_field('search_for', array_var($_GET, 'search_for')) ?>
    <input type="hidden" name="c" value="search" />
    <input type="hidden" name="a" value="search" />
    <?php echo submit_button(lang('search')) ?>
  </form>
</div>

<div id="headerDiv" class="searchDescription">
<?php if (array_var($_GET, 'search_all_projects') != 'true' && active_project() instanceof Project) 
		echo lang("search for in project", clean($search_string), clean(active_project()->getName()));
	else
		echo lang("search for", clean($search_string)); 
	if ($has_search_results && array_var($_GET, 'search_all_projects') != 'true' && active_project() instanceof Project) { ?>
	<br/><a class="internalLink" href="<?php echo get_url('search','search',array("search_for" => array_var($_GET, 'search_for'), "search_all_projects" => "true" )) ?>"><?php echo lang('search in all workspaces') ?></a>
<?php } //if ?>
</div>




<div style="padding-left:10px;padding-right:10px"><?php 

if($has_search_results) {
	foreach($search_results as $search_result) { 
		$alt = true;
		$pagination = $search_result["pagination"];?>
	<div class="searchGroup">
	<table width="100%"><tr><td align=center>
	<div class="searchHeader">
		<table width="100%"><tr><td><a class="coViewAction ico-<?php echo $search_result["icontype"]?> internalLink searchGroupTitle" href='<?php echo get_url('search', 'searchbytype', 
		array('manager' => $search_result["manager"], 'search_for' => $search_string, 'search_all_projects' => $search_all_projects)); ?>'><?php echo $search_result["type"]?></a></td>
		<td align=right><?php if (isset($enable_pagination) && $pagination->getTotalItems() > $pagination->getItemsPerPage()) {?>
			<?php echo advanced_pagination($pagination, get_url('search', 
				'searchbytype',
					array('active_project' => (active_project())?active_project()->getId():'',
					'search_for' => $search_string, 'manager' => $search_result["manager"],
					'page' => '#PAGE#', 'search_all_projects' => $search_all_projects)), 'search_pagination'); ?>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<?php echo lang('search result description short', $pagination->getStartItemNumber(),$pagination->getEndItemNumber() , $pagination->getTotalItems(), clean($search_string)) ?>
		<?php } // if ?>
		<?php if (!isset($enable_pagination) && $pagination->countItemsOnPage(1) < $pagination->getTotalItems()) { ?>
			<a class="internalLink" href='<?php echo get_url('search', 'searchbytype', 
			array('manager' => $search_result["manager"], 'search_for' => $search_string, 'search_all_projects' => $search_all_projects)); ?>'>
			<?php echo lang('more results', $pagination->getTotalItems() - $pagination->countItemsOnPage(1)) ?></a>
		<?php } else echo "" ?>
		</td></tr>
		</table>
	</div>
	<div class="searchResults">
	<table style="width:100%">
	<?php foreach($search_result['result'] as $srrow) {
		$alt = !$alt;
		$result = $srrow['object'];?>
		<tr style="vertical-align:middle" class="<?php echo $alt? "searchAltRow" : 'searchRow' ?>">
			<td style="padding:6px" rowspan=<?php echo $showContext ? 2 : 1 ?> width=36>
		<?php if ($search_result["manager"] == 'ProjectFiles' || $search_result["manager"] == 'ProjectFileRevisions') {?>
			<img style="width:36px" src="<?php echo $result->getTypeIconUrl() ?>"/>
		<?php } ?>
		<?php if ($search_result["manager"] == 'Contacts') {?>
			<img style="width:36px" src="<?php echo $result->getPictureUrl() ?>"/>
		<?php } ?>
		<?php if ($search_result["manager"] == 'Users') {?>
			<img style="width:36px" src="<?php echo $result->getAvatarUrl() ?>"/>
		<?php } ?></td>
		<td style="padding:6px;vertical-align:middle"><?php if ($result instanceof ProjectDataObject){
			$dws = $result->getWorkspaces();
			$projectLinks = array();
			foreach ($dws as $ws) {
				$projectLinks[] = $ws->getId();
			echo '<span style="padding-right:5px"><span class="project-replace">' . implode(',',$projectLinks)  . '</span></span>';
		}}?><?php if ($search_result["manager"] == 'Projects') {?>
			<span class="project-replace" onclick="Ext.getCmp('tabs-panel').setActiveTab('overview-panel')"><?php echo $result->getId() ?></span>
		<?php } else { ?>
			<a class="internalLink" href="<?php echo $result->getObjectUrl() ?>"><?php echo clean($result->getObjectName()) ?></a>
		<?php } // if ?>
		</td>
		<td style="padding:6px;vertical-align:middle" align=right><?php echo lang("modified by on short", $result->getUpdatedByCardUrl(), ($result->getUpdatedBy() instanceof User ? clean($result->getUpdatedByDisplayName()) : clean($result->getCreatedByDisplayName())), format_descriptive_date($result->getObjectUpdateTime())) ?></td>
		</tr>
	<?php } // foreach row ?>
	</table>
	</div>
	</td></tr></table>
	</div>
 <?php } // foreach group?>

<?php } else { ?>
<div id="noResultsFoundDiv" class="searchDescription" style="font-weight:normal;font-size:140%;padding-top:30px; padding-bottom:30px">
<?php echo lang('no search result for', clean($search_string)) ?>
<?php if (array_var($_GET, 'search_all_projects') != 'true' && active_project() instanceof Project) { ?>
<br/>
<a class="internalLink" href="<?php echo get_url('search','search',array("search_for" => array_var($_GET, 'search_for'), "search_all_projects" => "true" )) ?>"><?php echo lang('search in all workspaces') ?></a>
<?php } //if ?>
</div>
<?php } // if ?>

<div style="width:100%;text-align:center;color:#888;padding-bottom:20px">
	<br/>
	<p><?php echo lang('time used in search', sprintf("%01.2f",$time)) ?></p>
</div>
</div>
</div>
</div>
<script type="text/javascript">
og.showWsPaths('<?php echo $genid; ?>Search',true);
</script>