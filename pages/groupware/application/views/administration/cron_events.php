<div class="adminCronEvents" style="height:100%;background-color:white">
	<div class="adminHeader">
		<div class="adminTitle"><?php echo lang("cron events") ?></div>
	</div>
	<div class="adminSeparator"></div>
	<div class="adminMainBlock">

	<div style="margin-bottom: 15px">
		<a href="#" onclick="document.getElementById('cron-events-info').style.display = 'block';this.style.display = 'none';return false;"><?php echo lang('about cron events') ?></a>
		<span id="cron-events-info" style="display:none;"><?php echo lang('cron events info') ?></span>
	</div>

<?php if (is_array($events) && count($events)) { ?>
<form class="internalForm" action="<?php echo get_url("administration", "cron_events") ?>" method="post" onreset="return confirm('<?php echo escape_single_quotes(lang('confirm reset form')) ?>')">
	<table class="cronEventsTable"><tbody>
		<tr class="cronEventsHeader even">
			<th class="cronEventsName"><?php echo lang("name") ?></th>
			<th class="cronEventsDate"><?php echo lang("next execution") ?></th>
			<th class="cronEventsDelay"><?php echo lang("delay between executions") ?></th>
			<th class="cronEventsEnabled"><?php echo lang("enabled") ?></th>
		</tr>
	<?php $counter = 0; ?>
	<?php foreach ($events as $event) { ?>
		<?php $counter++; ?>
		<tr class="cronEventsRow <?php echo $counter % 2 ? 'even' : 'odd' ?>">
			<td class="cronEventsName">
				<label><?php echo $event->getDisplayName() ?>:</label>
				<?php if (trim($event_description = $event->getDisplayDescription())) { ?>
					<div class="desc"><?php echo clean($event_description) ?></div>
				<?php } // if ?>
			</td>
			<td class="cronEventsDate">
				<table><tbody><tr><td><?php echo pick_date_widget2('cron_events['.$event->getId().'][date]', $event->getDate(), null, false) ?>
				</td><td><?php echo pick_time_widget2('cron_events['.$event->getId().'][time]', $event->getDate()) ?>
				</td></tr></tbody></table>
							
			</td>
			<td class="cronEventsDelay">
				<?php echo input_field('cron_events['.$event->getId().'][delay]', $event->getDelay(), array('class' => 'short')) ?>
				<span class="desc"><?php echo lang('minutes') ?></span>
			</td>
			<td class="cronEventsEnabled">
				<?php echo checkbox_field('cron_events['.$event->getId().'][enabled]', $event->getEnabled()) ?>
			</td>
		</tr>
	<?php } // foreach ?>
	</tbody></table>

	<?php echo submit_button(lang('save')) ?>&nbsp;<button type="reset"><?php echo lang('reset') ?></button>
</form>
<?php } else { ?>
	<p><?php echo lang('no cron events to display') ?></p>
<?php } // if ?>
	</div>
</div>
