<div class="history" style="height:100%;background-color:white">
<div class="coInputHeader">
	<div class="coInputHeaderUpperRow">
	<div class="coInputTitle"><?php echo lang('view history for') . ' ' . clean($object->getObjectName()); ?></div>
	</div>
</div>
<div class="coInputSeparator"></div>
<div class="coInputMainBlock adminMainBlock">

<table style="min-width:400px;margin-top:10px;">
<tr><th><?php echo lang('date')?></th>
<th><?php echo lang('user')?></th>
<th><?php echo lang('details')?></th>
</tr>
<?php
$isAlt = true;
if (is_array($logs)) {
	foreach ($logs as $log) {
		$isAlt = !$isAlt;
		echo '<tr' . ($isAlt? ' class="altRow"' : '') . '><td  style="padding:5px;padding-right:15px;">';
		if ($log->getCreatedOn()->getYear() != DateTimeValueLib::now()->getYear())
			$date = format_time($log->getCreatedOn(), "M d Y, H:i");
		else{
			if ($log->isToday())
				$date = lang('today') . format_time($log->getCreatedOn(), ", H:i:s");
			else
				$date = format_time($log->getCreatedOn(), "M d, H:i");
		}
		echo $date . ' </td><td style="padding:5px;padding-right:15px;"><a class="internalLink" href="' . $log->getTakenBy()->getCardUrl() . '">'  . clean($log->getTakenByDisplayName()) . '</a></td><td style="padding:5px;padding-right:15px;"> ' . $log->getText();
		echo '</td></tr>';
	}
}

?>
</table>
</div>
</div>