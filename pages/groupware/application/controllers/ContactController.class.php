<?php

/**
 * Contact controller
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>,  Marcos Saiz <marcos.saiz@opengoo.org>
 */
class ContactController extends ApplicationController {

	/**
	 * Construct the ContactController
	 *
	 * @access public
	 * @param void
	 * @return ContactController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct

	/**
	 * Creates a system user, receiving a Contact id
	 *
	 */
	function create_user(){
		$contact = Contacts::findById(get_id());
		if(!($contact instanceof Contact)) {
			flash_error(lang('contact dnx'));
			ajx_current("empty");
			return;
		} // if
		
		if(!can_manage_security(logged_user())){
			flash_error(lang('no permissions'));
			ajx_current("empty");
			return;
		} // if
		
		$company = Companies::findById($contact->getCompanyId());
		if(!($company instanceof Company)) {
			flash_error(lang('company dnx') .'. ' . lang('users must belong to a company'));
			ajx_current("empty");
			return;
		} // if
		$this->redirectTo('user','add',array('company_id' => $company->getId(), 'contact_id' => $contact->getId()));
		
	}
	
	/**
	 * Lists all contacts and clients
	 *
	 */
	function list_all()
	{
		ajx_current("empty");
		
		// Get all variables from request
		$start = array_var($_GET,'start', 0);
		$limit = array_var($_GET,'limit', config_option('files_per_page'));
		$page = 1;
		if ($start > 0){
			$page = ($start / $limit) + 1;
		}
		$order = array_var($_GET,'sort');
		$order_dir = array_var($_GET,'dir');
		$tag = array_var($_GET,'tag');
		$action = array_var($_GET,'action');
		$attributes = array(
			"ids" => explode(',', array_var($_GET,'ids')),
			"types" => explode(',', array_var($_GET,'types')),
			"tag" => array_var($_GET,'tagTag'),
			"accountId" => array_var($_GET,'account_id'),
			"viewType" => array_var($_GET,'view_type')
		);
		
		//Resolve actions to perform
		$actionMessage = array();
		if (isset($action)) {
			$actionMessage = $this->resolveAction($action, $attributes);
			if ($actionMessage["errorCode"] == 0) {
				flash_success($actionMessage["errorMessage"]);
			} else {
				flash_error($actionMessage["errorMessage"]);
			}
		} 
		
		// Get all emails and companies to contacts
		$pid = array_var($_GET, 'active_project', 0);
		$project = Projects::findById($pid);
		/*$contacts = $this->getContacts($tag, $attributes, $project);
		$companies = array();
		$companies = $this->getCompanies($tag, $attributes, $project);
		$union = $this->addContactsAndCompanies($contacts, $companies);*/
		
		$type = null;
		if ($attributes['viewType'] == 'contacts')
			$type = 'Contacts';
		else if ($attributes['viewType'] == 'companies')
			$type = 'Companies';
		
		$count = $this->countContactObjects($tag, $type, active_project());
		if ($start > $count){
			$start = 0;
			$page = 1;
		}
		if ($count > 0)
			$union = $this->getContactObjects($page,$limit,$tag,$order,$order_dir,$type,active_project());
		else $union = array();
		// Prepare response object
		$object = $this->newPrepareObject($union, $count, $start, $attributes);
		ajx_extra_data($object);
    	tpl_assign("listing", $object);
	}
	
	
	private static function getContactQueries($project = null, $tag = null, $count = false, $order=null){
		switch ($order){
			case 'updatedOn':
				$order_crit_companies = 'updated_on';
				$order_crit_contacts = 'updated_on';
				break;
			case 'createdOn':
				$order_crit_companies = 'created_on';
				$order_crit_contacts = 'created_on';
				break;
			default:
				$order_crit_contacts = "TRIM(CONCAT(' ', `lastname`, `firstname`, `middlename`))";
				$order_crit_companies = 'name';
				break;
		}
		if (isset($project)) {
    		$proj_ids = $project->getAllSubWorkspacesCSV(true, logged_user());
    	} else {
    		$proj_ids = logged_user()->getActiveProjectIdsCSV();
    		if ($proj_ids && $proj_ids != '')
    			$proj_ids .= ',';
    		$proj_ids .= '0';
    	}
    	
    	$proj_cond_companies = ' `id` IN (SELECT `object_id` FROM `'.TABLE_PREFIX.'workspace_objects` WHERE `object_manager` = \'Companies\' AND `workspace_id` IN ('.$proj_ids.'))';
    	$proj_cond_contacts = ' `project_id` IN (' . $proj_ids . ')';
    	
    	// show companies with no workspace when viewing All
    	if (!isset($project)) {
    		$proj_cond_companies = 'true';
    	} 
    	
    	if(isset($tag) && $tag && $tag!='')
    		$tag_str = " AND EXISTS (SELECT * FROM `" . TABLE_PREFIX . "tags` `t` WHERE `tag`=".DB::escape($tag)." AND `co`.`id` = `t`.`rel_object_id` AND `t`.`rel_object_manager` = `object_manager_value`) ";
    	else
    		$tag_str= ' ';
    	$res = array();
    	
		$permissions = ' AND ( ' . permissions_sql_for_listings(Companies::instance(), ACCESS_LEVEL_READ, logged_user(), '`project_id`', '`co`') .')';
		$res['Companies'] = "SELECT  $order_crit_companies AS `order_value`, 'Companies' AS `object_manager_value`, `id` as `oid` FROM `" . 
					TABLE_PREFIX . "companies` `co` WHERE `trashed_by_id` = 0 AND " .$proj_cond_companies . str_replace('= `object_manager_value`', "= 'Companies'", $tag_str) . $permissions;
					
		$permissions = ' AND ( ' . permissions_sql_for_listings(Contacts::instance(), ACCESS_LEVEL_READ, logged_user(), '`project_id`', '`co`') . ')';
		if (isset($project)) {
			$res['Contacts'] = "SELECT $order_crit_contacts AS `order_value`, 'Contacts' AS `object_manager_value`, `id` AS `oid` FROM `" . 
					TABLE_PREFIX . "contacts` `co` WHERE `trashed_by_id` = 0 AND EXISTS (SELECT * FROM `" . 
					TABLE_PREFIX . "project_contacts` `pc` WHERE `pc`.`contact_id` = `co`.`id` AND ".$proj_cond_contacts. ")" .
					str_replace('= `object_manager_value`', "= 'Contacts'", $tag_str) . $permissions;
		} else{
			$res['Contacts'] = "SELECT $order_crit_contacts AS `order_value`, 'Contacts' AS `object_manager_value`, `id` AS `oid` FROM `" . 
					TABLE_PREFIX . "contacts` `co` WHERE `trashed_by_id` = 0 " . str_replace('= `object_manager_value`', "= 'Contacts'", $tag_str) . $permissions;
		}
		
		if($count){
			foreach ($res as $p => $q){
				$res[$p] ="SELECT count(*) AS `quantity`, '$p' AS `objectName` FROM ( $q ) `table_alias`";
			}
		}
		return $res;
	}
	
	function countContactObjects($tag = null, $type = null, $project = null){
    	$queries = $this->getContactQueries($project, $tag, true);
		if(isset($type) && $type){
			$query = $queries[$type];
		} //if $type
		else {
			$query = '';
			foreach ($queries as $q){
				if($query == '')
					$query = $q;
				else 
					$query .= " \n UNION \n" . $q;
			}
		}
		$ret = 0;
    	//echo $query;die();
		$res = DB::execute($query);	
    	if(!$res)  return $ret;
    	$rows=$res->fetchAll();
		if(!$rows) return  $ret;	
    	foreach ($rows as $row){
    		if(isset($row['quantity']))
    			$ret += $row['quantity'];
    	}//foreach
    	return $ret;
	}

