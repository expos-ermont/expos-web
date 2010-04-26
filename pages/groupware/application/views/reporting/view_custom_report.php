<?php
	function format_value_to_print($value, $type, $textWrapper='') {
		switch ($type) {
			case DATA_TYPE_STRING: $formatted = $textWrapper . clean($value) . $textWrapper;
				break;
			case DATA_TYPE_INTEGER: $formatted = clean($value);
				break;
			case DATA_TYPE_BOOLEAN: $formatted = ($value == 1 ? lang('true') : lang('false'));
				break;
			case DATA_TYPE_DATE:
				if ($value != 0) { 
					$format = 'Y-m-d';
					if (str_ends_with($value, "00:00:00")) $format .= " H:i:s";
					$dtVal = DateTimeValueLib::dateFromFormatAndString($format, $value);
					$formatted = format_date($dtVal, null, 0);
				} else $formatted = '';
				break;
			case DATA_TYPE_DATETIME:
				if ($value != 0) {
					$dtVal = DateTimeValueLib::dateFromFormatAndString('Y-m-d H:i:s', $value);
					if (str_ends_with($value, "00:00:00")) $formatted = format_date($dtVal, null, 0);
					else $formatted = format_datetime($dtVal);
				} else $formatted = '';
				break;
			default: $formatted = $value;
		}
		
		return $formatted;
	}
	
	if ($description != '') echo clean($description) . '<br/>';
	$conditionHtml = '';
	
	if (count($conditions) > 0) {
		foreach ($conditions as $condition) {
			if($condition->getCustomPropertyId() > 0){
				$cp = CustomProperties::getCustomProperty($condition->getCustomPropertyId());
				$name = clean($cp->getName());
				$paramName = $cp->getName();
				$coltype = $cp->getOgType();
			}else{
				$name = lang('field ' . $model . ' ' . $condition->getFieldName());
				$coltype = array_key_exists($condition->getFieldName(), $types)? $types[$condition->getFieldName()]:'';
				$paramName = $condition->getId();
			}
			 
			$value = $condition->getIsParametrizable()? clean($parameters[$paramName]) : clean($condition->getValue());
			eval('$managerInstance = ' . $model . "::instance();");
			$externalCols = $managerInstance->getExternalColumns();
			if(in_array($condition->getFieldName(), $externalCols)){
				$value = clean(Reports::getExternalColumnValue($condition->getFieldName(), $value));
			}
			if ($value != '')
				$conditionHtml .= '- ' . $name . ' ' . ($condition->getCondition() != '%' ? $condition->getCondition() : lang('ends with') ) . ' ' . format_value_to_print($value, $coltype, '"') . '<br/>';
		}
	}
	
	if ($conditionHtml != '') {?>
<br/>
<b><?php echo lang('conditions')?>:</b><br/>
<p style="padding-left:10px">
	<?php echo $conditionHtml; ?>
</p>
<?php } // if ?>
<br/>
<input type="hidden" name="id" value="<?php echo $id ?>" />
<table>
<tbody>
<tr>
<?php foreach($columns as $col) { ?>
	<td style="padding-right:10px;border-bottom:1px solid #666"><b><?php echo clean($col) ?></b></td>
<?php } //foreach?>
</tr>
<?php
	$isAlt = true; 
	foreach($rows as $row) {
		$isAlt = !$isAlt; 
?>
	<tr<?php echo ($isAlt ? ' style="background-color:#F4F8F9"' : "") ?>>
		<?php foreach($row as $k => $value) { ?>
			<td style="padding-right:10px;"><?php echo format_value_to_print($value, ($k == 'link'?'':$types[$k])) ?></td>
		<?php }//foreach ?>
	</tr>
<?php } //foreach ?>
</tbody>
</table>

<br/><?php echo $pagination ?>
