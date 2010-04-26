<?php
if($chart->canDelete(logged_user())) {
		if ($chart->isTrashed()) {
    		add_page_action(lang('restore from trash'), "javascript:if(confirm(lang('confirm restore objects'))) og.openLink('" . $chart->getUntrashUrl() ."');", 'ico-restore');
    		add_page_action(lang('delete permanently'), "javascript:if(confirm(lang('confirm delete permanently'))) og.openLink('" . $chart->getDeletePermanentlyUrl() ."');", 'ico-delete');
    	} else {
    		add_page_action(lang('move to trash'), "javascript:if(confirm(lang('confirm move to trash'))) og.openLink('" . $chart->getTrashUrl() ."');", 'ico-trash');
    	}
  }
  if ($chart->canEdit(logged_user())){
    add_page_action(lang('edit chart'), $chart->getEditUrl(), 'ico-edit');
  }
  $c = 0;
?>

<div style="padding:7px">
<div class="chart">

	<?php $description = 'pepe';
		tpl_assign("title", $chart->getTitle());
		tpl_assign("iconclass", 'ico-large-chart');
		tpl_assign("content", $chart->Draw());
		tpl_assign("object", $chart);
		
		$this->includeTemplate(get_template_path('view', 'co'));
	?>
</div>
</div>