	private function getContactObjects($page, $objects_per_page, $tag=null, $order=null, $order_dir=null, $type = null, $project = null){
    	$queries = $this->getContactQueries($project, $tag, false, $order);
		if (!$order_dir){
			switch ($order){
				case 'name': $order_dir = 'ASC'; break;
				default: $order_dir = 'DESC';
			}
		}
		if(isset($type) && $type){
			$query = $queries[$type];
		} //if $type
		else {
			$query = '';
			foreach ($queries as $q){
				if($query == '')
					$query = $q;
				else 
					$query .= " \n UNION \n" . $q;
			}

		}
		$query .= " ORDER BY order_value $order_dir ";
		if($page && $objects_per_page){
			$start=($page-1) * $objects_per_page ;
			$query .=  " LIMIT " . $start . "," . $objects_per_page. " ";
		}		
		elseif($objects_per_page)
			$query .= " LIMIT " . $objects_per_page;
		
    	$res = DB::execute($query);
    	$objects = array();
    	if(!$res)  return $objects;
    	$rows=$res->fetchAll();
    	if(!$rows)  return $objects;
    	$i=1;
    	foreach ($rows as $row){
    		$manager= $row['object_manager_value'];
    		$id = $row['oid'];
    		if($id && $manager){
    			$obj=get_object_by_manager_and_id($id,$manager);    			
    			if($obj->canView(logged_user())){
    				$objects[]=$obj;
    			}
    		} //if($id && $manager)
    	}//foreach
    	return $objects;
    }
	
	/**
	 * Resolve action to perform
	 *
	 * @param string $action
	 * @param array $attributes
	 * @return string $message
	 */
	private function resolveAction($action, $attributes){
		$resultMessage = "";
		$resultCode = 0;
		switch ($action){
			case "delete":
				$succ = 0; $err = 0;
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$type = $attributes["types"][$i];
					
					switch ($type){
						case "contact":
							$contact = Contacts::findById($id);
							if (isset($contact) && $contact->canDelete(logged_user())){
								try{
									DB::beginWork();
									$contact->trash();
									DB::commit();
									ApplicationLogs::createLog($contact, $contact->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
									$succ++;
								} catch(Exception $e){
									DB::rollback();
									$err++;
								}
							} else {
								$err++;
							}
							break;
							
						case "company":
							$company = Companies::findById($id);
							if (isset($company)) {
								if ($company->canDelete(logged_user())) {
									try{
										DB::beginWork();
										$company->trash();									
										DB::commit();
										ApplicationLogs::createLog($company, $company->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
										$succ++;
									} catch(Exception $e){
										DB::rollback();
										$err++;
									}
								} else {
									$err++;
								}
							};
							break;
							
						default:
							$err++;
							break;
					}; // switch
				}; // for
				if ($err > 0) {
					$resultCode = 2;
					$resultMessage = lang("error delete objects", $err) . "<br />" . ($succ > 0 ? lang("success delete objects", $succ) : "");
				} else {
					$resultMessage = lang("success delete objects", $succ);
				}
				break;
						
			case "tag":
				$tag = $attributes["tag"];
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$type = $attributes["types"][$i];
					switch ($type){
						case "contact":
							$contact = Contacts::findById($id);
							if (isset($contact) && $contact->canEdit(logged_user())){
								Tags::addObjectTag($tag, $contact);
								ApplicationLogs::createLog($contact, $contact->getWorkspaces(), ApplicationLogs::ACTION_TAG,false,null,true,$tag);
								$resultMessage = lang("success tag objects", '');
							};
							break;

						case "company":
							$company = Companies::findById($id);
							if (isset($company) && $company->canEdit(logged_user())){
								Tags::addObjectTag($tag, $company);
								ApplicationLogs::createLog($company, $company->getWorkspaces(), ApplicationLogs::ACTION_TAG,false,null,true,$tag);
								$resultMessage = lang("success tag objects", '');
							};
							break;

						default:
							$resultMessage = lang("unimplemented type" .": '" . $type . "'");// if
							$resultCode = 2;
							break;
					}; // switch
				}; // for
				break;

			default:
				$resultMessage = lang("unimplemented action" . ": '" . $action . "'");// if 
				$resultCode = 2;	
				break;		
		} // switch
		return array("errorMessage" => $resultMessage, "errorCode" => $resultCode);
	}
	
		
	/**
	 * Prepares return object for a list of emails and messages
	 *
	 * @param array $totMsg
	 * @param integer $start
	 * @param integer $limit
	 * @return array
	 */
	private function newPrepareObject($objects, $count, $start = 0, $attributes = null)
	{
		$object = array(
			"totalCount" => $count,
			"start" => $start,
			"contacts" => array()
		);
		for ($i = 0; $i < count($objects); $i++){
			if (isset($objects[$i])){
				$c= $objects[$i];
					
				if ($c instanceof Contact){						
					$roleName = "";
					$roleTags = "";
					$project = active_project();
					if ($project) {
						$role = $c->getRole($project);
						if ($role instanceof ProjectContact) {
							$roleName = $role->getRole();
						}
					}
					$company = $c->getCompany();
					$companyName = '';
					if (!is_null($company))
					$companyName= $company->getName();
					$object["contacts"][] = array(
						"id" => $i,
						"object_id" => $c->getId(),
						"type" => 'contact',
						"wsIds" => $c->getProjectIdsCSV(logged_user()->getActiveProjectIdsCSV()),
    					"workspaceColors" => $c->getWorkspaceColorsCSV(logged_user()->getActiveProjectIdsCSV()),
						"name" => $c->getReverseDisplayName(),
						"email" => $c->getEmail(),
						"companyId" => $c->getCompanyId(),
						"companyName" => $companyName,
						"website" => $c->getHWebPage(),
						"jobTitle" => $c->getJobTitle(),
				    	"role" => $roleName,
						"tags" => project_object_tags($c),
						"department" => $c->getDepartment(),
						"email2" => $c->getEmail2(),
						"email3" => $c->getEmail3(),
						"workWebsite" => $c->getWWebPage(),
						"workAddress" => $c->getFullWorkAddress(),
						"workPhone1" => $c->getWPhoneNumber(),
						"workPhone2" => $c->getWPhoneNumber2(),
						"homeWebsite" => $c->getHWebPage(),
						"homeAddress" => $c->getFullHomeAddress(),
						"homePhone1" => $c->getHPhoneNumber(),
						"homePhone2" => $c->getHPhoneNumber2(),
						"mobilePhone" =>$c->getHMobileNumber(),
						"createdOn" => $c->getCreatedOn() instanceof DateTimeValue ? $c->getCreatedOn()->getTimestamp() : 0,
						"createdBy" => $c->getCreatedByDisplayName(),
						"createdById" => $c->getCreatedById(),
						"updatedOn" => $c->getUpdatedOn() instanceof DateTimeValue ? $c->getUpdatedOn()->getTimestamp() : 0,
						"updatedBy" => $c->getUpdatedByDisplayName(),
						"updatedById" => $c->getUpdatedById()
					);
				} else if ($c instanceof Company ){					
					$roleName = "";
					$roleTags = "";
//					$project = active_project();
//					if ($project ) {
//						$role = $c->getRole($project);
//						if ($role instanceof ProjectContact) {
//							$roleName = $role->getRole();
//						}
//					}
//					$company = $c->getCompany();
//					$companyName = '';
					if (!is_null($c))
					$companyName= $c->getName();
					$object["contacts"][] = array(
						"id" => $i,
						"object_id" => $c->getId(),
						"type" => 'company',
						"wsIds" => $c->getWorkspacesIdsCSV(logged_user()->getActiveProjectIdsCSV()),
    					"workspaceColors" => $c->getWorkspaceColorsCSV(logged_user()->getActiveProjectIdsCSV()),
						'name' => $c->getName(),
						'email' => $c->getEmail(),
						'website' => $c->getHomepage(),
						'workPhone1' => $c->getPhoneNumber(),
          				'workPhone2' => $c->getFaxNumber(),
          				'workAddress' => $c->getAddress() . ' - ' . $c->getAddress2(),
						"companyId" => $c->getId(),
						"companyName" => $c->getName(),
						"jobTitle" => '',
				    	"role" => lang('company'),
						"tags" => project_object_tags($c),
						"department" => lang('company'),
						"email2" => '',
						"email3" => '',
						"workWebsite" => $c->getHomepage(),
						"homeWebsite" => '',
						"homeAddress" => '',
						"homePhone1" => '',
						"homePhone2" => '',
						"mobilePhone" =>'',
						"createdOn" => $c->getCreatedOn() instanceof DateTimeValue ? $c->getCreatedOn()->getTimestamp() : 0,
						"createdBy" => $c->getCreatedByDisplayName(),
						"createdById" => $c->getCreatedById(),
						"updatedOn" => $c->getUpdatedOn() instanceof DateTimeValue ? $c->getUpdatedOn()->getTimestamp() : 0,
						"updatedBy" => $c->getUpdatedByDisplayName(),
						"updatedById" => $c->getUpdatedById()
					);
				}
    		}
		}
		return $object;
	}

