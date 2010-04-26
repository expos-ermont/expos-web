<?php

/**
 * Message controller
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class MessageController extends ApplicationController {

	/**
	 * Construct the MessageController
	 *
	 * @access public
	 * @param void
	 * @return MessageController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct
	
	// ---------------------------------------------------
	//  Index
	// ---------------------------------------------------
	
	function list_all() {
		ajx_current("empty");
		
		// Get all variables from request
		$start = array_var($_GET,'start');
		$limit = config_option('files_per_page');
		$order = array_var($_GET,'sort');
		$order_dir = array_var($_GET,'dir');
		if (! $start) {
			$start = 0;
		}
		$tag = array_var($_GET,'tag');
		$action = array_var($_GET,'action');
		$attributes = array(
			"ids" => explode(',', array_var($_GET,'ids')),
			"types" => explode(',', array_var($_GET,'types')),
			"tag" => array_var($_GET,'tagTag'),
			"accountId" => array_var($_GET,'account_id'),
			"viewType" => array_var($_GET,'view_type'),
			"readType" => array_var($_GET,'read_type'),
			"stateType" => array_var($_GET,'state_type')
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
		
		// Get all emails and messages to display
		$pid = array_var($_GET, 'active_project', 0);
		$project = Projects::findById($pid);
//		$emails = $this->getEmails($action, $tag, $attributes, $project);
		$messages = $this->getMessages($action, $tag, $attributes, $project, $order, $order_dir);
//		$totMsg = $this->addMessagesAndEmails($messages, $emails);
		
		// Prepare response object
		$object = $this->prepareObject($messages, $start, $limit);
		ajx_extra_data($object);
    	tpl_assign("listing", $object);
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
						case "message":
							$message = ProjectMessages::findById($id);
							if (isset($message) && $message->canDelete(logged_user())){
								try{
									DB::beginWork();
									$message->trash();
									ApplicationLogs::createLog($message, $ws, ApplicationLogs::ACTION_TRASH);
									DB::commit();
									$succ++;
								} catch(Exception $e){
									DB::rollback();
									$err++;
								}
							} else {
								$err++;
							}
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
						case "message":
							$message = ProjectMessages::findById($id);
							if (isset($message) && $message->canEdit(logged_user())){
								Tags::addObjectTag($tag, $message);
								ApplicationLogs::createLog($message, $message->getWorkspaces(), ApplicationLogs::ACTION_TAG,false,null,true,$tag);
								$resultMessage = lang("success tag objects", '');
							};
							break;

						default:
							$resultMessage = lang("Unimplemented type: '" . $type . "'");// if
							$resultCode = 2;
							break;
					}; // switch
				}; // for
				break;
				
			case "move":
				$wsid = $attributes["moveTo"];
				$count = 0;
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$type = $attributes["types"][$i];
					switch ($type){
						case "message":
							$message = ProjectMessages::findById($id);
							if (isset($message) && $message->canEdit(logged_user())){
								// TODO
								//ApplicationLogs::createLog($message, $message->getWorkspaces(), ApplicationLogs::ACTION_EDIT,false,null,true,$tag);
								$count++;
							};
							break;

						default:
							$resultMessage = lang("Unimplemented type: '" . $type . "'");// if
							$resultCode = 2;
							break;
					}; // switch
				}; // for
				$resultMessage = lang("success move objects", $count);
				break;
				
			default:
				$resultMessage = lang("Unimplemented action: '" . $action . "'");// if 
				$resultCode = 2;	
				break;		
		} // switch
		return array("errorMessage" => $resultMessage, "errorCode" => $resultCode);
	}

	/**
	 * Adds the messages and emails arrays
	 *
	 * @param array $messages
	 * @param array $emails
	 * @return array
	 */
	private function addMessagesAndEmails($messages, $emails){
		$totCount = 0;
		if (isset($emails)) $totCount = count($emails);
		if (isset($messages)) $totCount += count($messages);
		
		//Order messages and emails by date
		if (!isset($messages))
			$totMsg = $emails;
		else {
			$e = 0;
			$m = 0;
			while (($e + $m) < $totCount){
				if ($e < count($emails))
					if ($m < count($messages))
						if ($emails[$e]['comp_date'] > $messages[$m]['comp_date']){
							$totMsg[] = $emails[$e];
							$e++;
						} else {
							$totMsg[] = $messages[$m];
							$m++;
						}
					else {
						$totMsg[] = $emails[$e];
						$e++;
					}
				else {
					$totMsg[] = $messages[$m];
					$m++;
				}
			}
		}
		
		return $totMsg;
	}
	
	
	/**
	 * Returns a list of messages according to the requested parameters
	 *
	 * @param string $action
	 * @param string $tag
	 * @param array $attributes
	 * @param Project $project
	 * @return array
	 */
	private function getMessages($action, $tag, $attributes, $project = null, $order = null, $order_dir = null) {
		switch ($order){
			case 'updatedOn':
				$order_crit = 'updated_on';
				break;
			case 'createdOn':
				$order_crit = 'created_on';
				break;
			case 'title':
				$order_crit = 'title';
				break;
			default:
				$order_crit = 'updated_on';
				break;
		}
		if (!$order_dir){
			switch ($order){
				case 'name': $order_dir = 'ASC'; break;
				default: $order_dir = 'DESC';
			}
		}
		if (isset($attributes["viewType"]) && 
			($attributes["viewType"] != "all" && $attributes["viewType"] != "messages"))
			return null;

		if ($project instanceof Project){
			$pids = $project->getAllSubWorkspacesCSV(true, logged_user());
		} else {
			$pids = logged_user()->getActiveProjectIdsCSV();
		}
		$messageConditions = "`id` IN (SELECT `object_id` FROM `".TABLE_PREFIX."workspace_objects` WHERE `object_manager` = 'ProjectMessages' && `workspace_id` IN ($pids))";

		if (!isset($tag) || $tag == '' || $tag == null) {
			$tagstr = " '1' = '1'"; // dummy condition
		} else {
			$tagstr = "(select count(*) from " . TABLE_PREFIX . "tags where " .
				TABLE_PREFIX . "project_messages.id = " . TABLE_PREFIX . "tags.rel_object_id and " .
				TABLE_PREFIX . "tags.tag = '".$tag."' and " . TABLE_PREFIX . "tags.rel_object_manager ='ProjectMessages' ) > 0 ";
		}
		
		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectMessages::instance(),ACCESS_LEVEL_READ, logged_user(), 'project_id') .')';

		$res = DB::execute("SELECT id, 'ProjectMessages' as manager, $order_crit from " . TABLE_PREFIX. "project_messages where " . 
			"`trashed_by_id` = 0 AND ".$messageConditions . " AND " . $tagstr . $permissions 
			. " ORDER BY $order_crit $order_dir");
			
		if(!$res) return null;
		return $res->fetchAll();
	}
	
	/**
	 * Prepares return object for a list of emails and messages
	 *
	 * @param array $totMsg
	 * @param integer $start
	 * @param integer $limit
	 * @return array
	 */
	private function prepareObject($totMsg, $start, $limit, $attributes = null) {
		$object = array(
			"totalCount" => count($totMsg),
			"start" => (integer)min(array(count($totMsg) - (count($totMsg) % $limit),$start)),
			"messages" => array()
		);
		for ($i = $start; $i < $start + $limit; $i++){
			if (isset($totMsg[$i])){
				$manager= $totMsg[$i]['manager'];
    			$id = $totMsg[$i]['id'];
    			if($id && $manager){
    				$msg=get_object_by_manager_and_id($id, $manager);  
					
					if ($msg instanceof ProjectMessage){
						$text = $msg->getText();
						if (strlen($text) > 300)
							$text = substr_utf($text,0,300) . "...";
						$object["messages"][] = array(
						    "id" => $i,
							"object_id" => $msg->getId(),
							"type" => 'message',
							"hasAttachment" => false,
							"accountId" => 0,
							"accountName" => '',
							"title" => $msg->getTitle(),
							"text" => $text,
							"date" => $msg->getUpdatedOn() instanceof DateTimeValue ? $msg->getUpdatedOn()->getTimestamp() : 0,
							"wsIds" => $msg->getWorkspacesIdsCSV(logged_user()->getActiveProjectIdsCSV()),
							"userId" => $msg->getCreatedById(),
							"userName" => $msg->getCreatedByDisplayName(),
							"tags" => project_object_tags($msg),
							"from" => $msg->getCreatedByDisplayName(),							
							"isDraft" => 0,
							"isSent"=> 0
						);
					}
    			}
			}
		}
		return $object;
	}
	

	// ---------------------------------------------------
	//  Messages
	// ---------------------------------------------------
	
	/**
	 * View single message
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function view() {
		$this->addHelper('textile');

		$message = ProjectMessages::findById(get_id());
		if(!($message instanceof ProjectMessage)) {
			flash_error(lang('message dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$message->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		//$this->setHelp("view_message");
		
		tpl_assign('message', $message);
		tpl_assign('subscribers', $message->getSubscribers());
		ajx_extra_data(array("title" => $message->getTitle(), 'icon'=>'ico-message'));
		ajx_set_no_toolbar(true);

	} // view
	
	/**
	 * View a message in a printer-friendly format.
	 *
	 */
	function print_view() {
		$this->setLayout("html");
		$this->addHelper('textile');
		
		$message = ProjectMessages::findById(get_id());
		if(!($message instanceof ProjectMessage)) {
			flash_error(lang('message dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$message->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		
		tpl_assign('message', $message);
	} // print_view

	/**
	 * Add message
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function add() {
		$this->setTemplate('add_message');
		$message = new ProjectMessage();
		tpl_assign('message', $message);

		$message_data = array_var($_POST, 'message');
		if(!is_array($message_data)) {
			$message_data = array(); // array
		} // if
		tpl_assign('message_data', $message_data);

		if(is_array(array_var($_POST, 'message'))) {
			$ids = array_var($_POST, "ws_ids", "");
			$enteredWS = Projects::findByCSVIds($ids);
			$validWS = array();
			foreach ($enteredWS as $ws) {
				if (ProjectMessage::canAdd(logged_user(), $ws)) {
					$validWS[] = $ws;
				}
			}
			if (empty($validWS)) {
				flash_error(lang('must choose at least one workspace error'));
				ajx_current("empty");
				return;
			}
			
			try {
				$message->setFromAttributes($message_data);
				$message->setIsPrivate(false);
				// Options are reserved only for members of owner company
				if(!logged_user()->isMemberOfOwnerCompany()) {
					$message->setIsImportant(false);
					$message->setCommentsEnabled(true);
					$message->setAnonymousCommentsEnabled(false);
				} // if

				DB::beginWork();
				$message->save();
				$message->setTagsFromCSV(array_var($message_data, 'tags'));
				$message->removeFromWorkspaces(logged_user()->getActiveProjectIdsCSV());
				foreach ($validWS as $w) {
					$message->addToWorkspace($w);
				}
				
				$object_controller = new ObjectController();
			    $object_controller->link_to_new_object($message);
				$object_controller->add_subscribers($message);
				$object_controller->add_custom_properties($message);
				
				ApplicationLogs::createLog($message, $validWS, ApplicationLogs::ACTION_ADD);
			    
				DB::commit();

				flash_success(lang('success add message', $message->getTitle()));
				if (array_var($_POST, 'popup', false)) {
					ajx_current("reload");
	          	} else {
	          		ajx_current("back");
	          	}
	          	ajx_add("overview-panel", "reload");          	
					
				// Error...
			} catch(Exception $e) {
				DB::rollback();

				$message->setNew(true);
				flash_error($e->getMessage());
				ajx_current("empty");
				
			} // try

		} // if
	} // add

	/**
	 * Edit specific message
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function edit() {
		$this->setTemplate('add_message');

		$message = ProjectMessages::findById(get_id());
		if(!($message instanceof ProjectMessage)) {
			flash_error(lang('message dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$message->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$message_data = array_var($_POST, 'message');
		if(!is_array($message_data)) {
			$tag_names = $message->getTagNames();
			$message_data = array(
				'milestone_id' => $message->getMilestoneId(),
				'title' => $message->getTitle(),
				'text' => $message->getText(),
				'additional_text' => $message->getAdditionalText(),
				'tags' => is_array($tag_names) ? implode(', ', $tag_names) : '',
				'is_private' => $message->isPrivate(),
				'is_important' => $message->getIsImportant(),
				'comments_enabled' => $message->getCommentsEnabled(),
				'anonymous_comments_enabled' => $message->getAnonymousCommentsEnabled(),
			); // array
		} // if

		tpl_assign('message', $message);
		tpl_assign('message_data', $message_data);

		if(is_array(array_var($_POST, 'message'))) {
			try {
				$old_is_private = $message->isPrivate();
				$old_is_important = $message->getIsImportant();
				$old_comments_enabled = $message->getCommentsEnabled();
				$old_anonymous_comments_enabled = $message->getAnonymousCommentsEnabled();

				$message->setFromAttributes($message_data);

				// Options are reserved only for members of owner company
				if(!logged_user()->isMemberOfOwnerCompany()) {
					$message->setIsPrivate($old_is_private);
					$message->setIsImportant($old_is_important);
					$message->setCommentsEnabled($old_comments_enabled);
					$message->setAnonymousCommentsEnabled($old_anonymous_comments_enabled);
				} // if

				DB::beginWork();
				$message->save();
				$message->setTagsFromCSV(array_var($message_data, 'tags'));
				
				/* <multiples workspaces> */
				$message->removeFromWorkspaces(logged_user()->getActiveProjectIdsCSV());
				$ids = array_var($_POST, "ws_ids", "");
				$enteredWS = Projects::findByCSVIds($ids);
				$validWS = array();
				foreach ($enteredWS as $ws) {
					if (ProjectMessage::canAdd(logged_user(), $ws)) {
						$validWS[] = $ws;
					}
				}
				if (empty($validWS)) {
					throw new Exception(lang('must choose at least one workspace error'));
				}
				foreach ($validWS as $w) {
					$message->addToWorkspace($w);
				}
				/* </multiples workspaces> */
				
				$object_controller = new ObjectController();
			    $object_controller->link_to_new_object($message);
				$object_controller->add_subscribers($message);
				$object_controller->add_custom_properties($message);
				
				ApplicationLogs::createLog($message, $validWS, ApplicationLogs::ACTION_EDIT);
			    
				DB::commit();
				
				flash_success(lang('success edit message', $message->getTitle()));
				if (array_var($_POST, 'popup', false)) {
					ajx_current("reload");
	          	} else {
	          		ajx_current("back");
	          	}
	          	ajx_add("overview-panel", "reload");          	

			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // edit

	/**
	 * Delete specific message
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function delete() {
		ajx_current("empty");
		$message = ProjectMessages::findById(get_id());
		if(!($message instanceof ProjectMessage)) {
			flash_error(lang('message dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$message->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		try {

			DB::beginWork();
			$message->trash();
			$ws = $message->getWorkspaces();
			ApplicationLogs::createLog($message, $ws, ApplicationLogs::ACTION_TRASH);
			DB::commit();

			flash_success(lang('success deleted message', $message->getTitle()));
			if (array_var($_POST, 'popup', false)) {
				ajx_current("reload");
          	} else {
          		ajx_current("back");
          	}
          	ajx_add("overview-panel", "reload");          	
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete message'));
			ajx_current("empty");
		} // try
	} // delete



} // MessageController

?>