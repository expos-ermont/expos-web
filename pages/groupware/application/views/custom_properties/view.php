<?php
	$properties = $__properties_object->getCustomProperties();	
	$cpvCount = CustomPropertyValues::getCustomPropertyValueCount($__properties_object->getId(), get_class($__properties_object->manager()));
	if ((!is_array($properties) || count($properties) == 0) && $cpvCount == 0) 
		return "";
?>
<div class="commentsTitle"><?php echo lang('custom properties')?></div>
<?php if($cpvCount > 0){?>
<table class="og-custom-properties">
<?php 
	$alt = true;
	$cps = CustomProperties::getAllCustomPropertiesByObjectType(get_class($__properties_object->manager()));
	foreach($cps as $customProp){ 
		$alt = !$alt;
		$cpv = CustomPropertyValues::getCustomPropertyValue($__properties_object->getId(), $customProp->getId());
		if($cpv instanceof CustomPropertyValue && ($customProp->getIsRequired() || $cpv->getValue() != '')){?>
			<tr class="<?php echo $alt ? 'altRow' : ''?>">
				<td class="name" title="<?php echo clean($customProp->getName()) ?>"><?php echo clean(truncate($customProp->getName(), 20)) ?>:&nbsp;</td>
				<?php
					// dates are in standard format "Y-m-d H:i:s", must be formatted
					if ($customProp->getType() == 'date') {
						$dtv = DateTimeValueLib::dateFromFormatAndString("Y-m-d H:i:s", $cpv->getValue());
						$value = $dtv->format(user_config_option('date_format', 'd/m/Y'));
					} else {
						$value = $cpv->getValue();
					}
					
					$title = '';
					$style = '';
					if ($customProp->getType() == 'boolean'){
						$htmlValue = '<div class="db-ico ico-'.($value?'complete':'delete').'">&nbsp;</div>';
					} else if ($customProp->getIsMultipleValues()) {
						$multValues = explode(',', $value); //Change to new separator logic
						$htmlValue = '<table style="width:100%;margin-bottom:2px">';
						$newAlt = $alt;
						foreach ($multValues as $mv){
							$title =  (strlen($mv) > 100 && $customProp->getType() != 'memo') ? clean(str_replace('|', ',', $mv)) : '';
							$showValue = $customProp->getType() == 'memo' ? str_replace('|', ',', $mv) : truncate($mv,100);
							$htmlValue .= '<tr class="' . ($newAlt ? 'altRow' : 'row') . '"><td style="padding:0px 5px" title="' . $title . '">' . clean($showValue) . '</td></tr>';
							$newAlt = !$newAlt; 
						}
						$htmlValue .= '</table>';
						$style = 'style="padding:1px 0px"';
					} else {
						$title =  (strlen($value) > 100 && $customProp->getType() != 'memo') ? clean($value) : '';
						$htmlValue = nl2br(clean($customProp->getType() == 'memo' ? $value : truncate($value,100)));
					}
				?>
				<td class="value" <?php echo $style ?> title="<?php echo $title?>"><?php echo $htmlValue ?></td>
			</tr>
		<?php } // if
	} // foreach ?>
</table>
<?php } // if
	
// Draw flexible custom properties
if (is_array($properties) && count($properties) > 0){ ?>
	<table class="og-custom-properties">
	<?php foreach ($properties as $prop) {?>
		<tr>
			<td class="name" title="<?php echo $prop->getPropertyName() ?>">- <?php echo truncate($prop->getPropertyName(), 12) ?>:&nbsp;</td>
			<td title="' . $prop->getPropertyValue() . '"><?php echo truncate($prop->getPropertyValue(), 12) ?></td>
		</tr>
	<?php } // foreach ?>
	</table>
<?php } // if ?>
</div>