	/**
	 * View single contact
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function view() {
		$this->card();
	} // view

	/**
	 * View single contact
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function card() {
		$contact = Contacts::findById(get_id());
		if(!$contact || !$contact->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		$roles = ProjectContacts::getRolesByContact($contact);
		if (isset($roles))
		{
			foreach ($roles as $role)
			{
				$tags[$role->getProjectId()] = $role->getTagNames();
			}
		}

		tpl_assign('contact', $contact);
		if(($uid = $contact->getUserId()) && ($usr = Users::findById($uid)))
			tpl_assign('user', $usr);
		if (isset($roles))
		tpl_assign('roles',$roles);
		if (isset($tags))
		tpl_assign('tags',$tags);
		ajx_extra_data(array("title" => $contact->getDisplayName(), 'icon'=>'ico-contact'));
		ajx_set_no_toolbar(true);
	} // view

	/**
	 * Add contact
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function add() {
		if (active_project() instanceof Project) {
			tpl_assign('isAddProject',true);
		}
		$this->setTemplate('edit_contact');

		if(!Contact::canAdd(logged_user(),active_project())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$contact = new Contact();		
		$im_types = ImTypes::findAll(array('order' => '`id`'));
		$contact_data = array_var($_POST, 'contact');
		if(!array_var($contact_data,'company_id')){
//			$company_id = get_id('company_id');
//			if($company_id && ( Companies::findById($company_id) instanceof Company) ) {
				$contact_data['company_id'] = get_id('company_id');
				$contact_data['timezone'] = logged_user()->getTimezone();
//			}	
		}
		$redirect_to = get_url('contact');

		tpl_assign('contact', $contact);
		tpl_assign('contact_data', $contact_data);
		tpl_assign('im_types', $im_types);

		if(is_array(array_var($_POST, 'contact'))) {
			ajx_current("empty");
			try {
				DB::beginWork();
				
				$newCompany = false;
				if (array_var($contact_data, 'isNewCompany') == 'true' && is_array(array_var($_POST, 'company'))){
					$company_data = array_var($_POST, 'company');
					$company = new Company();
					$company->setFromAttributes($company_data);
					$company->setClientOfId(1);
					
					$company->save();
					ApplicationLogs::createLog($company, null, ApplicationLogs::ACTION_ADD);
					$newCompany = true;
				}
				
				$contact_data['o_birthday'] = getDateValue($contact_data["o_birthday_value"]);
				
				$contact->setFromAttributes($contact_data);

				if($newCompany)
					$contact->setCompanyId($company->getId());
				$contact->setIsPrivate(false);

				//link it!
			    $object_controller = new ObjectController();
			    $object_controller->link_to_new_object($contact);								
				
				$contact->save();
				$contact->setTagsFromCSV(array_var($contact_data, 'tags'));
				
				$object_controller->add_subscribers($contact);
				$object_controller->add_custom_properties($contact);
				
				foreach($im_types as $im_type) {
					$value = trim(array_var($contact_data, 'im_' . $im_type->getId()));
					if($value <> '') {

						$contact_im_value = new ContactImValue();

						$contact_im_value->setContactId($contact->getId());
						$contact_im_value->setImTypeId($im_type->getId());
						$contact_im_value->setValue($value);
						$contact_im_value->setIsDefault(array_var($contact_data, 'default_im') == $im_type->getId());

						$contact_im_value->save();
					} // if
				} // foreach
				
				if(active_project() instanceof Project)
				{
					if(!ProjectContact::canAdd(logged_user(), active_project())) {
						flash_error(lang('error contact added but not assigned', $contact->getDisplayName(), active_project()->getName()));
						ajx_current("start");
						return;
					} // if
					
					$pc = new ProjectContact();
					$pc->setContactId($contact->getId());
					$pc->setProjectId(active_project()->getId());
					$pc->setRole(array_var($contact_data,'role'));
					
					DB::beginWork();
					$pc->save();
					DB::commit();
				}

				ApplicationLogs::createLog($contact, null, ApplicationLogs::ACTION_ADD);
				
				DB::commit();

				flash_success(lang('success add contact', $contact->getDisplayName()));
				ajx_current("back");

				// Error...
			} catch(Exception $e) {
				DB::rollback();
				//tpl_assign('error', $e);
				flash_error($e->getMessage());
			} // try

		} // if
	} // add

	/**
	 * Edit specific contact
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function edit() {
		$this->setTemplate('edit_contact');
		
		if (active_project() instanceof Project) {
			tpl_assign('isAddProject',true);
		}

		$contact = Contacts::findById(get_id());
		if(!($contact instanceof Contact)) {
			flash_error(lang('contact dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$contact->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$im_types = ImTypes::findAll(array('order' => '`id`'));
		$active_project = active_project();
		$role = "" ;
		if($active_project){
			$pc = $contact->getRole(active_project());
			if ($pc instanceof ProjectContact) {
				$role = $pc->getRole();
			}
		}
		
		$contact_data = array_var($_POST, 'contact');
		if(!is_array($contact_data)) {
			$tag_names = $contact->getTagNames();
			$contact_data = array(
          	'firstname' => $contact->getFirstName(),
          	'lastname' => $contact->getLastName(),
			'middlename'=> $contact->getMiddleName(), 
          	'department' => $contact->getDepartment(),
          	'job_title' => $contact->getJobTitle(),
            'email' => $contact->getEmail(),
            'email2' => $contact->getEmail2(),
            'email3' => $contact->getEmail3(),
			'w_web_page'=> $contact->getWWebPage(), 
			'w_address'=> $contact->getWAddress(), 
			'w_city'=> $contact->getWCity(), 
			'w_state'=> $contact->getWState(), 
			'w_zipcode'=> $contact->getWZipcode(), 
			'w_country'=> $contact->getWCountry(), 
			'w_phone_number'=> $contact->getWPhoneNumber(), 
			'w_phone_number2'=> $contact->getWPhoneNumber2(), 
			'w_fax_number'=> $contact->getWFaxNumber(), 
			'w_assistant_number'=> $contact->getWAssistantNumber(), 
			'w_callback_number'=> $contact->getWCallbackNumber(), 

			'h_web_page'=> $contact->getHWebPage(), 
			'h_address'=> $contact->getHAddress(), 
			'h_city'=> $contact->getHCity(), 
			'h_state'=> $contact->getHState(), 
			'h_zipcode'=> $contact->getHZipcode(), 
			'h_country'=> $contact->getHCountry(), 
			'h_phone_number'=> $contact->getHPhoneNumber(), 
			'h_phone_number2'=> $contact->getHPhoneNumber2(), 
			'h_fax_number'=> $contact->getHFaxNumber(), 
			'h_mobile_number'=> $contact->getHMobileNumber(), 
			'h_pager_number'=> $contact->getHPagerNumber(), 

			'o_web_page'=> $contact->getOWebPage(), 
			'o_address'=> $contact->getOAddress(), 
			'o_city'=> $contact->getOCity(), 
			'o_state'=> $contact->getOState(), 
			'o_zipcode'=> $contact->getOZipcode(), 
			'o_country'=> $contact->getOCountry(), 
			'o_phone_number'=> $contact->getOPhoneNumber(), 
			'o_phone_number2'=> $contact->getOPhoneNumber2(), 
			'o_fax_number'=> $contact->getOFaxNumber(), 
			'o_birthday'=> $contact->getOBirthday(), 
          	'picture_file' => $contact->getPictureFile(),
          	'timezone' => $contact->getTimezone(),
          	'notes' => $contact->getNotes(),
          	'is_private' => $contact->getIsPrivate(),
          	'company_id' => $contact->getCompanyId(),
      	    'role' => $role,
      	    'tags' => is_array($tag_names) ? implode(', ', $tag_names) : '',
      	    
      	    ); // array

      	    if(is_array($im_types)) {
      	    	foreach($im_types as $im_type) {
      	    		$contact_data['im_' . $im_type->getId()] = $contact->getImValue($im_type);
      	    	} // forech
      	    } // if

      	    $default_im = $contact->getDefaultImType();
      	    $contact_data['default_im'] = $default_im instanceof ImType ? $default_im->getId() : '';
		} // if

		tpl_assign('contact', $contact);
		tpl_assign('contact_data', $contact_data);
		tpl_assign('im_types', $im_types);

		if(is_array(array_var($_POST, 'contact'))) {
			try {
				DB::beginWork();
				
				$newCompany = false;
				if (array_var($contact_data, 'isNewCompany') == 'true' && is_array(array_var($_POST, 'company'))){
					$company_data = array_var($_POST, 'company');
					$company = new Company();
					$company->setFromAttributes($company_data);
					$company->setClientOfId(1);
					
					$company->save();
					ApplicationLogs::createLog($company, null, ApplicationLogs::ACTION_ADD );
					$newCompany = true;
				}
				
				$contact_data['o_birthday'] = getDateValue($contact_data["o_birthday_value"]);
				
				$contact->setFromAttributes($contact_data);
				
				/*if (!is_null($contact->getOBirthday()) && $contact_data["o_birthday_year"] == 0){
					$contact->setOBirthday(null);
				} else if ($contact_data["o_birthday_year"] != 0) {
					$bday = new DateTimeValue(0);
					$bday->setYear($contact_data["o_birthday_year"]);
					$bday->setMonth($contact_data["o_birthday_month"]);
					$bday->setDay($contact_data["o_birthday_day"]);
					$contact->setOBirthday($bday);
				}*/

