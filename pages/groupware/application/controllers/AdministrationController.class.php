<?php

/**
 * Administration controller
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class AdministrationController extends ApplicationController {

	/**
	 * Construct the AdministrationController
	 *
	 * @access public
	 * @param void
	 * @return AdministrationController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
		ajx_set_panel("administration");

		// Access permissios
		if(!logged_user()->isCompanyAdmin(owner_company())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
		} // if
	} // __construct

	/**
	 * Show administration index
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function index() {

	} // index

	/**
	 * Show company page
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function company() {
		tpl_assign('company', owner_company());
		ajx_set_no_toolbar(true);
		$this->setTemplate(get_template_path('view_company', 'company'));
	} // company

	/**
	 * Show owner company members
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function members() {
		tpl_assign('company', owner_company());
		tpl_assign('users_by_company', Users::getGroupedByCompany());
	} // members

	/**
	 * List all company projects
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function projects() {
		if(can_manage_workspaces(logged_user()))
			tpl_assign('projects', Projects::getAll());
		else
			tpl_assign('projects', logged_user()->getProjects());
	} // projects

	/**
	 * List clients
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function clients() {
		tpl_assign('clients', owner_company()->getClientCompanies());
	} // clients
	
	/**
	 * List custom properties
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function custom_properties() {
		tpl_assign('object_types', array('<option value="" selected>'.lang('select one').'</option>',
			'<option value="Companies">'.lang('company').'</option>',
			'<option value="Contacts">'.lang('contact').'</option>',
			'<option value="MailContents">'.lang('email type').'</option>',
			'<option value="ProjectEvents">'.lang('events').'</option>', 
			'<option value="ProjectFiles">'.lang('file').'</option>',
			'<option value="ProjectMilestones">'.lang('milestone').'</option>',
			'<option value="ProjectMessages">'.lang('message').'</option>',
			'<option value="ProjectTasks">'.lang('task').'</option>',
			'<option value="Users">'.lang('user').'</option>',
			'<option value="ProjectWebPages">'.lang('webpage').'</option>',
			'<option value="Projects">'.lang('workspace').'</option>', 
		));
		$custom_properties = array_var($_POST, 'custom_properties');
		$obj_type = array_var($_POST, 'objectType');
		if (is_array($custom_properties)) {
			try {
				DB::beginWork();
				foreach ($custom_properties as $id => $data) {
					$new_cp = new CustomProperty();
					if($data['id'] != ''){
						$new_cp = CustomProperties::getCustomProperty($data['id']);					
					}	
					if($data['deleted'] == "1"){
						$new_cp->delete();
						continue;
					}
					$new_cp->setObjectType($obj_type);
					$new_cp->setName($data['name']);
					$new_cp->setType($data['type']);
					$new_cp->setDescription($data['description']);
					$new_cp->setValues($data['values']);
					if($data['type'] == 'boolean'){
						$new_cp->setDefaultValue(isset($data['default_value_boolean']));
					}else{
						$new_cp->setDefaultValue($data['default_value']);
					}		
					$new_cp->setIsRequired(isset($data['required']));
					$new_cp->setIsMultipleValues(isset($data['multiple_values']));
					$new_cp->setOrder($id);
					$new_cp->setVisibleByDefault(isset($data['visible_by_default']));
					$new_cp->save();
				}
				DB::commit();
				flash_success(lang('custom properties updated'));
				ajx_current('back');
			} catch (Exception $ex) {
				DB::rollback();
				flash_error($ex->getMessage());
			}
		}
	} // custom_properties

	/**
	 * List groups
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function groups() {
		tpl_assign('groups', Groups::getAll());
	} // clients

	/**
	 * Show configuration index page
	 *
	 * @param void
	 * @return null
	 */
	function configuration() {
		$this->addHelper('textile');
		tpl_assign('config_categories', ConfigCategories::getAll());
	} // configuration

	/**
	 * List all available administration tools
	 *
	 * @param void
	 * @return null
	 */
	function tools() {
		tpl_assign('tools', AdministrationTools::getAll());
	} // tools

	/**
	 * List all templates
	 *
	 * @param void
	 * @return null
	 */
	function task_templates() {
		tpl_assign('task_templates', ProjectTasks::getAllTaskTemplates());
	} // tools

	
	
	/**
	 * Show upgrade page
	 *
	 * @param void
	 * @return null
	 */
	function upgrade() {
		$this->addHelper('textile');

		$version_feed = VersionChecker::check(true);
		if(!($version_feed instanceof VersionsFeed)) {
			flash_error(lang('error check for upgrade'));
			$this->redirectTo('administration', 'upgrade');
		} // if

		tpl_assign('versions_feed', $version_feed);
	} // upgrade	

	// ---------------------------------------------------
	//  Tool implementations
	// ---------------------------------------------------

	/**
	 * Render and execute test mailer form
	 *
	 * @param void
	 * @return null
	 */
	function tool_test_email() {
		$tool = AdministrationTools::getByName('test_mail_settings');
		if(!($tool instanceof AdministrationTool)) {
			flash_error(lang('administration tool dnx', 'test_mail_settings'));
			$this->redirectTo('administration', 'tools');
		} // if

		$test_mail_data = array_var($_POST, 'test_mail');

		tpl_assign('tool', $tool);
		tpl_assign('test_mail_data', $test_mail_data);

		if(is_array($test_mail_data)) {
			try {
				$recepient = trim(array_var($test_mail_data, 'recepient'));
				$message = trim(array_var($test_mail_data, 'message'));

				$errors = array();

				if($recepient == '') {
					$errors[] = lang('test mail recipient required');
				} else {
					if(!is_valid_email($recepient)) {
						$errors[] = lang('test mail recipient invalid format');
					} // if
				} // if

				if($message == '') {
					$errors[] = lang('test mail message required');
				} // if

				if(count($errors)) {
					throw new FormSubmissionErrors($errors);
				} // if

				$success = Notifier::sendEmail($recepient, logged_user()->getEmail(), lang('test mail message subject'), $message);
				if($success) {
					flash_success(lang('success test mail settings'));
				} else {
					flash_error(lang('error test mail settings'));
				} // if
				ajx_current("back");
			} catch(Exception $e) {
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // tool_test_email

	/**
	 * Send multiple emails using this simple tool
	 *
	 * @param void
	 * @return null
	 */
	function tool_mass_mailer() {
		$tool = AdministrationTools::getByName('mass_mailer');
		if(!($tool instanceof AdministrationTool)) {
			flash_error(lang('administration tool dnx', 'test_mail_settings'));
			$this->redirectTo('administration', 'tools');
		} // if

		$massmailer_data = array_var($_POST, 'massmailer');

		tpl_assign('tool', $tool);
		tpl_assign('grouped_users', Users::getGroupedByCompany());
		tpl_assign('massmailer_data', $massmailer_data);

		if(is_array($massmailer_data)) {
			try {
				$subject = trim(array_var($massmailer_data, 'subject'));
				$message = trim(array_var($massmailer_data, 'message'));

				$errors = array();

				if($subject == '') {
					$errors[] = lang('massmailer subject required');
				} // if

				if($message == '') {
					$errors[] = lang('massmailer message required');
				} // if

				$users = Users::getAll();
				$recepients = array();
				if(is_array($users)) {
					foreach($users as $user) {
						if(array_var($massmailer_data, 'user_' . $user->getId()) == 'checked') {
							$recepients[] = Notifier::prepareEmailAddress($user->getEmail(), $user->getDisplayName());
						} // if
					} // foreach
				} // if

				if(!count($recepients)) {
					$errors[] = lang('massmailer select recepients');
				} // if

				if(count($errors)) {
					throw new FormSubmissionErrors($errors);
				} // if

				if(Notifier::sendEmail($recepients, Notifier::prepareEmailAddress(logged_user()->getEmail(), logged_user()->getDisplayName()), $subject, $message)) {
					flash_success(lang('success massmail'));
				} else {
					flash_error(lang('error massmail'));
				} // if
				ajx_current("back");
			} catch(Exception $e) {
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // tool_mass_mailer
		
	function cron_events() {
		$events = CronEvents::getUserEvents();
		tpl_assign("events", $events);
		$cron_events = array_var($_POST, 'cron_events');
		if (is_array($cron_events)) {
			try {
				DB::beginWork();
				foreach ($cron_events as $id => $data) {
					$event = CronEvents::findById($id);
					$date = getDateValue($data['date']);
					if ($date instanceof DateTimeValue) {
						$this->parseTime($data['time'], $hour, $minute);
						$date->add("m", $minute);
						$date->add("h", $hour);
						$event->setDate($date);
					}
					$delay = $data['delay'];
					if (is_numeric($delay)) {
						$event->setDelay($delay);
					}
					$enabled = array_var($data, 'enabled') == 'checked';
					$event->setEnabled($enabled);
					$event->save();
				}
				DB::commit();
				flash_success(lang("success update cron events"));
				ajx_current("back");
			} catch (Exception $ex) {
				DB::rollback();
				flash_error($ex->getMessage());
			}
		}
	}
	
	/**
	 * Returns hour and minute in 24 hour format
	 *
	 * @param string $time_str
	 * @param int $hour
	 * @param int $minute
	 */
	function parseTime($time_str, &$hour, &$minute) {
		$exp = explode(':', $time_str);
		$hour = $exp[0];
		$minute = $exp[1];
		if (str_ends_with($time_str, 'M')) {
			$exp = explode(' ', $minute);
			$minute = $exp[0];
			if ($exp[1] == 'PM' && $hour < 12) {
				$hour = ($hour + 12) % 24;
			}
			if ($exp[1] == 'AM' && $hour == 12) {
				$hour = 0;
			}
		}
	}

	
} // AdministrationController

?>