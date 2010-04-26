<?php
require_javascript("og/CustomProperties.js");
$cps = CustomProperties::getAllCustomPropertiesByObjectType($type);
$ti = 0;
if (!isset($genid))
$genid = gen_id();
if (!isset($startTi))
$startTi = 20000;
if(count($cps) > 0){
	foreach($cps as $customProp){
		if(!isset($required) || ($required && ($customProp->getIsRequired() || $customProp->getVisibleByDefault())) || (!$required && !($customProp->getIsRequired() || $customProp->getVisibleByDefault()))){
			$ti++;
			$cpv = CustomPropertyValues::getCustomPropertyValue($_custom_properties_object->getId(), $customProp->getId());
			$default_value = $customProp->getDefaultValue();
			if($cpv instanceof CustomPropertyValue){
				$default_value = $cpv->getValue();
			}
			$name = 'object_custom_properties['.$customProp->getId().']';
			echo '<div style="margin-top:6px">';

			if ($customProp->getType() == 'boolean')
			echo checkbox_field($name, $default_value, array('tabindex' => $startTi + $ti, 'style' => 'margin-right:4px', 'id' => $genid . 'cp' . $customProp->getName()));

			echo label_tag(clean($customProp->getName()), $genid . 'cp' . $customProp->getName(), $customProp->getIsRequired(), array('style' => 'display:inline'), $customProp->getType() == 'boolean'?'':':');
			if ($customProp->getDescription() != ''){
				echo '<span class="desc" style="margin-left:10px">- ' . clean($customProp->getDescription()) . '</span>';
			}
			echo '</div>';

			switch ($customProp->getType()) {
				case 'text':
				case 'numeric':
				case 'memo':
					if($customProp->getIsMultipleValues()){
						$numeric = ($customProp->getType() == "numeric");
						echo "<table><tr><td>";
						echo '<div id="listValues'.$customProp->getId().'" name="listValues'.$customProp->getId().'">';
						$isMemo = $customProp->getType() == 'memo';
						$count = 0;
						$fieldValues = explode(',', $default_value);
						foreach($fieldValues as $value){
							$value = str_replace('|', ',', $value);
							if($value != ''){
								echo '<div id="value'.$count.'">';
								if($isMemo){
									echo textarea_field($name.'[]', $value, array('tabindex' => $startTi + $ti, 'id' => $name.'[]'));
								}else{
									echo text_field($name.'[]', $value, array('tabindex' => $startTi + $ti, 'id' => $name.'[]'));
								}
								echo '&nbsp;<a href="#" class="link-ico ico-delete" onclick="og.removeCPValue('.$customProp->getId().','.($count).','.($isMemo ? 1 : 0).')" ></a>';
								echo '</div>';
								$count++;
							}
						}
						echo '<div id="value'.$count.'">';
						if($customProp->getType() == 'memo'){
							echo textarea_field($name.'[]', '', array('tabindex' => $startTi + $ti, 'id' => $name.'[]'));
						}else{
							echo text_field($name.'[]', '', array('tabindex' => $startTi + $ti, 'id' => $name.'[]'));
						}
						echo '&nbsp;<a href="#" class="link-ico ico-add" onclick="og.addCPValue('.$customProp->getId().',\''.$isMemo.'\')">'.lang('add value').'</a><br/>';
						echo '</div>';
						echo '</div>';
						echo "</td></tr></table>";
						$include_script = true;
					}else{
						if($customProp->getType() == 'memo'){
							echo textarea_field($name, $default_value, array('tabindex' => $startTi + $ti, 'class' => 'short'));
						}else{
							echo text_field($name, $default_value, array('tabindex' => $startTi + $ti));
						}
					}
					break;
				case 'boolean':
					break;
				case 'date':
					// dates from table are saved as a string in "Y-m-d H:i:s" format
					$value = DateTimeValueLib::dateFromFormatAndString("Y-m-d H:i:s", $default_value);
					echo pick_date_widget2($name, $value, null, $startTi + $ti);
					break;
				case 'list':
					$options = array();
					if(!$customProp->getIsRequired()){
						$options[] = '<option value=""></option>';
					}
					$totalOptions = 0;
					foreach(explode(',', $customProp->getValues()) as $value){
						$selected = ($value == $default_value) || ($customProp->getIsMultipleValues() && in_array($value, explode(',', $default_value)));
						if($selected){
							$options[] = '<option value="'. clean($value) .'" selected>'. clean($value) .'</option>';
						}else{
							$options[] = option_tag($value, $value);
						}
						$totalOptions++;
					}
					if($customProp->getIsMultipleValues()){
						$name .= '[]';
						echo select_box($name, $options, array('tabindex' => $startTi + $ti, 'style' => 'min-width:140px',  'size' => $totalOptions, 'multiple' => 'multiple'));
					}else{
						echo select_box($name, $options, array('tabindex' => $startTi + $ti, 'style' => 'min-width:140px'));
					}
					break;
				default: break;
			}
		}
	}
}


?>