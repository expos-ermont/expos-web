<?php
	$genid = gen_id();
	$tiCount = 0;
	$firstId = '';
?>

<form style='height:100%;background-color:white' class="internalForm" action="<?php echo get_url('reporting', 'view_custom_report', array('id' => $id)) ?>" method="get">

<div class="coInputHeader">
	<div class="coInputHeaderUpperRow">
		<div class="coInputTitle"><?php echo $title ?></div>
	</div>
</div>
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">

	<div style="width:600px;padding-bottom:20px"><?php echo $description ?></div>

	<table>
		<tr>
			<td style="padding:0 10px 10px 10px;"><b><?php echo lang('field') ?></b></td>
			<td style="text-align:center;padding:0 10px 10px 10px;"><b><?php echo lang('condition') ?></b></td>
			<td style="text-align:center;padding:0 10px 10px 10px;"><b><?php echo lang('value') ?></b></td>
		</tr>
		
		<?php foreach($conditions as $condition){
			$tiCount ++;
			?>
			<tr style='height:30px;'>
			<?php if($condition->getCustomPropertyId() > 0){
					$cp = CustomProperties::getCustomProperty($condition->getCustomPropertyId());
					$name = clean($cp->getName());
				  }else{
					$name = lang('field ' . $model . ' ' . $condition->getFieldName());	
				  } 
				  $condId = $genid . 'rpcond' . $condition->getId();
				if ($firstId == '')
					$firstId = $condId;
			?>
				<td style="padding:3px 0 0 10px;"><b><?php echo $name ?>&nbsp;</b></td>
				<td style="text-align:center;padding:3px 0 0 0;"><?php echo ($condition->getCondition() != '%' ? $condition->getCondition() : lang('ends with') ) ?>&nbsp;</td>
			<?php if(isset($cp)){ ?>
				<td align='left'>
					<?php if($cp->getType() == 'text' || $cp->getType() == 'numeric'){ ?>
						<input type="text" id="<?php echo $condId; ?>" name="params[<?php echo clean($cp->getName()) ?>]" tabindex=<?php echo $tiCount?>/>
					<?php }else if($cp->getType() == 'boolean'){  ?>
						<select id="<?php echo $condId; ?>" name="params[<?php echo clean($cp->getName()) ?>]" tabindex=<?php echo $tiCount?>>
							<option value="1" > <?php echo lang('true') ?>  </option>
							<option value="0" > <?php echo lang('false') ?> </option>
						</select>
					<?php }else if($cp->getType() == 'list'){  ?>
						<select id="<?php echo $condId; ?>" name="params[<?php echo clean($cp->getName()) ?>]" tabindex=<?php echo $tiCount?>>
						<?php foreach(explode(',', $cp->getValues()) as $value){ ?>
							<option value="<?php echo $value ?>"> <?php echo $value ?>  </option>
						<?php }//foreach ?>
						</select>
					<?php }else if($cp->getType() == 'date'){  ?>
						<?php echo pick_date_widget2("params[".clean($cp->getName())."]",$genid,$tiCount)?>
					<?php }?>
				</td>
			<?php }else{ ?>
				<td align='left'>
				<?php 
					$model_instance = new $model();
					$col_type = $model_instance->getColumnType($condition->getFieldName());
					$externalCols = $model_instance->getExternalColumns();
					if(in_array($condition->getFieldName(), $externalCols)){ ?>
						<select id="<?php echo $condId; ?>" name="params[<?php echo $condition->getId() ?>]" />
					<?php foreach($external_fields[$condition->getFieldName()] as $value){ ?>
							<option value="<?php echo $value['id'] ?>"><?php echo $value['name'] ?></option>
					<?php	}?>
						</select>
					<?php }else{
						if ($col_type == DATA_TYPE_DATE || $col_type == DATA_TYPE_DATETIME) {
							echo pick_date_widget2("params[".$condition->getId()."]");
						} else {
				?>
					<input type="text" id="<?php echo $condId; ?>" name="params[<?php echo $condition->getId() ?>]" />
				<?php 	}
					} ?>
				</td>
			<?php } ?>
			</tr>
		<?php
			unset($cp); 
			}//foreach ?>
	</table>
	
<?php echo submit_button(lang('generate report'),'s',array('tabindex' => $tiCount + 1))?>	
</div>

<script type="text/javascript">
var firstCond = Ext.getDom('<?php echo $firstId ?>');
if (firstCond)
	firstCond.focus();
</script>

</form>