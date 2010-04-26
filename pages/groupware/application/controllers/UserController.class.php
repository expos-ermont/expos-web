<?php

/**
 * User controller
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class UserController extends ApplicationController {

	/**
	 * Construct the UserController
	 *
	 * @access public
	 * @param void
	 * @return UserController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct

	/**
	 * User management index
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function index() {

	} // index

	/**
	 * Add user
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function add() {
   		$max_users = config_option('max_users');
		if($max_users && (Users::count() >= $max_users) ){
			flash_error(lang('maximum number of users reached error'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add_user');

		$company = Companies::findById(get_id('company_id'));
		if(!($company instanceof Company)) {
			flash_error(lang('company dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!User::canAdd(logged_user(), $company)) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$user = new User();

		$user_data = array_var($_POST, 'user');
		if(!is_array($user_data)) {
			//if it is a new contact
			$contact_id = get_id('contact_id');			
			if($contact_id && $contact = Contacts::findById($contact_id)){
				//if it will be created from a contact
				$user_data = array(
					'username' => substr_utf($contact->getFirstname(),0,1) . $contact->getLastname(),
					'display_name' => $contact->getFirstname() . $contact->getLastname(),
					'email' => $contact->getEmail(),
					'contact_id' => $contact->getId(),
					'password_generator' => 'random',
					'company_id' => $company->getId(),
					'timezone' => $contact->getTimezone(),
					'create_contact' => false ,
				); // array
				
			}
			else{
				// if it is new, and created from admin interface
				$user_data = array(
		          'password_generator' => 'random',
		          'company_id' => $company->getId(),
		          'timezone' => $company->getTimezone(),
					'create_contact' => true ,
					'send_email_notification' => true ,
				); // array
			}
		} // if

		$permissions = ProjectUsers::getNameTextArray();

		tpl_assign('user', $user);
		tpl_assign('company', $company);
		tpl_assign('permissions', $permissions);
		tpl_assign('user_data', $user_data);
		tpl_assign('billing_categories', BillingCategories::findAll());

		if(is_array(array_var($_POST, 'user'))) {
			$user->setFromAttributes($user_data);
			//$user->setCompanyId($company->getId());
			$is_admin = logged_user()->isAdministrator() && array_var($_POST, 'is_admin');

			try {
			  $user = $this->createUser($user, $user_data, $is_admin, array_var($_POST,'permissions'));
			
			  $object_controller = new ObjectController();
			  $object_controller->add_custom_properties($user);
					
			  flash_success(lang('success add user', $user->getDisplayName()));
			  ajx_current("back");

			} catch(Exception $e) {
				DB::rollback();
				ajx_current("empty");
				flash_error($e->getMessage());
			} // try

		} // if

	} // add
	
	/**
	 * Creates an user (called from add_user)
	 *
	 * @param User $user
	 * @param array $user_data
	 * @param boolean $is_admin
	 * @param string $permissionsString
	 * @return User $user
	 */
	function createUser($user, $user_data, $is_admin, $permissionsString) {
		if (!$user) {
			$user = new User();
			$user->setFromAttributes($user_data);
		}
		// Generate random password
		if(array_var($user_data, 'password_generator') == 'random') {
			$password = UserPasswords::generateRandomPassword();

			// Validate user input
		} else {
			$password = array_var($user_data, 'password');
			if(trim($password) == '') {
				throw new Error(lang('password value required'));
			} // if
			if($password <> array_var($user_data, 'password_a')) {
				throw new Error(lang('passwords dont match'));
			} // if
		} // if
				
		$user->setPassword($password);

		DB::beginWork();
		
		$user_password = new UserPassword();
		$user_password->setUserId($user->getId());
		$user_password->setPasswordDate(DateTimeValueLib::now());
		$user_password->setPassword(cp_encrypt($password, $user_password->getPasswordDate()->getTimestamp()));
		$user_password->password_temp = $password;
		$user_password->save();
		
		$user->save();
		$user_password->setUserId($user->getId());
		$user_password->save();
		
		if ($is_admin) {
			$user->setAsAdministrator();
		}

		/* create contact for this user*/
		if (array_var($user_data, 'create_contact')) {
			// if contact with same email exists take it, else create new
			$contact = Contacts::getByEmail($user->getEmail(), true);
			if (!$contact instanceof Contact) {
				$contact = new Contact();
				$contact->setEmail($user->getEmail());
			} else if ($contact->isTrashed()) {
				$contact->untrash();
			}
			$contact->setFirstname($user->getDisplayName());
			$contact->setUserId($user->getId());
			$contact->setTimezone($user->getTimezone());
			$contact->setCompanyId($user->getCompanyId());
			$contact->save();
		} else {
			// if contact with same email exists use it as user's contact, without changing it
			$contact = Contacts::getByEmail($user->getEmail(), true);
			if ($contact instanceof Contact) {
				$contact->setUserId($user->getId());
				if ($contact->isTrashed()) $contact->untrash();
			}
		}

		/* create personal project */
		$project = new Project();
		$project->setName($user->getUsername().'_personal');
		$project->setDescription(lang('files'));
		$project->setCreatedById($user->getId());

		$project->save(); //Save to set an ID number
		$project->setP1($project->getId()); //Set ID number to the first project
		$project->save();
		
		$new_project = $project;
		$user->setPersonalProjectId($project->getId());
		$user->save();

		ApplicationLogs::createLog($user, null, ApplicationLogs::ACTION_ADD);

		$project_user = new ProjectUser();
		$project_user->setProjectId($project->getId());
		$project_user->setUserId($user->getId());
		$project_user->setCreatedById($user->getId());
		$project_user->setAllPermissions(true);
		
		$project_user->save();
		/* end personal project */

	  	//TODO - Make batch update of these permissions
		if ($permissionsString && $permissionsString != ''){
			$permissions = json_decode($permissionsString);
		} else $permissions = null;
	  	if(is_array($permissions)) {
	  		foreach($permissions as $perm){			  			
	  			if(ProjectUser::hasAnyPermissions($perm->pr,$perm->pc)){	
		  			$relation = new ProjectUser();
			  		$relation->setProjectId($perm->wsid);
			  		$relation->setUserId($user->getId());
		  			
			  		$relation->setCheckboxPermissions($perm->pc);
			  		$relation->setRadioPermissions($perm->pr);
			  		$relation->save();
	  			}
	  		}
		  } // if

		  // update contact info if user was created from a contact
		  $contact_id = array_var($user_data,'contact_id');
		  if($contact_id && $contact = Contacts::findById($contact_id)){
		  	 if($contact->getUserId()==0){
			  	 $contact->setUserId($user->getId());
			  	 $contact->save();
		  	 }
		  }
		  // End: update contact info if user was created from a contact
		  DB::commit();
		  if (logged_user()->isProjectUser($new_project)) {
		  	evt_add("workspace added", array(
				"id" => $new_project->getId(),
				"name" => $new_project->getName(),
				"color" => $new_project->getColor()
			));
		  }

		  // Send notification...
		  try {
		  	if(array_var($user_data, 'send_email_notification')) {
		  		Notifier::newUserAccount($user, $password);
		  	} // if
		  } catch(Exception $e) {
		
		  } // try
		  return $user;
	}

	/**
	 * Delete specific user
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function delete() {
		$user = Users::findById(get_id());
		if(!($user instanceof User)) {
			flash_error(lang('user dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$user->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		try {

			DB::beginWork();
			$project = $user->getPersonalProject();
			$user->delete();
			if ($project instanceof Project) {
				$pid = $project->getId();
				if ($project->delete()){
					evt_add("workspace deleted", array(
						"id" => $pid
		  			));
				}
			}
			ApplicationLogs::createLog($user, null, ApplicationLogs::ACTION_DELETE);
			DB::commit();
			
			flash_success(lang('success delete user', $user->getDisplayName()));

			ajx_current("reload");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete user'));
			ajx_current("empty");
		} // try
	} // delete

	/**
	 * Create a contact with the data of a user
	 *
	 */
	function create_contact_from_user(){
		ajx_current("empty");
		$user = Users::findById(get_id());
		if(!($user instanceof User)) {
			flash_error(lang('user dnx'));
			return;
		} // if

		if(!logged_user()->canSeeUser($user)) {
			flash_error(lang('no access permissions'));
			return;
		} // if
		
		if($user->getContact()){
			flash_error(lang('user has contact'));
			return;			
		}
		
		try{
			DB::beginWork();
			$contact = new Contact();
			$contact->setFirstname($user->getDisplayName());
			$contact->setUserId($user->getId());
			$contact->setEmail($user->getEmail());
			$contact->setTimezone($user->getTimezone());
			$contact->setCompanyId($user->getCompanyId());
			$contact->save();
			DB::commit();
			$this->redirectTo('contact','card',array('id'=>$contact->getId()));
		}
		catch (Exception  $exx){
			flash_error(lang('error add contact from user') . $exx->getMessage());
		}
	}
	
	/**
	 * Show user card
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function card() {

		$user = Users::findById(get_id());
		if(!($user instanceof User)) {
			flash_error(lang('user dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!logged_user()->canSeeUser($user)) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		
		$pids = null;
		if (active_project() instanceof Project)
			$pids = active_project()->getAllSubWorkspacesCSV();
		$logs = ApplicationLogs::getOverallLogs(false,false,$pids,15,0,get_id());

		tpl_assign('logs', $logs);
		tpl_assign('user', $user);
		ajx_set_no_toolbar(true);
		ajx_extra_data(array("title" => $user->getDisplayName(), 'icon'=>'ico-user'));
	} // card

	
	function list_users() {
		$this->setTemplate(get_template_path("json"));
		ajx_current("empty");
		$usr_data = array();
		$users = Users::getAll();
		if ($users) {
			foreach ($users as $usr) {
				$usr_data[] = array(
					"id" => $usr->getId(),
					"name" => $usr->getDisplayName()
				);
			}
		}
		$extra = array();
		$extra['users'] = $usr_data;
		ajx_extra_data($extra);
	}
	
	
	/**
	 * Show and process config category form
	 *
	 * @param void
	 * @return null
	 */
	function update_category() {
		// Access permissios
		if(!can_manage_configuration(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return ;
		} // if
		$category = ConfigCategories::findById(get_id());
		if(!($category instanceof ConfigCategory)) {
			flash_error(lang('config category dnx'));
			$this->redirectToReferer(get_url('administration'));
		} // if

		if($category->isEmpty()) {
			flash_error(lang('config category is empty'));
			$this->redirectToReferer(get_url('administration'));
		} // if

		$options = $category->getOptions(false);
		$categories = ConfigCategories::getAll(false);

		tpl_assign('category', $category);
		tpl_assign('options', $options);
		tpl_assign('config_categories', $categories);

		$submited_values = array_var($_POST, 'options');
		if(is_array($submited_values)) {
			foreach($options as $option) {
				$new_value = array_var($submited_values, $option->getName());
				if(is_null($new_value) || ($new_value == $option->getValue())) continue;

				$option->setValue($new_value);
				$option->save();
			} // foreach
			flash_success(lang('success update config category', $category->getDisplayName()));
			ajx_current("back");
		} // if

	} // update_category

	/**
	 * List user preferences
	 *
	 */
	function list_user_categories(){	
		tpl_assign('config_categories', UserWsConfigCategories::getAll());	
	} //list_preferences

	/**
	 * List user preferences
	 *
	 */
	function update_user_preferences(){
		$category = UserWsConfigCategories::findById(get_id());
		if(!($category instanceof UserWsConfigCategory)) {
			flash_error(lang('config category dnx'));
			$this->redirectToReferer(get_url('user','card'));
		} // if

		if($category->isEmpty()) {
			flash_error(lang('config category is empty'));
			$this->redirectToReferer(get_url('user','card'));
		} // if

		$options = $category->getUserWsOptions(false);
		$categories = UserWsConfigCategories::getAll(false);

		tpl_assign('category', $category);
		tpl_assign('options', $options);
		tpl_assign('config_categories', $categories);

		$submited_values = array_var($_POST, 'options');
		if(is_array($submited_values)) {
			try{
				DB::beginWork();
				foreach($options as $option) {
					$new_value = array_var($submited_values, $option->getName());
					if(is_null($new_value) || ($new_value == $option->getUserValue(logged_user()->getId()))) continue;
	
					$option->setUserValue($new_value, logged_user()->getId());
					$option->save();
					evt_add("user config ".$option->getName()." changed", $new_value);
				} // foreach
				DB::commit();
				flash_success(lang('success update config value', $category->getDisplayName()));
				ajx_current("back");
			}
			catch (Exception $ex){
				DB::rollback();
				flash_success(lang('error update config value', $category->getDisplayName()));
			}
		} // if
	} //list_preferences
	
} // UserController

?>