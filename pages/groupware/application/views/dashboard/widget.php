<?php $widgetGenid = gen_id(); 
	$isWd = $widgetTemplate == 'workspace_description';
	if ($isWd) {
		$isExpanded = active_project()->getShowDescriptionInOverview();
	} else {
		$isExpanded = user_config_option($widgetTemplate . '_widget_expanded',true);
	}?>
<div class="<?php echo $widgetClass; ?>">
<table style="width:100%">
	<col width=12/><col/><col width=12/>
	<tr><td style="width:12px;height:1px;overflow:hidden;line-height:0px;"></td>
	<td style="height:0px;overflow:hidden;line-height:0px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td style="width:12px;height:1px;overflow:hidden;line-height:0px;"></td></tr>
	<tr>
	<td colspan=2 rowspan=2 class="dashHeader">
		<table style="width:100%;cursor: pointer" onclick="og.dashExpand('<?php echo $widgetGenid ?>','<?php echo $widgetTemplate; ?>')"><tr>
			<td>
				<div class="dashTitle"><?php echo $widgetTitle; ?></div>
			</td>
			<?php if (!($isWd && $isExpanded)){?>
			<td align=right style="width:30px">
				<?php if ($isExpanded) { ?>
					<div id="<?php echo $widgetGenid ?>expander" class="dash-expander ico-dash-expanded"></div>
				<?php } else { ?>
					<div id="<?php echo $widgetGenid ?>expander" class="dash-expander ico-dash-collapsed"></div>
				<?php } ?>
			</td>
			<?php } ?>
		</tr></table>
	</td>
	<td class="coViewTopRight">&nbsp;&nbsp;</td></tr>
	<tr><td class="coViewRight" rowspan=2></td></tr>
	
		<tr><td class="coViewBody" colspan=2 style='padding:0px'>
		<div id="<?php echo $widgetGenid ?>widget" style='<?php if (!$isExpanded) echo 'display:none' ?>'>
		<?php $this->includeTemplate(get_template_path('widget_' . $widgetTemplate, 'dashboard')); ?>
		</div>
		</td></tr>

		<tr><td class="coViewBottomLeft"></td>
		<td class="coViewBottom"></td>
		<td class="coViewBottomRight"></td></tr>
	</table>
</div>