				if($newCompany)
					$contact->setCompanyId($company->getId());

				$contact->save();
				$contact->setTagsFromCSV(array_var($contact_data, 'tags'));
				$contact->clearImValues();

				foreach($im_types as $im_type) {
					$value = trim(array_var($contact_data, 'im_' . $im_type->getId()));
					if($value <> '') {

						$contact_im_value = new ContactImValue();

						$contact_im_value->setContactId($contact->getId());
						$contact_im_value->setImTypeId($im_type->getId());
						$contact_im_value->setValue($value);
						$contact_im_value->setIsDefault(array_var($contact_data, 'default_im') == $im_type->getId());

						$contact_im_value->save();
					} // if
				} // foreach

				$object_controller = new ObjectController();
			    $object_controller->link_to_new_object($contact);
				$object_controller->add_subscribers($contact);
				$object_controller->add_custom_properties($contact);
				
				ApplicationLogs::createLog($contact, null, ApplicationLogs::ACTION_EDIT );
				
				DB::commit();
				
				if (trim(array_var($contact_data, 'role', '')) != '' && active_project() instanceof Project) {
					if(!ProjectContact::canAdd(logged_user(), active_project())) {
						flash_error(lang('error contact added but not assigned', $contact->getDisplayName(), active_project()->getName()));
						ajx_current("back");
						return;
					} // if
					
					$pc = $contact->getRole(active_project());
					if (!$pc instanceof ProjectContact) {
						$pc = new ProjectContact();
						$pc->setContactId($contact->getId());
						$pc->setProjectId(active_project()->getId());
					}
					$pc->setRole(array_var($contact_data,'role'));
					$pc->save();
//					ApplicationLogs::createLog($contact, $contact->getWorkspaces(), ApplicationLogs::ACTION_ADD);

				}

