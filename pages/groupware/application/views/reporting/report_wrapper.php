<?php
$genid = gen_id();
  	/*add_page_action(lang('print view'), '#', "ico-print", "_blank", array('onclick' => 'this.form' . $genid . '.submit'));*/
?>
<form name="form<?php echo $genid ?>" action="<?php echo get_url('reporting', $template_name . '_print') ?>" method="post" enctype="multipart/form-data" target="_blank">

<input name="post" type="hidden" value="<?php echo str_replace('"',"'", json_encode($post))?>"/>

<div class="report" style="padding:7px">
<table style="min-width:600px">
<col width=12/>
<tr>
	<td colspan=2 rowspan=2 class="coViewIcon">
		<div id="iconDiv" class="coViewIconImage ico-large-report"></div>
	</td>
	<td rowspan=2 class="coViewHeader">
		<div class="coViewTitle"><?php echo $title ?></div>
		<input type="submit" value="<?php echo lang('print view') ?>" style="width:120px; margin-top:10px;"/>
	</td>
	
	<td class="coViewTopRight"></td>
</tr>
<tr>
	<td class="coViewRight" rowspan=2></td>
</tr>
<tr>
	<td colspan=3 class="coViewBody" style="padding-left:12px">
		<?php $this->includeTemplate(get_template_path($template_name, 'reporting'));?>
	</td>
</tr>
<tr>
	<td class="coViewBottomLeft"></td>
	<td class="coViewBottom" ></td>
	<td class="coViewBottom" ></td>
	<td class="coViewBottomRight"></td>
</tr></table>

</div>

</form>