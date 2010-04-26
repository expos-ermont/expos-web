<?php
require_javascript("og/DateField.js");
require_javascript("og/ReportingFunctions.js");
$genid = gen_id();
?>
<form style='height: 100%; background-color: white' class="internalForm"
	action="<?php echo $url  ?>" method="post"
	onsubmit="return og.validateReport('<?php echo $genid ?>');"><input
	type="hidden" name="report[object_type]" id="report[object_type]"
	value="<?php echo isset($report_data['object_type']) ? $report_data['object_type'] : "" ?>" />
<div class="coInputHeader">
<div class="coInputHeaderUpperRow">
<div class="coInputTitle">
<table style="width: 535px">
	<tr>
		<td><?php echo (isset($id) ? lang('edit custom report') : lang('new custom report')) ?>
		</td>
		<td style="text-align: right"><?php echo submit_button((isset($id) ? lang('save changes') : lang('add report')),'s',array('style'=>'margin-top:0px;margin-left:10px', 'tabindex' => '100')) ?></td>
	</tr>
</table>
</div>
</div>
<div>
<?php echo label_tag(lang('name'), $genid . 'reportFormName', true) ?>
<?php echo text_field('report[name]', array_var($report_data, 'name'),
array('id' => $genid . 'reportFormName', 'tabindex' => '1', 'class' => 'title')) ?>
<?php echo label_tag(lang('description'), $genid . 'reportFormDescription', false) ?>
<?php echo text_field('report[description]', array_var($report_data, 'description'),
array('id' => $genid . 'reportFormDescription', 'tabindex' => '2', 'class' => 'title')) ?>
<?php echo label_tag(lang('object type'), $genid . 'reportFormObjectType', true) ?>
<?php
$options = array();
foreach ($object_types as $type) {
	if ($selected_type == $type[0]) {
		$selected = 'selected="selected"';
	} else {
		$selected = '';
	}
	$options[] = '<option value="'.$type[0].'" '.$selected.'>'.$type[1].'</option>';
} 
?>
<?php $strDisabled = count($options) > 1 ? '' : 'disabled'; 
echo select_box('objectTypeSel', $options,
array('id' => 'objectTypeSel' ,'onchange' => 'og.reportObjectTypeChanged("'.$genid.'", "", 1, "")', 'style' => 'width:200px;', $strDisabled => '')) ?>

</div>
</div>
<div id="<?php echo $genid ?>MainDiv" class="coInputMainBlock" style="display:none;">
<fieldset><legend><?php echo lang('conditions') ?></legend>
<div id="<?php echo $genid ?>"></div>
<br />
<a href="#" class="link-ico ico-add"
	onclick="og.addCondition('<?php echo $genid ?>', 0, 0, '', '', '', false)"><?php echo lang('add condition')?></a>
</fieldset>

<fieldset><legend><?php echo lang('columns and order') ?></legend>
<div><?php echo label_tag(lang('order by'), $genid . 'reportFormOrderBy', true, array('id' => 'orderByLbl', 'style' => 'display:none;')) ?>
<?php echo select_box('report[order_by]', array(),
array('id' => 'report[order_by]', 'style' => 'width:200px;display:none;'));
$asc = option_tag(lang('ascending'), 'asc');
$desc = option_tag(lang('descending'), 'desc');
echo select_box('report[order_by_asc]', array($asc, $desc),
array('id' => 'report[order_by_asc]', 'style' => 'width:200px;display:none;')) ?>
<br /><br />
<a href="#" onclick="og.toggleColumnSelection()"><?php echo lang('select unselect all')?></a>
<div id="columnList">
	<table>
		<tr>
			<td id="tdFields" style="padding-right:10px;"></td>
			<td id="tdCPs"></td>
		</tr>
	</table>
</div>
</div>
</fieldset>

<?php echo submit_button((isset($id) ? lang('save changes') : lang('add report')))?>

</div>

</form>

<script>
	og.loadReportingFlags();
	og.reportObjectTypeChanged("<?php echo $genid ?>", "", 1, "");
	<?php if(isset($conditions)){ ?>
		og.reportObjectTypeChanged('<?php echo $genid?>', '<?php echo array_var($report_data, 'order_by') ?>', '<?php echo array_var($report_data, 'order_by_asc') ?>', '<?php echo (isset($columns) ? implode(',', $columns) : '') ?>');
		<?php foreach($conditions as $condition){ ?>		
			og.addCondition('<?php echo $genid?>',<?php echo $condition->getId() ?>, <?php echo $condition->getCustomPropertyId() ?> , '<?php echo $condition->getFieldName() ?>', '<?php echo $condition->getCondition() ?>', '<?php echo $condition->getValue() ?>', '<?php echo $condition->getIsParametrizable() ?>');		
		<?php }//foreach ?>
	<?php }//if ?>		
</script>
