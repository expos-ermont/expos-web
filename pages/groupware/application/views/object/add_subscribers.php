<?php
require_javascript('modules/addMessageForm.js'); 
?>
<div class="og-add-subscribers">
<p><?php echo lang('add subscribers desc') ?></p>
<?php
	if (!is_array($subscriberIds)) $subscriberIds = array(logged_user()->getId());
	if (!isset($workspaces)) $workspaces = array(active_or_personal_project());
	if (!isset($genid)) $genid = gen_id();
?>
<?php
	// get users with permissions
	if ($type == 'Contacts') {
		$users = Users::getContactManagers();
	} else {
		$users = array();
		foreach ($workspaces as $ws) {
			$someUsers = $ws->getUsers(false);
			foreach ($someUsers as $u) {
				// see if user can read type of object in the workspace
				$canRead = can_manage_type($type, ProjectUsers::getByUserAndProject($ws, $u), ACCESS_LEVEL_READ);
				if ($canRead) {
					$users["u".$u->getId()] = $u;
				}
			}
		}
		$users = array_values($users);
	}
	$grouped = array();
	foreach($users as $user) {
		if(!isset($grouped[$user->getCompanyId()]) || !is_array($grouped[$user->getCompanyId()])) {
			$grouped[$user->getCompanyId()] = array();
		} // if
		$grouped[$user->getCompanyId()][] = $user;
	} // foreach
	$companyUsers = $grouped;
?>
<div id="<?php echo $genid ?>notify_companies"></div>
<script>
var div = Ext.getDom('<?php echo $genid ?>notify_companies');
div.notify_companies = {};
var cos = div.notify_companies;
</script>
<?php foreach($companyUsers as $companyId => $users) { ?>
	<script type="text/javascript">
		cos.company_<?php echo $companyId ?> = {
			id          : <?php echo $companyId ?>,
			checkbox_id : 'notifyCompany<?php echo $companyId ?>',
			users       : []
		};
	</script>
	<?php if(is_array($users) && count($users)) { ?>
	<div class="companyDetails">
		<div class="companyName">
			<?php echo checkbox_field('', null, 
				array('id' => $genid.'notifyCompany' . $companyId, 
					'onclick' => 'App.modules.addMessageForm.emailNotifyClickCompany(' . $companyId . ',"' . $genid. '","notify_companies", "notification")')) ?> 
			<label for="<?php echo $genid ?>notifyCompany<?php echo $companyId ?>" class="checkbox"><?php echo clean(Companies::findById($companyId)->getName()) ?></label>
		</div>
		
		<div class="companyMembers">
		<ul>
		<?php foreach($users as $user) { ?>
			<li><?php echo checkbox_field('subscribers[user_' . $user->getId() . ']', 
				in_array($user->getId(), $subscriberIds), 
				array('id' => $genid.'notifyUser' . $user->getId(), 
					'onclick' => 'App.modules.addMessageForm.emailNotifyClickUser(' . $companyId . ', ' . $user->getId() . ',"' . $genid. '","notify_companies", "notification")')) ?> 
				<label for="<?php echo $genid ?>notifyUser<?php echo $user->getId() ?>" class="checkbox"><?php echo clean($user->getDisplayName()) ?></label>
			<script type="text/javascript">
				cos.company_<?php echo $companyId ?>.users.push({
					id          : <?php echo $user->getId() ?>,
					checkbox_id : 'notifyUser<?php echo $user->getId() ?>'
				});
			</script></li>
		<?php } // foreach ?>
		</ul>
		</div>
		</div>
	<?php } // if ?>
<?php } // foreach ?>
</div>