				flash_success(lang('success edit contact', $contact->getDisplayName()));
				ajx_current("back");

			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
		  		ajx_current("empty");
			} // try
		} // if
	} // edit

	/**
	 * Edit contact picture
	 *
	 * @param void
	 * @return null
	 */
	function edit_picture() {
		$contact = Contacts::findById(get_id());
		if(!($contact instanceof Contact)) {
			flash_error(lang('contact dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$contact->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$redirect_to = array_var($_GET, 'redirect_to');
		if((trim($redirect_to)) == '' || !is_valid_url($redirect_to)) {
			$redirect_to = $contact->getUpdatePictureUrl();
		} // if
		tpl_assign('redirect_to', $redirect_to);

		$picture = array_var($_FILES, 'new_picture');
		tpl_assign('contact', $contact);

		if(is_array($picture)) {
			try {
				if(!isset($picture['name']) || !isset($picture['type']) || !isset($picture['size']) || !isset($picture['tmp_name']) || !is_readable($picture['tmp_name'])) {
					throw new InvalidUploadError($picture, lang('error upload file'));
				} // if

				$valid_types = array('image/jpg', 'image/jpeg', 'image/pjpeg', 'image/gif', 'image/png');
				$max_width   = config_option('max_avatar_width', 50);
				$max_height  = config_option('max_avatar_height', 50);

				if(!in_array($picture['type'], $valid_types) || !($image = getimagesize($picture['tmp_name']))) {
					throw new InvalidUploadError($picture, lang('invalid upload type', 'JPG, GIF, PNG'));
				} // if

				$old_file = $contact->getPicturePath();
				DB::beginWork();

				if(!$contact->setPicture($picture['tmp_name'], $max_width, $max_height)) {
					throw new InvalidUploadError($avatar, lang('error edit picture'));
				} // if

				ApplicationLogs::createLog($contact, null, ApplicationLogs::ACTION_EDIT);
				DB::commit();

				if(is_file($old_file)) {
					@unlink($old_file);
				} // if

				flash_success(lang('success edit picture'));
				
				ajx_current("back");
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // edit_picture

	/**
	 * Delete picture
	 *
	 * @param void
	 * @return null
	 */
	function delete_picture() {
		$contact = Contacts::findById(get_id());
		if(!($contact instanceof Contact)) {
			flash_error(lang('contact dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$contact->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$redirect_to = array_var($_GET, 'redirect_to');
		if((trim($redirect_to)) == '' || !is_valid_url($redirect_to)) {
			$redirect_to = $contact->getUpdatePictureUrl();
		} // if
		tpl_assign('redirect_to', $redirect_to);

		if(!$contact->hasPicture()) {
			flash_error(lang('picture dnx'));
			ajx_current("empty");
			return;
		} // if

		try {
			DB::beginWork();
			$contact->deletePicture();
			$contact->save();
			ApplicationLogs::createLog($contact, $contact->getWorkspaces(), ApplicationLogs::ACTION_EDIT);

			DB::commit();

			flash_success(lang('success delete picture'));
			ajx_current("back");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete picture'));
			ajx_current("empty");
		} // try

	} // delete_picture

	/**
	 * Delete specific contact
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function delete() {
		$contact = Contacts::findById(get_id());
		if(!($contact instanceof Contact)) {
			flash_error(lang('contact dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$contact->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		try {

			DB::beginWork();
			$contact->trash();
			ApplicationLogs::createLog($contact, null, ApplicationLogs::ACTION_TRASH );

			DB::commit();

			flash_success(lang('success delete contact', $contact->getDisplayName()));
			ajx_current("back");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete contact'));
			ajx_current("empty");
		} // try
	} // delete

	function assign_to_project()
	{
		$contact = Contacts::findById(get_id());
		if(!($contact instanceof Contact)) {
			flash_error(lang('contact dnx'));
			ajx_current("empty");
			return;
		} // if
		
		$projects = active_projects();
		$contactRoles = ProjectContacts::getRolesByContact($contact);

		if(!$contact->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$contact_data = array_var($_POST, 'contact');
		$enterData = true;
		if(!is_array($contact_data)) {
			$enterData = false;
			foreach($projects as $project){
				$contact_data['pid_'.$project->getId()] = false;
				$contact_data['role_pid_'.$project->getId()] = '';
				 
				if($contactRoles){
					foreach($contactRoles as $cr){
						if ($project->getId() == $cr->getProjectId()){
							$contact_data['pid_'.$project->getId()] = true;
							$contact_data['role_pid_'.$project->getId()] = $cr->getRole();
						} // if
					} // foreach
				} // if
			} // foreach
		} // if

		if($enterData){
			try {
				DB::beginWork();
				$err = 0; $succ = 0;
				$workspaces = array();
				foreach($projects as $project) {
					$pc = ProjectContacts::getRole($contact, $project);
					if (!ProjectContact::canAdd(logged_user(), $project)) {
						if ($pc instanceof ProjectContact) {
							if (!isset($contact_data['pid_'.$project->getId()])) {
								$err++; // trying to unassign with no permissions
							} else {
								$role = $contact_data['role_pid_'.$project->getId()];
								if ($role != $pc->getRole()) {
									$err++; // trying to edit role with no permissions
								}
							}
						} else {
							if (isset($contact_data['pid_'.$project->getId()])) {
								$err++; // trying to assign contact with no permissions
							}
						}
						$workspaces[] = $project->getName();
						continue;
					} // if
					if(!isset($contact_data['pid_'.$project->getId()])){
						if ($pc instanceof ProjectContact) {
							$pc->delete();
							$succ++;
						}
					} else {
						$role = $contact_data['role_pid_'.$project->getId()];
						if ($pc instanceof ProjectContact) {
							if ($pc->getRole() != $role){
								$pc->setRole($role);
								$pc->save();
								$succ++;
//								ApplicationLogs::createLog($contact, $project, ApplicationLogs::ACTION_EDIT);
							} //if
						} else {
							$pc = new ProjectContact();
							$pc->setProjectId($project->getId());
							$pc->setContactId($contact->getId());
							$pc->setRole($role);
							$pc->save();
							$succ++;
//							ApplicationLogs::createLog($contact, $project, ApplicationLogs::ACTION_EDIT);
						}//if else
					}//if else
				}//foreach
				if ($err == 0) {
					flash_success(lang('success edit contact', $contact->getDisplayName()));
					DB::commit();
					ApplicationLogs::createLog($contact, null, ApplicationLogs::ACTION_EDIT );
					ajx_current("back");
				} else {
					flash_error(lang('failed to assign contact due to permissions', implode(", ", $workspaces)));
					DB::rollback();
					ajx_current("empty");
				}
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if

		tpl_assign('contact', $contact);
		tpl_assign('contact_data', $contact_data);
		tpl_assign('projects', $projects);
	} // assign_to_project
	
	
	function import_from_csv_file() {
		if (isset($_GET['from_menu']) && $_GET['from_menu'] == 1) unset($_SESSION['history_back']);
		if (isset($_SESSION['history_back'])) {
			unset($_SESSION['history_back']);
			ajx_current("start");
		} else {
			
			if(!Contact::canAdd(logged_user(), active_or_personal_project())) {
				flash_error(lang('no access permissions'));
				ajx_current("empty");
				return;
			} // if
	
			$this->setTemplate('csv_import');
			
			$type = array_var($_GET, 'type', array_var($_SESSION, 'import_type', 'contact')); //type of import (contact - company)
			if (!isset($_SESSION['import_type']) || ($type != $_SESSION['import_type'] && $type != ''))
				$_SESSION['import_type'] = $type;
			tpl_assign('import_type', $type);
			
			$filedata = array_var($_FILES, 'csv_file');
			if (is_array($filedata) && !is_array(array_var($_POST, 'select_contact'))) {
				
				$filename = $filedata['tmp_name'].'.csv';
				copy($filedata['tmp_name'], $filename);
				
				$first_record_has_names = array_var($_POST, 'first_record_has_names', false);
				$delimiter = array_var($_POST, 'delimiter', '');
				if ($delimiter == '') $delimiter = $this->searchForDelimiter($filename);
				
				$_SESSION['delimiter'] = $delimiter;
				$_SESSION['csv_import_filename'] = $filename;
				$_SESSION['first_record_has_names'] = $first_record_has_names;
				
				$titles = $this->read_csv_file($filename, $delimiter, true);
				
				tpl_assign('titles', $titles);
			}
			
			if (array_var($_GET, 'calling_back', false)) {
				$filename = $_SESSION['csv_import_filename'];
				$delimiter = $_SESSION['delimiter'];
				$first_record_has_names = $_SESSION['first_record_has_names'];
				
				$titles = $this->read_csv_file($filename, $delimiter, true);

				unset($_GET['calling_back']);
				tpl_assign('titles', $titles);
			}
			
			if (is_array(array_var($_POST, 'select_contact')) || is_array(array_var($_POST, 'select_company'))) {
				
				$type = $_SESSION['import_type'];
				$filename = $_SESSION['csv_import_filename'];
				$delimiter = $_SESSION['delimiter'];
				$first_record_has_names = $_SESSION['first_record_has_names'];
				
				$registers = $this->read_csv_file($filename, $delimiter);
				
				$import_result = array('import_ok' => array(), 'import_fail' => array());

				$i = $first_record_has_names ? 1 : 0;
				while ($i < count($registers)) {
					try {
						DB::beginWork();
						if ($type == 'contact') {
							$contact_data = $this->buildContactData(array_var($_POST, 'select_contact'), array_var($_POST, 'check_contact'), $registers[$i]);
							$contact_data['import_status'] = '('.lang('updated').')';
							$fname = str_replace("'", "", array_var($contact_data, "firstname"));
							$lname = str_replace("'", "", array_var($contact_data, "lastname"));
							$contact = Contacts::findOne(array("conditions" => "firstname = '".$fname."' AND lastname = '".$lname."' OR email = '".array_var($contact_data, "email")."'"));
							if (!$contact) {
								$contact = new Contact();
								$contact_data['import_status'] = '('.lang('new').')';
							}
							$contact->setFromAttributes($contact_data);
							$contact->save();
							ApplicationLogs::createLog($contact, null, ApplicationLogs::ACTION_ADD);
							$contact->setTagsFromCSV(array_var($_POST, 'tags'));
							
							if(active_project() instanceof Project) {
								$pc = ProjectContacts::findOne(array("conditions" => "contact_id = ".$contact->getId()." AND project_id = ".active_project()->getId()));
								if (!$pc) {
									$pc = new ProjectContact();
									$pc->setContactId($contact->getId());
									$pc->setProjectId(active_project()->getId());
									$pc->setRole(array_var($contact_data,'role'));
									$pc->save();
								}
							}
							$import_result['import_ok'][] = $contact_data;
							
						} else if ($type == 'company') {
							//foreach($registers[$i] as $k=>$v) Logger::log("$k->$v");
							$contact_data = $this->buildCompanyData(array_var($_POST, 'select_company'), array_var($_POST, 'check_company'), $registers[$i]);
							$contact_data['import_status'] = '('.lang('updated').')';
							$company = Companies::findOne(array("conditions" => "name = '".array_var($contact_data, "name")."'"));
							if (!$company) {
								$company = new Company();
								$contact_data['import_status'] = '('.lang('new').')';
							}
							$company->setFromAttributes($contact_data);
							$company->setClientOfId(owner_company()->getId());
							$company->save();
							ApplicationLogs::createLog($company, null, ApplicationLogs::ACTION_ADD);
							$company->setTagsFromCSV(array_var($_POST, 'tags'));
							$company->addToWorkspace(active_or_personal_project());
							
							$import_result['import_ok'][] = $contact_data;
						}

						DB::commit();						
						
					} catch (Exception $e) {
						$contact_data['fail_message'] = substr_utf($e->getMessage(), strpos_utf($e->getMessage(), "\r\n"));
						DB::rollback();
						$import_result['import_fail'][] = $contact_data;
					}		
					$i++;
				}
				unlink($_SESSION['csv_import_filename']);
				unset($_SESSION['csv_import_filename']);
				unset($_SESSION['delimiter']);
				unset($_SESSION['first_record_has_names']);
				unset($_SESSION['import_type']);
				
				$_SESSION['history_back'] = true;
				tpl_assign('import_result', $import_result);
			}
		}
	} // import_from_csv_file

		
	function read_csv_file($filename, $delimiter, $only_first_record = false) {
		$handle = fopen($filename, 'rb');
		if (!$handle) {
			flash_error(lang('file not exists'));
			ajx_current("empty");
			return;
		}
		
		if ($only_first_record) {
			$result = fgetcsv($handle, null, $delimiter);
			$aux = array();
			foreach ($result as $title) $aux[] = mb_convert_encoding($title, "utf-8", detect_encoding($title));
			$result = $aux;			
		} else {
			$result = array();
			while ($fields = fgetcsv($handle, null, $delimiter)) {
				$aux = array();
				foreach ($fields as $field) $aux[] = mb_convert_encoding($field, "utf-8", detect_encoding($field));
				$result[] = $aux;
			}
		}

		fclose($handle);
		return $result;
	} //read_csv_file
	
	private function searchForDelimiter($filename) {
		$delimiterCount = array(',' => 0, ';' => 0);
		
		$handle = fopen($filename, 'rb');
		$str = fgets($handle);
		fclose($handle);
		
		$del = null;
		foreach($delimiterCount as $k => $v) {
			$exploded = explode($k, $str);
			$delimiterCount[$k] = count($exploded);
			if ($del == null || $delimiterCount[$k] > $delimiterCount[$del]) $del = $k;
		}
		return $del;
	}
	
	function buildCompanyData($position, $checked, $fields) {
		$contact_data = array();
		if (isset($checked['name']) && $checked['name']) $contact_data['name'] = array_var($fields, $position['name']);
		if (isset($checked['email']) && $checked['email']) $contact_data['email'] = array_var($fields, $position['email']);
		if (isset($checked['homepage']) && $checked['homepage']) $contact_data['homepage'] = array_var($fields, $position['homepage']);
		if (isset($checked['address']) && $checked['address']) $contact_data['address'] = array_var($fields, $position['address']);
		if (isset($checked['address2']) && $checked['address2']) $contact_data['address2'] = array_var($fields, $position['address2']);
		if (isset($checked['city']) && $checked['city']) $contact_data['city'] = array_var($fields, $position['city']);
		if (isset($checked['state']) && $checked['state']) $contact_data['state'] = array_var($fields, $position['state']);
		if (isset($checked['zipcode']) && $checked['zipcode']) $contact_data['zipcode'] = array_var($fields, $position['zipcode']);
		if (isset($checked['country']) && $checked['country']) $contact_data['country'] = CountryCodes::getCountryCodeByName(array_var($fields, $position['country']));
		if (isset($checked['phone_number']) && $checked['phone_number']) $contact_data['phone_number'] = array_var($fields, $position['phone_number']);
		if (isset($checked['fax_number']) && $checked['fax_number']) $contact_data['fax_number'] = array_var($fields, $position['fax_number']);
		$contact_data['timezone'] = logged_user()->getTimezone();
		
		return $contact_data;
	}
	
	function buildContactData($position, $checked, $fields) {
		$contact_data = array();
		if (isset($checked['firstname']) && $checked['firstname']) $contact_data['firstname'] = array_var($fields, $position['firstname']);
		if (isset($checked['lastname']) && $checked['lastname']) $contact_data['lastname'] = array_var($fields, $position['lastname']);
		if (isset($checked['email']) && $checked['email']) $contact_data['email'] = array_var($fields, $position['email']);
		
		if (isset($checked['w_web_page']) && $checked['w_web_page']) $contact_data['w_web_page'] = array_var($fields, $position['w_web_page']);
		if (isset($checked['w_address']) && $checked['w_address']) $contact_data['w_address'] = array_var($fields, $position['w_address']);
		if (isset($checked['w_city']) && $checked['w_city']) $contact_data['w_city'] = array_var($fields, $position['w_city']);
		if (isset($checked['w_state']) && $checked['w_state']) $contact_data['w_state'] = array_var($fields, $position['w_state']);
		if (isset($checked['w_zipcode']) && $checked['w_zipcode']) $contact_data['w_zipcode'] = array_var($fields, $position['w_zipcode']);
		if (isset($checked['w_country']) && $checked['w_country']) $contact_data['w_country'] = CountryCodes::getCountryCodeByName(array_var($fields, $position['w_country']));
		if (isset($checked['w_phone_number']) && $checked['w_phone_number']) $contact_data['w_phone_number'] = array_var($fields, $position['w_phone_number']);
		if (isset($checked['w_phone_number2']) && $checked['w_phone_number2']) $contact_data['w_phone_number2'] = array_var($fields, $position['w_phone_number2']);
		if (isset($checked['w_fax_number']) && $checked['w_fax_number']) $contact_data['w_fax_number'] = array_var($fields, $position['w_fax_number']);
		if (isset($checked['w_assistant_number']) && $checked['w_assistant_number']) $contact_data['w_assistant_number'] = array_var($fields, $position['w_assistant_number']);
		if (isset($checked['w_callback_number']) && $checked['w_callback_number']) $contact_data['w_callback_number'] = array_var($fields, $position['w_callback_number']);
		
		if (isset($checked['h_web_page']) && $checked['h_web_page']) $contact_data['h_web_page'] = array_var($fields, $position['h_web_page']);
		if (isset($checked['h_address']) && $checked['h_address']) $contact_data['h_address'] = array_var($fields, $position['h_address']);
		if (isset($checked['h_city']) && $checked['h_city']) $contact_data['h_city'] = array_var($fields, $position['h_city']);
		if (isset($checked['h_state']) && $checked['h_state']) $contact_data['h_state'] = array_var($fields, $position['h_state']);
		if (isset($checked['h_zipcode']) && $checked['h_zipcode']) $contact_data['h_zipcode'] = array_var($fields, $position['h_zipcode']);
		if (isset($checked['h_country']) && $checked['h_country']) $contact_data['h_country'] = CountryCodes::getCountryCodeByName(array_var($fields, $position['h_country']));
		if (isset($checked['h_phone_number']) && $checked['h_phone_number']) $contact_data['h_phone_number'] = array_var($fields, $position['h_phone_number']);
		if (isset($checked['h_phone_number2']) && $checked['h_phone_number2']) $contact_data['h_phone_number2'] = array_var($fields, $position['h_phone_number2']);
		if (isset($checked['h_fax_number']) && $checked['h_fax_number']) $contact_data['h_fax_number'] = array_var($fields, $position['h_fax_number']);
		if (isset($checked['h_mobile_number']) && $checked['h_mobile_number']) $contact_data['h_mobile_number'] = array_var($fields, $position['h_mobile_number']);
		if (isset($checked['h_pager_number']) && $checked['h_pager_number']) $contact_data['h_pager_number'] = array_var($fields, $position['h_pager_number']);
		
		if (isset($checked['o_web_page']) && $checked['o_web_page']) $contact_data['o_web_page'] = array_var($fields, $position['o_web_page']);
		if (isset($checked['o_address']) && $checked['o_address']) $contact_data['o_address'] = array_var($fields, $position['o_address']);
		if (isset($checked['o_city']) && $checked['o_city']) $contact_data['o_city'] = array_var($fields, $position['o_city']);
		if (isset($checked['o_state']) && $checked['o_state']) $contact_data['o_state'] = array_var($fields, $position['o_state']);
		if (isset($checked['o_zipcode']) && $checked['o_zipcode']) $contact_data['o_zipcode'] = array_var($fields, $position['o_zipcode']);
		if (isset($checked['o_country']) && $checked['o_country']) $contact_data['o_country'] = CountryCodes::getCountryCodeByName(array_var($fields, $position['o_country']));
		if (isset($checked['o_phone_number']) && $checked['o_phone_number']) $contact_data['o_phone_number'] = array_var($fields, $position['o_phone_number']);
		if (isset($checked['o_phone_number2']) && $checked['o_phone_number2']) $contact_data['o_phone_number2'] = array_var($fields, $position['o_phone_number2']);
		if (isset($checked['o_fax_number']) && $checked['o_fax_number']) $contact_data['o_fax_number'] = array_var($fields, $position['o_fax_number']);
		if (isset($checked['o_birthday']) && $checked['o_birthday']) $contact_data['o_birthday'] = array_var($fields, $position['o_birthday']);
		if (isset($checked['email2']) && $checked['email2']) $contact_data['email2'] = array_var($fields, $position['email2']);
		if (isset($checked['email3']) && $checked['email3']) $contact_data['email3'] = array_var($fields, $position['email3']);
		if (isset($checked['job_title']) && $checked['job_title']) $contact_data['job_title'] = array_var($fields, $position['job_title']);
		if (isset($checked['department']) && $checked['department']) $contact_data['department'] = array_var($fields, $position['department']);
		if (isset($checked['middlename']) && $checked['middlename']) $contact_data['middlename'] = array_var($fields, $position['middlename']);
		if (isset($checked['notes']) && $checked['notes']) $contact_data['notes'] = array_var($fields, $position['notes']);
		          
		$contact_data['is_private'] = false;
		$contact_data['timezone'] = logged_user()->getTimezone();
		
		$contact_data["firstname"] = str_replace("'", "", array_var($contact_data, "firstname"));
		$contact_data["lastname"] = str_replace("'", "", array_var($contact_data, "lastname"));		

		return $contact_data;
	} // buildContactData

	function export_to_csv_file() {
		$this->setTemplate('csv_export');
		
		$type = array_var($_GET, 'type', array_var($_SESSION, 'import_type', 'contact')); //type of import (contact - company)
		tpl_assign('import_type', $type);
		if (!isset($_SESSION['import_type']) || ($type != $_SESSION['import_type'] && $type != ''))
			$_SESSION['import_type'] = $type;
		
		if ($type == 'contact') $checked_fields = array_var($_POST, 'check_contact');
		else $checked_fields = array_var($_POST, 'check_company');
		if (is_array($checked_fields)) {
			$titles = '';
			$imp_type = array_var($_SESSION, 'import_type', 'contact');
			if ($imp_type == 'contact') {
				$field_names = Contacts::getContactFieldNames();
				
				foreach($checked_fields as $k => $v) {
					if (isset($field_names["contact[$k]"]) && $v == 'checked')
						$titles .= $field_names["contact[$k]"] . ',';
				}
				$titles = substr_utf($titles, 0, strlen_utf($titles)-1) . "\n";
			} else {
				$field_names = Companies::getCompanyFieldNames();
				
				foreach($checked_fields as $k => $v) {
					if (isset($field_names["company[$k]"]) && $v == 'checked')
						$titles .= $field_names["company[$k]"] . ',';
				}
				$titles = substr_utf($titles, 0, strlen_utf($titles)-1) . "\n";
			}
			
			$filename = rand().'.tmp';
			$handle = fopen(ROOT.'/tmp/'.$filename, 'wb');
			fwrite($handle, $titles);
			
			if (array_var($_SESSION, 'import_type', 'contact') == 'contact') {
				$contacts = Contacts::getAll();
				foreach ($contacts as $contact) {
					fwrite($handle, $this->build_csv_from_contact($contact, $checked_fields) . "\n");
				}
			} else {
				$companies = Companies::getAll();
				foreach ($companies as $company) {
					fwrite($handle, $this->build_csv_from_company($company, $checked_fields) . "\n");
				}
			}
			
			fclose($handle);

			$_SESSION['contact_export_filename'] = $filename;
			flash_success(($imp_type == 'contact' ? lang('success export contacts') : lang('success export companies')));
			ajx_current("start");
		} else {
			unset($_SESSION['contact_export_filename']);
			return;
		}
	}
	
	function download_exported_file() {
		$filename = array_var($_SESSION, 'contact_export_filename', '');
		if ($filename != '') {
			$path = ROOT.'/tmp/'.$filename;
			$size = filesize($path);
			
			$name = (array_var($_SESSION, 'import_type', 'contact') == 'contact' ? 'contacts.csv' : 'companies.csv');
			unset($_SESSION['contact_export_filename']);
			unset($_SESSION['import_type']);
			download_file($path, 'text/csv', $name, $size, false);
			unlink($path);
			die();			
		} else $this->setTemplate('csv_export');
	}
	
	function build_csv_from_contact(Contact $contact, $checked) {
		$str = '';
		
		if (isset($checked['firstname']) && $checked['firstname'] == 'checked') $str .= $contact->getFirstname() . ',';
		if (isset($checked['lastname']) && $checked['lastname'] == 'checked') $str .= $contact->getLastname() . ',';
		if (isset($checked['email']) && $checked['email'] == 'checked') $str .= $contact->getEmail() . ',';
		
		if (isset($checked['w_web_page']) && $checked['w_web_page'] == 'checked') $str .= $contact->getWWebPage() . ',';
		if (isset($checked['w_address']) && $checked['w_address'] == 'checked') $str .= $contact->getWAddress() . ',';
		if (isset($checked['w_city']) && $checked['w_city'] == 'checked') $str .= $contact->getWCity() . ',';
		if (isset($checked['w_state']) && $checked['w_state'] == 'checked') $str .= $contact->getWState() . ',';
		if (isset($checked['w_zipcode']) && $checked['w_zipcode'] == 'checked') $str .= $contact->getWZipcode() . ',';
		if (isset($checked['w_country']) && $checked['w_country'] == 'checked') $str .= $contact->getWCountryName() . ',';
		if (isset($checked['w_phone_number']) && $checked['w_phone_number'] == 'checked') $str .= $contact->getWPhoneNumber() . ',';
		if (isset($checked['w_phone_number2']) && $checked['w_phone_number2'] == 'checked') $str .= $contact->getWPhoneNumber2() . ',';
		if (isset($checked['w_fax_number']) && $checked['w_fax_number'] == 'checked') $str .= $contact->getWFaxNumber() . ',';
		if (isset($checked['w_assistant_number']) && $checked['w_assistant_number'] == 'checked') $str .= $contact->getWAssistantNumber() . ',';
		if (isset($checked['w_callback_number']) && $checked['w_callback_number'] == 'checked') $str .= $contact->getWCallbackNumber() . ',';
		
		if (isset($checked['h_web_page']) && $checked['h_web_page'] == 'checked') $str .= $contact->getHWebPage() . ',';
		if (isset($checked['h_address']) && $checked['h_address'] == 'checked') $str .= $contact->getHAddress() . ',';
		if (isset($checked['h_city']) && $checked['h_city'] == 'checked') $str .= $contact->getHCity() . ',';
		if (isset($checked['h_state']) && $checked['h_state'] == 'checked') $str .= $contact->getHState() . ',';
		if (isset($checked['h_zipcode']) && $checked['h_zipcode'] == 'checked') $str .= $contact->getHZipcode() . ',';
		if (isset($checked['h_country']) && $checked['h_country'] == 'checked') $str .= $contact->getHCountryName() . ',';
		if (isset($checked['h_phone_number']) && $checked['h_phone_number'] == 'checked') $str .= $contact->getHPhoneNumber() . ',';
		if (isset($checked['h_phone_number2']) && $checked['h_phone_number2'] == 'checked') $str .= $contact->getHPhoneNumber2() . ',';
		if (isset($checked['h_fax_number']) && $checked['h_fax_number'] == 'checked') $str .= $contact->getHFaxNumber() . ',';
		if (isset($checked['h_mobile_number']) && $checked['h_mobile_number'] == 'checked') $str .= $contact->getHMobileNumber() . ',';
		if (isset($checked['h_pager_number']) && $checked['h_pager_number'] == 'checked') $str .= $contact->getHPagerNumber() . ',';
		
		if (isset($checked['o_web_page']) && $checked['o_web_page'] == 'checked') $str .= $contact->getOWebPage() . ',';
		if (isset($checked['o_address']) && $checked['o_address'] == 'checked') $str .= $contact->getOAddress() . ',';
		if (isset($checked['o_city']) && $checked['o_city'] == 'checked') $str .= $contact->getOCity() . ',';
		if (isset($checked['o_state']) && $checked['o_state'] == 'checked') $str .= $contact->getOState() . ',';
		if (isset($checked['o_zipcode']) && $checked['o_zipcode'] == 'checked') $str .= $contact->getOZipcode() . ',';
		if (isset($checked['o_country']) && $checked['o_country'] == 'checked') $str .= $contact->getOCountryName() . ',';
		if (isset($checked['o_phone_number']) && $checked['o_phone_number'] == 'checked') $str .= $contact->getOPhoneNumber() . ',';
		if (isset($checked['o_phone_number2']) && $checked['o_phone_number2'] == 'checked') $str .= $contact->getOPhoneNumber2() . ',';
		if (isset($checked['o_fax_number']) && $checked['o_fax_number'] == 'checked') $str .= $contact->getOFaxNumber() . ',';
		if (isset($checked['o_birthday']) && $checked['o_birthday'] == 'checked') $str .= $contact->getOBirthday() . ',';
		if (isset($checked['email2']) && $checked['email2'] == 'checked') $str .= $contact->getEmail2() . ',';
		if (isset($checked['email3']) && $checked['email3'] == 'checked') $str .= $contact->getEmail3() . ',';
		if (isset($checked['job_title']) && $checked['job_title'] == 'checked') $str .= $contact->getJobTitle() . ',';
		if (isset($checked['department']) && $checked['department'] == 'checked') $str .= $contact->getDepartment() . ',';
		if (isset($checked['middlename']) && $checked['middlename'] == 'checked') $str .= $contact->getMiddlename() . ',';
		if (isset($checked['notes']) && $checked['notes'] == 'checked') $str .= $contact->getNotes();
		
		return $str;
	}
	
	function build_csv_from_company(Company $company, $checked) {
		$str = '';
		
		if (isset($checked['name']) && $checked['name'] == 'checked') $str .= $company->getName() . ',';
		if (isset($checked['address']) && $checked['address'] == 'checked') $str .= $company->getAddress() . ',';
		if (isset($checked['address2']) && $checked['address2'] == 'checked') $str .= $company->getAddress2() . ',';
		if (isset($checked['city']) && $checked['city'] == 'checked') $str .= $company->getCity() . ',';
		if (isset($checked['state']) && $checked['state'] == 'checked') $str .= $company->getState() . ',';
		if (isset($checked['zipcode']) && $checked['zipcode'] == 'checked') $str .= $company->getZipcode() . ',';
		if (isset($checked['country']) && $checked['country'] == 'checked') $str .= $company->getCountryName() . ',';
		if (isset($checked['phone_number']) && $checked['phone_number'] == 'checked') $str .= $company->getPhoneNumber() . ',';
		if (isset($checked['fax_number']) && $checked['fax_number'] == 'checked') $str .= $company->getFaxNumber() . ',';
		if (isset($checked['email']) && $checked['email'] == 'checked') $str .= $company->getEmail() . ',';
		if (isset($checked['homepage']) && $checked['homepage'] == 'checked') $str .= $company->getHomepage();
		
		return $str;
	}
	
	function search(){
		ajx_current('empty');
		if (!can_manage_contacts(logged_user())) {
			flash_error(lang("no access permissions"));
			return;
		}
		
		$search_for = array_var($_POST,'search_for',false);
		if ($search_for){
			$projects = logged_user()->getActiveProjectIdsCSV();
			if ($projects != '')
				$projects .= ',';
			$projects .= '0';
			
			$search_results = SearchableObjects::searchByType($search_for, $projects, 'Contacts', true, 50);
			$contacts = $search_results[0];
			if ($contacts && count($contacts) > 0){
				$result = array();
				foreach ($contacts as $contactResult){
					$contact = $contactResult['object'];
					$result[] = array(
						'contact_name' => $contact->getFirstname() . ' ' . $contact->getLastname(),
						'contact_title' => $contact->getJobTitle(),
						'company_name' => $contact->getCompany() instanceof Company? $contact->getCompany()->getName() : '',
						'contact_department' => $contact->getDepartment(),
						'contact_id' => $contact->getId()
					);
				}
				ajx_extra_data(array("results" => $result));
			}
		}
	}
} // ContactController

?>