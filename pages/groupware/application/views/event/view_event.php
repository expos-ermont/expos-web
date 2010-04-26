<?php

$duration = $variables["duration"];
$desc = $variables["desc"];
$attendance = isset($variables["attendance"]) ? $variables["attendance"] : null;
$otherInvitationsTable = isset($variables["other_invitations"]) ? $variables["other_invitations"] : null;

if ($attendance != null) {
	echo '<br>' . $attendance;
}
?>
<br><b><?php echo lang('CAL_DURATION')?>:</b> <?php echo $duration?><br>
<br><b><?php echo lang('CAL_DESCRIPTION')?>:</b><br><?php echo $desc . ($desc != '' ? '<br>' : ''); ?>
<?php
if ($otherInvitationsTable != null) {
	echo '<br><b>' . lang('invitations') . ':</b><br>' . $otherInvitationsTable;
}
?>
