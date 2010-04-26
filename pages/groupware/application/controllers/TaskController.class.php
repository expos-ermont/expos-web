<?php

/**
 * Controller for handling task list and task related requests
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class TaskController extends ApplicationController {

	/**
	 * Construct the MilestoneController
	 *
	 * @access public
	 * @param void
	 * @return MilestoneController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct

	private function task_item(ProjectTask $task) {
		$isCurrentProject = active_project() instanceof Project && $task->getProject()->getId() == active_project()->getId();
		
		return array(
			"id" => $task->getId(),
			"title" => $task->getObjectName(),
			"parent" => $task->getParentId(),
			"milestone" => $task->getMilestoneId(),
			"assignedTo" => $task->getAssignedTo()? $task->getAssignedToName():'',
			"workspaces" => ($isCurrentProject? '' : $task->getWorkspacesNamesCSV(logged_user()->getActiveProjectIdsCSV())),
			"workspaceids" => ($isCurrentProject? '' : $task->getWorkspacesIdsCSV(logged_user()->getActiveProjectIdsCSV())),
			"workspacecolors" => ($isCurrentProject? '' : $task->getWorkspaceColorsCSV(logged_user()->getActiveProjectIdsCSV())),
			"completed" => $task->isCompleted(),
			"completedBy" => $task->getCompletedByName(),
			"isLate" => $task->isLate(),
			"daysLate" => $task->getLateInDays(),
			"priority" => $task->getPriority(),
			"duedate" => ($task->getDueDate() ? $task->getDueDate()->getTimestamp() : '0'),
			"order" => $task->getOrder()
		);
	}
	
	function assign(){
		ajx_current("empty");
		$task_id = array_var($_POST, 'taskId');
		$task = ProjectTasks::findById($task_id);
		if(!($task instanceof ProjectTask)) {
			flash_error(lang('task list dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$task->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		
		$assigned_to = explode(':', array_var($_POST, 'assigned_to', ''));
		try{
			DB::beginWork();
			
			$company_id = array_var($assigned_to, 0, 0);
			$user_id = array_var($assigned_to, 1, 0);
			if ($company_id < 0) $company_id = 0;
			if ($user_id < 0) $user_id = 0;
			
			$can_assign = can_assign_task_to_company_user(logged_user(), $task, $company_id, $user_id);
			if ($can_assign !== true) {
				flash_error($can_assign);
				return;
			} 
			
			$task->setAssignedToCompanyId($company_id);
			$task->setAssignedToUserId($user_id);
			
			$task->save();
			
			DB::commit();
			ajx_extra_data(array("id" => $task_id));
		} catch(Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		} // try	
	}
		
	function quick_add_task() {
		ajx_current("empty");
		$task = new ProjectTask();
		$task_data = array_var($_POST, 'task');
		$parent_id = array_var($task_data, 'parent_id', 0);
		$parent = ProjectTasks::findById($parent_id);
		$project = Projects::findById(array_var($task_data, 'project_id', 0));
		if (!$project instanceof Project) {
			if ($parent instanceof ProjectTask){
				$project = $parent->getProject();
			} else {
				$milestone_id = array_var($task_data,'milestone_id',null);
				if($milestone_id){
					$milestone = ProjectMilestones::findById($milestone_id);
					if($milestone)
						$project =$milestone->getProject();
				}
				if(!$project instanceof Project)
					$project = active_or_personal_project();
			}
		}
		
		if(!ProjectTask::canAdd(logged_user(), $project)) {
			flash_error(lang('no access permissions'));
			return;
		} // if
		
		if (is_array($task_data)) {
			$task_data['due_date'] = getDateValue(array_var($task_data, 'task_due_date'));
			$task_data['start_date'] = getDateValue(array_var($task_data, 'task_start_date'));
			
			$task->setFromAttributes($task_data);
			$task->setProjectId($project->getId());
			//$task->setOrder(ProjectTasks::maxOrder(array_var($task_data, "parent_id", 0), array_var($task_data, "milestone_id", 0)));
			// Set assigned to
			$assigned_to = explode(':', array_var($task_data, 'assigned_to', ''));
			$company_id = array_var($assigned_to, 0, 0);
			$user_id = array_var($assigned_to, 1, 0);
			$can_assign = can_assign_task_to_company_user(logged_user(), $task, $company_id, $user_id);
			if ($can_assign !== true) {
				flash_error($can_assign);
				return;
			}
			$task->setAssignedToCompanyId($company_id);
			$task->setAssignedToUserId($user_id);
			$task->setIsPrivate(false); // Not used, but defined as not null.
			
			if (array_var($task_data,'is_completed',false) == 'true'){
				$task->setCompletedOn(DateTimeValueLib::now());
				$task->setCompletedById(logged_user()->getId());
			}
			
			try {
				DB::beginWork();
				$task->save();
				$task->setTagsFromCSV(array_var($task_data, 'tags'));
				
				//Add new work timeslot for this task
				if (array_var($task_data,'hours') != '' && array_var($task_data,'hours') > 0){
					$hours = array_var($task_data, 'hours');
					$hours = - $hours;
					
					$timeslot = new Timeslot();
					$dt = DateTimeValueLib::now();
					$dt2 = DateTimeValueLib::now();
					$timeslot->setEndTime($dt);
					$dt2 = $dt2->add('h', $hours);
					$timeslot->setStartTime($dt2);
					$timeslot->setUserId(logged_user()->getId());
					$timeslot->setObjectManager("ProjectTasks");
					$timeslot->setObjectId($task->getId());
					$timeslot->save();
				}
				
				// subscribe
				$task->subscribeUser(logged_user());
				
				ApplicationLogs::createLog($task, $task->getWorkspaces(), ApplicationLogs::ACTION_ADD);
				$assignee = $task->getAssignedToUser();
				if ($assignee instanceof User) {
					$task->subscribeUser($assignee);
				}
				
				DB::commit();

				// notify asignee
				if(array_var($task_data, 'notify') == 'true') {
					try {
						Notifier::taskAssigned($task);
					} catch(Exception $e) {
					} // try
				}
				ajx_extra_data(array("task" => $task->getArrayInfo()));
				flash_success(lang('success add task', $task->getTitle()));
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
			} // try
		} // if
	}
	
	function quick_edit_task() {
		ajx_current("empty");
		
		$task = ProjectTasks::findById(get_id());
		if(!($task instanceof ProjectTask)) {
			flash_error(lang('task list dnx'));
			return;
		} // if
		
		if(!$task->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if
		
		$task_data = array_var($_POST, 'task');
		
		if (is_array($task_data)) {
			$task_data['due_date'] = getDateValue(array_var($task_data, 'task_due_date'));
			$task_data['start_date'] = getDateValue(array_var($task_data, 'task_start_date'));
			
			$task->setFromAttributes($task_data);
			$project = Projects::findById(array_var($task_data, 'project_id', 0));
			if ($project instanceof Project && $task->canAdd(logged_user(),$project)) {
				$task->setProjectId($project->getId());
			}
			//$task->setOrder(ProjectTasks::maxOrder(array_var($task_data, "parent_id", 0), array_var($task_data, "milestone_id", 0)));
			// Set assigned to
			$assigned_to = explode(':', array_var($task_data, 'assigned_to', ''));
			
			$company_id = array_var($assigned_to, 0, 0);
			$user_id = array_var($assigned_to, 1, 0);
			$can_assign = can_assign_task_to_company_user(logged_user(), $task, $company_id, $user_id);
			if ($can_assign !== true) {
				flash_error($can_assign);
				return;
			}
			$task->setAssignedToCompanyId($company_id);
			$task->setAssignedToUserId($user_id);			
			//$task->setIsPrivate(false); // Not used, but defined as not null.
			
			/*if (array_var($task_data,'is_completed',false) == 'true'){
				$task->setCompletedOn(DateTimeValueLib::now());
				$task->setCompletedById(logged_user()->getId());
			}*/
			
			try {
				DB::beginWork();
				$task->save();
				$task->setTagsFromCSV(array_var($task_data, 'tags'));
				
				//Add new work timeslot for this task
				if (array_var($task_data,'hours') != '' && array_var($task_data,'hours') > 0){
					$hours = array_var($task_data, 'hours');
					
					if (strpos($hours,',') && !strpos($hours,'.'))
						$hours = str_replace(',','.',$hours);
					
					$timeslot = new Timeslot();
					$dt = DateTimeValueLib::now();
					$dt2 = DateTimeValueLib::now();
					$timeslot->setEndTime($dt);
					$dt2 = $dt2->add('h', -$hours);
					$timeslot->setStartTime($dt2);
					$timeslot->setUserId(logged_user()->getId());
					$timeslot->setObjectManager("ProjectTasks");
					$timeslot->setObjectId($task->getId());
					$timeslot->save();
				}
				ApplicationLogs::createLog($task, $task->getWorkspaces(), ApplicationLogs::ACTION_EDIT);
				$assignee = $task->getAssignedToUser();
				if ($assignee instanceof User) {
					$task->subscribeUser($assignee);
				}
				DB::commit();

				// notify asignee
				if(array_var($task_data, 'notify') == 'true') {
					try {
						Notifier::taskAssigned($task);
					} catch(Exception $e) {
					} // try
				}
				$task->getTagNames(true); //Forces reload of task tags to update changes
				ajx_extra_data(array("task" => $task->getArrayInfo()));
				flash_success(lang('success edit task', $task->getTitle()));
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
			} // try
		} // if
	}
	
	function multi_task_action(){
		ajx_current("empty");
		$ids = explode(',', array_var($_POST, 'ids'));
		$action = array_var($_POST, 'action');
		$options = array_var($_POST, 'options');
		
		if (!is_array($ids) || trim(array_var($_POST, 'ids')) == '' || count($ids) <= 0){
			flash_error(lang('no items selected'));
			return;
		}
		
		$tasks = ProjectTasks::findAll(array('conditions' => 'id in (' . implode(',',$ids) . ')'));
		$tasksToReturn = array();
		$showSuccessMessage = true;
		try{
			DB::beginWork();
			foreach($tasks as $task){
				switch ($action){
					case 'tag':
						if ($task->canEdit(logged_user())){
							$tag = $options;
							Tags::addObjectTag($tag, $task);
							ApplicationLogs::createLog($task, $task->getWorkspaces(), ApplicationLogs::ACTION_TAG,false,null,true,$tag);
							$tasksToReturn[] = $task->getArrayInfo();
						}
						break;
					case 'complete':
						if ($task->canEdit(logged_user())){
							$task->completeTask();
							$tasksToReturn[] = $task->getArrayInfo();
						}
						break;
					case 'delete':
						if ($task->canDelete(logged_user())){
							$tasksToReturn[] = array('id' => $task->getId());
							$task->trash();
							ApplicationLogs::createLog($task, $task->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
						}
						break;
					case 'start_work':
						if ($task->canEdit(logged_user())){
							$task->addTimeslot(logged_user());
							ApplicationLogs::createLog($task, $task->getWorkspaces(), ApplicationLogs::ACTION_EDIT);
							
							$tasksToReturn[] = $task->getArrayInfo();
							$showSuccessMessage = false;
						}
						break;
					case 'close_work':
						if ($task->canEdit(logged_user())){
							$task->closeTimeslots(logged_user(),array_var($_POST, 'options'));
							ApplicationLogs::createLog($task, $task->getWorkspaces(), ApplicationLogs::ACTION_EDIT);
							
							$tasksToReturn[] = $task->getArrayInfo();
							$showSuccessMessage = false;
						}
						break;
					case 'pause_work':
						if ($task->canEdit(logged_user())){
							$task->pauseTimeslots(logged_user());
							$tasksToReturn[] = $task->getArrayInfo();
							$showSuccessMessage = false;
						}
						break;
					case 'resume_work':
						if ($task->canEdit(logged_user())){
							$task->resumeTimeslots(logged_user());
							$tasksToReturn[] = $task->getArrayInfo();
							$showSuccessMessage = false;
						}
						break;
					default:
						DB::rollback();
						flash_error(lang('invalid action'));
						return;
				} // end switch
			} // end foreach
			DB::commit();
			if (count($tasksToReturn) < count($tasks))
				flash_error(lang('tasks updated') . '. ' . lang('some tasks could not be updated due to permission restrictions'));
			else
				if ($showSuccessMessage)
					flash_success(lang('tasks updated'));
			
			ajx_extra_data(array('tasks' => $tasksToReturn));
		} catch(Exception $e){
			DB::rollback();
			flash_error($e->getMessage());
		}
		
	}
	
	function new_list_tasks(){
		// get query parameters, save user preferences if necessary
		$status = array_var($_GET,'status',null);
		if (is_null($status) || $status == '') {
			$status = user_config_option('task panel status',2);
		} else
			if (user_config_option('task panel status') != $status)
				set_user_config_option('task panel status', $status, logged_user()->getId());
				
		$filter = array_var($_GET,'filter');
		if (is_null($filter) || $filter == '') {
			$filter = user_config_option('task panel filter','assigned_to');
		} else
			if (user_config_option('task panel filter') != $filter)
				set_user_config_option('task panel filter', $filter, logged_user()->getId());
		
		if ($filter != 'no_filter'){
			$filter_value = array_var($_GET,'fval');
			if (is_null($filter_value) || $filter_value == '') {
				$filter_value = user_config_option('task panel filter value',logged_user()->getCompanyId() . ':' . logged_user()->getId());
			} else
				if (user_config_option('task panel filter value') != $filter_value)
					set_user_config_option('task panel filter value', $filter_value, logged_user()->getId());
		}
		$isJson = array_var($_GET,'isJson',false);
		if ($isJson)
			ajx_current("empty");
		
		$project = active_project();
		$tag = active_tag();
		
		$template_condition = "`is_template` = 0 ";
		
		//Get the task query conditions
		$task_filter_condition = "";
		switch($filter){
			case 'assigned_to':
				$assigned_to = explode(':', $filter_value);
				$assigned_to_user = array_var($assigned_to,1,0);
				$assigned_to_company = array_var($assigned_to,0,0);
				if ($assigned_to_user > 0)
					$task_filter_condition = " AND (`assigned_to_user_id` = " . $assigned_to_user 
						. " OR (`assigned_to_company_id` = " . $assigned_to_company . " AND `assigned_to_user_id` = 0)) ";
				else
					if ($assigned_to_company > 0)
						$task_filter_condition = " AND  `assigned_to_company_id` = " . $assigned_to_company . " AND `assigned_to_user_id` = 0";
					else{
						if ($assigned_to_company == -1 && $assigned_to_user == -1)
							$task_filter_condition = "  AND `assigned_to_company_id` = 0 AND `assigned_to_user_id` = 0 ";
					}
				break;
			case 'assigned_by':
				if ($filter_value != 0)
					$task_filter_condition = " AND  `assigned_by_id` = " . $filter_value . " ";
				break;
			case 'created_by':
				if ($filter_value != 0)
					$task_filter_condition = " AND  `created_by_id` = " . $filter_value . " ";
				break;
			case 'completed_by':
				if ($filter_value != 0)
					$task_filter_condition = " AND  `completed_by_id` = " . $filter_value . " ";
				break;
			case 'milestone':
				$task_filter_condition = " AND  `milestone_id` = " . $filter_value . " ";
				break;
			case 'priority':
				$task_filter_condition = " AND  `priority` = " . $filter_value . " ";
				break;
			case 'no_filter':
				$task_filter_condition = "";
				break;
			default:
				flash_error(lang('task filter criteria not recognised', $filter));
		}
		
		if ($project instanceof Project) {
			$pids = $project->getAllSubWorkspacesCSV(true, logged_user());
		} else {
			$pids = logged_user()->getActiveProjectIdsCSV();
		}
		$projectstr = " AND `project_id` IN ($pids) ";
		
		$task_status_condition = "";
		switch($status){
			case 0: // Incomplete tasks
				$task_status_condition = " AND `completed_by_id` = 0 ";
				break;
			case 1: // Complete tasks
				$task_status_condition = " AND `completed_by_id` > 0 ";
				break;
			case 2: // All tasks
				break;
			default:
				throw new Exception('Task status "' . $status . '" not recognised');
		}
		
		if ($tag == '') {
			$tagstr = "";
		} else {
			$tagstr = " AND (select count(*) from " . TABLE_PREFIX . "tags where " .
			TABLE_PREFIX . "project_tasks.id = " . TABLE_PREFIX . "tags.rel_object_id and " .
			TABLE_PREFIX . "tags.tag = ".DB::escape($tag)." and " . TABLE_PREFIX . "tags.rel_object_manager ='ProjectTasks' ) > 0 ";
		}
		
		$conditions = $template_condition . $task_filter_condition . $task_status_condition . $tagstr . $projectstr;
		
		//Now get the tasks
		$tasks = ProjectTasks::findAll(array('conditions' => $conditions, 'order' => 'created_on DESC', 'limit' => '0 501'));
		ProjectTasks::populateTimeslots($tasks);
		ProjectTasks::populateTags($tasks);
		//Find all internal milestones for these tasks
		$internalMilestones = ProjectMilestones::getProjectMilestones($project, null, 'DESC', $tag, null, null, null, ($status == 0), false);
		
		//Find all external milestones for these tasks
		$milestone_ids = array();
		if($tasks){
			foreach ($tasks as $task){
				if ($task->getMilestoneId() != 0)
					$milestone_ids[$task->getMilestoneId()]	= $task->getMilestoneId();
			}
		}
		
		$milestone_ids_condition = '';
		if (count($milestone_ids) > 0){
			$milestone_ids_condition = ' OR id in (' . implode(',',$milestone_ids) . ')';
		}
		
		if ($status == 0) {
			$pendingstr = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " ";
		} else {
			 $pendingstr = "";
		}
		
		if ($tag == '') {
			$tagstr = "";
		} else {
			$tagstr = " AND (select count(*) from " . TABLE_PREFIX . "tags where " .
			TABLE_PREFIX . "project_milestones.id = " . TABLE_PREFIX . "tags.rel_object_id and " .
			TABLE_PREFIX . "tags.tag = ".DB::escape($tag)." and " . TABLE_PREFIX . "tags.rel_object_manager ='ProjectMilestones' ) > 0 ";
		}
		$milestone_conditions = " `is_template` = false " . $projectstr . $pendingstr . $tagstr . $milestone_ids_condition;
		$externalMilestonesTemp = ProjectMilestones::findAll(array('conditions' => $milestone_conditions));
		$externalMilestones = array();
		if($externalMilestonesTemp){
			foreach ($externalMilestonesTemp as $em){
				$found = false;
				if($internalMilestones){
					foreach ($internalMilestones as $im){
						if ($im->getId() == $em->getId()){
							$found = true;
							break;
						}
					}
				}
				if (!$found)
					$externalMilestones[] = $em;
			}
		}
		
		//Get Users Info
		if (logged_user()->isMemberOfOwnerCompany())
			$users = Users::getAll();
		else
			$users = logged_user()->getCompany()->getUsers();
		
		//Get Companies Info
		if (logged_user()->isMemberOfOwnerCompany())
			$companies = Companies::getCompaniesWithUsers();
		else
			$companies = array(logged_user()->getCompany());
		
		if (!$isJson){
			if(active_project() instanceof Project)
				$task_templates = WorkspaceTemplates::getTemplatesByWorkspace(active_project()->getId());
			else 
				$task_templates = array();
			tpl_assign('project_templates', $task_templates);
			tpl_assign('all_templates', COTemplates::findAll());
			tpl_assign('tasks', $tasks);
			if (count($tasks) > 500)
				tpl_assign('displayTooManyTasks', true);
			
			tpl_assign('internalMilestones', $internalMilestones);
			tpl_assign('externalMilestones', $externalMilestones);
			tpl_assign('users', $users);
			tpl_assign('companies', $companies);
			tpl_assign('userPreferences', array(
				'filterValue' => isset($filter_value)?$filter_value:'',
				'filter' => $filter,
				'status' => $status,
				'showWorkspaces' => user_config_option('tasksShowWorkspaces',1),
				'showTime' => user_config_option('tasksShowTime',0),
				'showDates' => user_config_option('tasksShowDates',0),
				'showTags' => user_config_option('tasksShowTags',0),
				'groupBy' => user_config_option('tasksGroupBy','milestone'),
				'orderBy' => user_config_option('tasksOrderBy','priority'),
				'defaultNotifyValue' => user_config_option('can notify from quick add')
			));
			ajx_set_no_toolbar(true);
		}
	}

	/**
	 * View task page
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function view_task() {
		$task_list = ProjectTasks::findById(get_id());
		$this->addHelper('textile');
		$this->setTemplate('view_list');
		
		if(!($task_list instanceof ProjectTask)) {
			flash_error(lang('task list dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$task_list->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		tpl_assign('task_list', $task_list);

		/*if(active_project()){
			$open=active_project()->getOpenTasks();
			$comp=active_project()->getCompletedTasks();
		}
		else{
			$projects=active_projects();
			foreach ($projects as $p){
				$open[] = $p->getOpenTasks();
				$comp[] = $p->getCompletedTasks();
			}
		}*/
		$this->addHelper('textile');
		ajx_extra_data(array("title" => $task_list->getTitle(), 'icon'=>'ico-task'));
		ajx_set_no_toolbar(true);
	} // view_task
	
	function print_task() {
		$this->setLayout("html");
		$task = ProjectTasks::findById(get_id());
		
		if(!($task instanceof ProjectTask)) {
			flash_error(lang('task list dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$task->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		tpl_assign('task', $task);
		$this->setTemplate('print_task');
	} // print_task

	/**
	 * Add new task
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function add_task() {
		$project = active_or_personal_project();
		if(!ProjectTask::canAdd(logged_user(), $project)) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		
		$task = new ProjectTask();
		$task_data = array_var($_POST, 'task');
		if(!is_array($task_data)) {
			$task_data = array(
				'milestone_id' => array_var($_POST, 'milestone_id',0),
				'project_id' => array_var($_POST, 'project_id',active_or_personal_project()->getId()),
				'title' => array_var($_POST, 'title', ''),
				'assigned_to' => array_var($_POST, 'assigned_to', '0:0'),
				'parent_id' => array_var($_POST, 'parent_id', 0),
				'priority' => array_var($_POST, 'priority', ProjectTasks::PRIORITY_NORMAL),
				'text' => array_var($_POST, 'text', ''),
				'start_date' => getDateValue(array_var($_POST, 'task_start_date', '')),
				'due_date' => getDateValue(array_var($_POST, 'task_due_date', '')),
				'is_template' => array_var($_POST, "is_template", array_var($_GET, "is_template", false)),
				'send_notification' => array_var($_POST, 'notify') && array_var($_POST, 'notify') == 'true'
			); // array
		} // if

		tpl_assign('task_data', $task_data);
		tpl_assign('task', $task);

		if (is_array(array_var($_POST, 'task'))) {
			$proj = Projects::findById(array_var($task_data, 'project_id'));
			if ($proj instanceof Project) {
				$project = $proj;
			}
			// order
			$task->setOrder(ProjectTasks::maxOrder(array_var($task_data, "parent_id", 0), array_var($task_data, "milestone_id", 0)));
			
			$task_data['due_date'] = getDateValue(array_var($_POST, 'task_due_date'));
			$task_data['start_date'] = getDateValue(array_var($_POST, 'task_start_date'));
			
			$task->setFromAttributes($task_data);
			
			$totalMinutes = (array_var($task_data, 'time_estimate_hours',0) * 60) +
				(array_var($task_data, 'time_estimate_minutes',0));
			$task->setTimeEstimate($totalMinutes);
			
			$task->setIsPrivate(false); // Not used, but defined as not null.
			$task->setProjectId($project->getId());
			// Set assigned to
			$assigned_to = explode(':', array_var($task_data, 'assigned_to', ''));
			$company_id = array_var($assigned_to, 0, 0);
			$user_id = array_var($assigned_to, 1, 0);
			$can_assign = can_assign_task_to_company_user(logged_user(), $task, $company_id, $user_id);
			if ($can_assign !== true) {
				flash_error($can_assign);
				ajx_current("empty");
				return;
			}
			$task->setAssignedToCompanyId($company_id);
			$task->setAssignedToUserId($user_id);
			
			$id = array_var($_GET, 'id', 0);
			$parent = ProjectTasks::findById($id);
			if ($parent instanceof ProjectTask) {
				$task->setParentId($id);
				if ($parent->getIsTemplate()) {
					$task->setIsTemplate(true);
				}
			}
			
			if ($task->getParentId() > 0 && $task->hasChild($task->getParentId())) {
				flash_error(lang('task child of child error'));
				ajx_current("empty");
				return;
			}
			
			//Add handins
			$handins = array();
			for($i = 0; $i < 4; $i++) {
				if(isset($task_data["handin$i"]) && is_array($task_data["handin$i"]) && (trim(array_var($task_data["handin$i"], 'title')) <> '')) {
					$assigned_to = explode(':', array_var($task_data["handin$i"], 'assigned_to', ''));
					$handins[] = array(
						'title' => array_var($task_data["handin$i"], 'title'),
						'responsible_company_id' => array_var($assigned_to, 0, 0),
						'responsible_user_id' => array_var($assigned_to, 1, 0)
					); // array
				} // if
			} // for

			try {
				DB::beginWork();
				$task->save();
				//echo 'pepe'; DB::rollback(); die();
				$task->setTagsFromCSV(array_var($task_data, 'tags'));
					
				foreach($handins as $handin_data) {
					$handin = new ObjectHandin();
					$handin->setFromAttributes($handin_data);
					$handin->setObjectId($task->getId());
					$handin->setObjectManager(get_class($task->manager()));
					$handin->save();
				} // foreach*/
				
				if (array_var($_GET, 'copyId', 0) > 0) {
					// copy remaining stuff from the task with id copyId
					$toCopy = ProjectTasks::findById(array_var($_GET, 'copyId'));
					if ($toCopy instanceof ProjectTask) {
						ProjectTasks::copySubTasks($toCopy, $task, array_var($task_data, 'is_template', false));
					}
				}
				
				//Link objects
			    $object_controller = new ObjectController();
			    $object_controller->link_to_new_object($task);
				$object_controller->add_subscribers($task);
				$object_controller->add_custom_properties($task);
				$object_controller->add_reminders($task);
				
				ApplicationLogs::createLog($task, $task->getWorkspaces(), ApplicationLogs::ACTION_ADD);
				
				DB::commit();
								
				// notify asignee
				if(array_var($task_data, 'send_notification') == 'checked') {
					try {
						Notifier::taskAssigned($task);
					} catch(Exception $e) {
						evt_add("debug", $e->getMessage());
					} // try
				}

				if ($task->getIsTemplate()) {
					flash_success(lang('success add template', $task->getTitle()));
				} else {
					flash_success(lang('success add task list', $task->getTitle()));
				}
				if (array_var($task_data, 'inputtype') != 'taskview')
					ajx_current("back");
				else
					ajx_current("reload");

			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // add_task
	
	/**
	 * Copy task
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function copy_task() {
		$project = active_or_personal_project();
		if(!ProjectTask::canAdd(logged_user(), $project)) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		
		$id = get_id();
		$task = ProjectTasks::findById($id);
		if (!$task instanceof ProjectTask) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$task_data = array(
			'milestone_id' => $task->getMilestoneId(),
			'title' => $task->getIsTemplate() ? $task->getTitle() : lang("copy of", $task->getTitle()),
			'assigned_to' => $task->getAssignedToCompanyId() . ":" . $task->getAssignedToUserId(),
			'parent_id' => $task->getParentId(),
			'priority' => $task->getPriority(),
			'tags' => implode(",", $task->getTagNames()),
			'project_id' => $task->getProjectId(),
			'time_estimate' => $task->getTimeEstimate(),
			'text' => $task->getText(),
			'copyId' => $task->getId(),
		); // array
		if ($task->getStartDate() instanceof DateTimeValue) {
			$task_data['start_date'] = $task->getStartDate()->getTimestamp();
		}
		if ($task->getDueDate() instanceof DateTimeValue) {
			$task_data['due_date'] = $task->getDueDate()->getTimestamp();
		}

		$newtask = new ProjectTask();
		tpl_assign('task_data', $task_data);
		tpl_assign('task', $newtask);
		tpl_assign('base_task', $task);
		$this->setTemplate("add_task");
	} // copy_task


	/**
	 * Edit task
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function edit_task() {
		$this->setTemplate('add_task');

		$task = ProjectTasks::findById(get_id());
		if(!($task instanceof ProjectTask)) {
			flash_error(lang('task list dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$task->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$task_data = array_var($_POST, 'task');
		if(!is_array($task_data)) {
			$tag_names = $task->getTagNames();
			$task_data = array(
				'title' => array_var($_POST, 'title', $task->getTitle()),
				'text' => $task->getText(),
				'milestone_id' => array_var($_POST, 'milestone_id',$task->getMilestoneId()),
				'due_date' => getDateValue(array_var($_POST, 'task_due_date'), $task->getDueDate()),
				'start_date' => getDateValue(array_var($_POST, 'task_start_date', $task->getStartDate())),
				'parent_id' => $task->getParentId(),
				'project_id' => array_var($_POST, 'project_id',$task->getProjectId()),
				'tags' => is_array($tag_names) && count($tag_names) ? implode(', ', $tag_names) : '',
				'is_private' => $task->isPrivate(),
				'assigned_to' => array_var($_POST, 'assigned_to', $task->getAssignedToCompanyId() . ':' . $task->getAssignedToUserId()),
				'priority' => array_var($_POST, 'priority', $task->getPriority()),
				'send_notification' => array_var($_POST, 'notify') == 'true',
				'time_estimate' => $task->getTimeEstimate()
			); // array
			$handins = ObjectHandins::getAllHandinsByObject($task);
			$id = 0;
			if($handins){
				foreach($handins as $handin){
					$task_data['handin'.$id] =array(
		              'title' => $handin->getTitle(),
		              'assigned_to' => $handin->getResponsibleCompanyId() . ':' . $handin->getResponsibleUserId()
					); // array
					$id=$id +1;
					if($id>3) break;
				} // foreach
			} // if
		} // if

		tpl_assign('task', $task);
		tpl_assign('task_data', $task_data);

		if(is_array(array_var($_POST, 'task'))) {
			$old_owner = $task->getAssignedTo();
			if (array_var($task_data, 'parent_id') == $task->getId()) {
				flash_error(lang("task own parent error"));
				ajx_current("empty");
				return;
			}
			$old_is_private = $task->isPrivate();
			$old_project_id = $task->getProjectId();
			$project_id = $task_data['project_id'];
			if ($old_project_id != $project_id) {
				$newProject = Projects::findById($project_id);
				if($newProject && !$task->canAdd(logged_user(),$newProject)) {
					flash_error(lang('no access permissions'));
					ajx_current("empty");
					return;
				} // if
			}
			
			$task_data['due_date'] = getDateValue(array_var($_POST, 'task_due_date'));
			$task_data['start_date'] = getDateValue(array_var($_POST, 'task_start_date'));
			
			$task->setFromAttributes($task_data);
			// Set assigned to
			$assigned_to = explode(':', array_var($task_data, 'assigned_to', ''));
			$company_id = array_var($assigned_to, 0, 0);
			$user_id = array_var($assigned_to, 1, 0);
			$can_assign = can_assign_task_to_company_user(logged_user(), $task, $company_id, $user_id);
			if ($can_assign !== true) {
				flash_error($can_assign);
				return;
			}
			$task->setAssignedToCompanyId($company_id);
			$task->setAssignedToUserId($user_id);
			if(!logged_user()->isMemberOfOwnerCompany()) $task->setIsPrivate($old_is_private);
			
			$totalMinutes = (array_var($task_data, 'time_estimate_hours') * 60) +
				(array_var($task_data, 'time_estimate_minutes'));
			$task->setTimeEstimate($totalMinutes);
			
			//Add handins
			$handins = array();
			for($i = 0; $i < 4; $i++) {
				if(isset($task_data["handin$i"]) && is_array($task_data["handin$i"]) && (trim(array_var($task_data["handin$i"], 'title')) <> '')) {
					$assigned_to = explode(':', array_var($task_data["handin$i"], 'assigned_to', ''));
					$handins[] = array(
              'title' => array_var($task_data["handin$i"], 'title'),
              'responsible_company_id' => array_var($assigned_to, 0, 0),
              'responsible_user_id' => array_var($assigned_to, 1, 0)
					); // array
				} // if
			} // for
			
			if ($task->getParentId() > 0 && $task->hasChild($task->getParentId())) {
				flash_error(lang('task child of child error'));
				ajx_current("empty");
				return;
			}
			try {
				DB::beginWork();
				if($task_data['project_id'] && $task_data['project_id'] != $old_project_id){
					//update projectid for child tasks
					foreach ($task->getSubTasks() as $subtask){
						$subtask->setProjectId(array_var($task_data, 'project_id'));
						$subtask->save();
					}
				}
				$task->save();
				$task->setTagsFromCSV(array_var($task_data, 'tags'));

				try {
					$subtasks = $task->getSubTasks();
					foreach ($subtasks as $sub) {
						if (!$sub->getAssignedTo() instanceof ApplicationDataObject) {
							$sub->setAssignedToCompanyId($company_id);
							$sub->setAssignedToUserId($user_id);
						}
					}
				} catch (Exception $e) {
				}
				
				$object_controller = new ObjectController();
			    $object_controller->link_to_new_object($task);
				$object_controller->add_subscribers($task);
				$object_controller->add_custom_properties($task);
				$object_controller->add_reminders($task);
				
				ApplicationLogs::createLog($task, $task->getWorkspaces(), ApplicationLogs::ACTION_EDIT);
				
				DB::commit();
				
				try {
					if(array_var($task_data, 'send_notification') == 'checked') {
						$new_owner = $task->getAssignedTo();
						if($new_owner instanceof User) {
							Notifier::taskAssigned($task);
						} // if
					} // if
				} catch(Exception $e) {

				} // try

				flash_success(lang('success edit task list', $task->getTitle()));
				ajx_current("back");

			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // edit_task

	/**
	 * Delete task
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function delete_task() {
		ajx_current("empty");
		$project = active_or_personal_project();
		$task = ProjectTasks::findById(get_id());
		if (!($task instanceof ProjectTask)) {
			flash_error(lang('task dnx'));
			return;
		} // if

		if (!$task->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if

		try {
			DB::beginWork();
			$is_template = $task->getIsTemplate();
			$task->trash();
			ApplicationLogs::createLog($task, $task->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
			DB::commit();

			if ($is_template) {
				flash_success(lang('success delete template', $task->getTitle()));
			} else {
				flash_success(lang('success delete task list', $task->getTitle()));
			}
			if (array_var($_GET, 'quick', false)) {
				ajx_current('empty');
			} else if (array_var($_GET, 'taskview', false)){
				ajx_current('reload');
			} else {
				ajx_current('back');
			}
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete task list'));
		} // try
	} // delete_task


	// ---------------------------------------------------
	//  Tasks
	// ---------------------------------------------------

	/**
	 * Complete task
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function complete_task() {
		ajx_current("empty");
		$task = ProjectTasks::findById(get_id());
		if(!($task instanceof ProjectTask)) {
			flash_error(lang('task dnx'));
			return;
		} // if

		if(!$task->canChangeStatus(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if

		try {
			DB::beginWork();
			$task->completeTask();
			
			/*$completed_tasks = array(); //Completes the parents if all subtasks are complete
			$parent = $task->getParent();
			while ($parent instanceof ProjectTask && $parent->countOpenSubTasks() <= 0) {
				$parent->completeTask();
				$completed_tasks[] = $parent->getId();
				$milestone = ProjectMilestones::findById($parent->getMilestoneId());
				if ($milestone instanceof ProjectMilestones && $milestone->countOpenTasks() <= 0) {
					$milestone->setCompletedOn(DateTimeValueLib::now());
					ajx_extra_data(array("completedMilestone" => $milestone->getId())); 
				}
				$parent = $parent->getParent();
			}
			ajx_extra_data(array("completedTasks" => $completed_tasks));*/
			
			//Already called in completeTask
			//ApplicationLogs::createLog($task, $task->getProject(), ApplicationLogs::ACTION_CLOSE);
			DB::commit();
			flash_success(lang('success complete task'));
			
			$redirect_to = array_var($_GET, 'redirect_to', false);
			if (array_var($_GET, 'quick', false)) {
				ajx_current("empty");
				ajx_extra_data(array("task" => $task->getArrayInfo()));
			} else {
				ajx_current("reload");
			}
		} catch(Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		} // try
	} // complete_task

	/**
	 * Reopen completed task
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function open_task() {
		ajx_current("empty");
		$task = ProjectTasks::findById(get_id());
		if(!($task instanceof ProjectTask)) {
			flash_error(lang('task dnx'));
			return;
		} // if

		if(!$task->canChangeStatus(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		try {
			DB::beginWork();
			$task->openTask();
			
			/*$opened_tasks = array();
			$parent = $task->getParent();
			while ($parent instanceof ProjectTask && $parent->isCompleted()) {
				$parent->openTask();
				$opened_tasks[] = $parent->getId();
				$milestone = ProjectMilestones::findById($parent->getMilestoneId());
				if ($milestone instanceof ProjectMilestones && $milestone->isCompleted()) {
					$milestone->setCompletedOn(EMPTY_DATETIME);
					ajx_extra_data(array("openedMilestone" => $milestone->getId())); 
				}
				$parent = $parent->getParent();
			}
			ajx_extra_data(array("openedTasks" => $opened_tasks));*/
			
			//Already called in openTask
			//ApplicationLogs::createLog($task, $task->getProject(), ApplicationLogs::ACTION_OPEN);
			DB::commit();
			
			flash_success(lang('success open task'));
			
			$redirect_to = array_var($_GET, 'redirect_to', false);
			if (array_var($_GET, 'quick', false)) {
				ajx_current("empty");
				ajx_extra_data(array("task" => $task->getArrayInfo()));
			} else {
				ajx_current("reload");
			}
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error open task'));
		} // try
	} // open_task

	private function getTasksAndMilestones($page, $objects_per_page, $tag=null, $order=null, $order_dir=null, $parent_task_id = null, $project = null, $tasksAndOrMilestones = 'both'){
		if(!$parent_task_id || !is_numeric($parent_task_id))
			$parent_task_id = 0;
		$parent_string = " AND parent_id = $parent_task_id ";
		$queries = ObjectController::getDashboardObjectQueries($project, $tag);
		if ($tasksAndOrMilestones == 'both') {
			$query = $queries['Tasks'] . $parent_string . " UNION " . $queries['Milestones'];
		} else if ($tasksAndOrMilestones == 'tasks') {
			$query = $queries['Tasks'] . $parent_string;
		} else {
			$query = $queries['Milestones'];
		}
		if ($order) {
			$query .= " order by " . $order . " ";
			if ($order_dir) {
				$query .= " " . $order_dir . " ";
			}
		} else {
			$query .= " order by last_update desc ";
		}
		if ($page && $objects_per_page) {
			$start = ($page-1) * $objects_per_page;
			$query .=  " limit " . $start . "," . $objects_per_page. " ";
		} else if ($objects_per_page) {
			$query .= " limit " . $objects_per_page;
		}

		$res = DB::execute($query);
		$objects = array();
		if (!$res) return $objects;
		$rows = $res->fetchAll();
		if (!$rows) return $objects;
		$i=1;
		foreach ($rows as $row) {
			$manager= $row['object_manager_value'];
			$id = $row['oid'];
			if ($id && $manager) {
				$obj = get_object_by_manager_and_id($id, $manager);
				if ($obj->canView(logged_user())) {
					$dash_object = $obj->getDashboardObject();
					//	$dash_object['id'] = $i++;
					$objects[] = $dash_object;
				}
			} //if($id && $manager)
		}//foreach
		return $objects;
	} //getDashboardobjects

	/**
	 * Counts dashboard objects
	 *
	 * @return unknown
	 */
	private function countTasksAndMilestones($tag=null, $project=null) {
		$queries = ObjectController::getDashboardObjectQueries($project,$tag,true);
		$query = $queries['Tasks'] . " UNION " . $queries['Milestones'];
		$ret = 0;
		$res1 = DB::execute($query);
		if ($res1) {
			$rows = $res1->fetchAll();
			if ($rows) {
				foreach ($rows as $row) {
					if (isset($row['quantity'])) {
						$ret += $row['quantity'];
					}
				}//foreach
			}
		}
		return $ret;
	}
	
	/**
	 * Create a new template
	 *
	 */
	function new_template() {
		$project = active_or_personal_project();
		if(!ProjectTask::canAdd(logged_user(), $project)) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		
		$id = get_id();
		$task = ProjectTasks::findById($id);
		if (!$task instanceof ProjectTask) {
			$task_data = array('is_template' => true);
		} else {
			$task_data = array(
				'milestone_id' => $task->getMilestoneId(),
				'title' => $task->getTitle(),
				'assigned_to' => $task->getAssignedToCompanyId() . ":" . $task->getAssignedToUserId(),
				'parent_id' => $task->getParentId(),
				'priority' => $task->getPriority(),
				'tags' => implode(",", $task->getTagNames()),
				'project_id' => $task->getProjectId(),
				'time_estimate' => $task->getTimeEstimate(),
				'text' => $task->getText(),
				'is_template' => true,
				'copyId' => $task->getId(),
			); // array
			if ($task->getStartDate() instanceof DateTimeValue) {
				$task_data['start_date'] = $task->getStartDate()->getTimestamp();
			}
			if ($task->getDueDate() instanceof DateTimeValue) {
				$task_data['due_date'] = $task->getDueDate()->getTimestamp();
			}
		}

		$task = new ProjectTask();
		tpl_assign('task_data', $task_data);
		tpl_assign('task', $task);
		$this->setTemplate("add_task");
	} // new_template

	/**
	 * View a message in a printer-friendly format.
	 *
	 */
	function print_view_all() {
		$this->setLayout("html");
		$this->view_tasks();
	} // print_view
	
	function allowed_users_to_assign() {
		$comp_array = array();
		$wspace_id = isset($_GET['ws_id']) ? $_GET['ws_id'] : 0;
		$ws = Projects::findById($wspace_id);
		
		$companies = Companies::findAll();
		
		if ($companies != null) {
			foreach ($companies as $comp) {
				if ($ws != null) $users = $comp->getUsersOnProject($ws);
				else $users = $comp->getUsers();
				if (is_array($users)) {
				foreach ($users as $k => $user) {
					$proj_us = ProjectUsers::findById(array('project_id' => $wspace_id, 'user_id' => $user->getId()));
						if ($proj_us == null || !$proj_us->getCanReadTasks() || !can_assign_task(logged_user(), $ws, $user)) {
							unset($users[$k]);
						}
					}
					if (count($users) > 0) {
						$comp_data = array(
										'id' => $comp->getId(),
										'name' => $comp->getName(),
										'users' => array() 
						);
						foreach ($users as $user) {
							$comp_data['users'][] = $user->getArrayInfo();			
						}
						//if ($ws == null || can_assign_task(logged_user(), $ws, $comp)) {
						if (count($users) > 0) {
							$comp_array[] = $comp_data;
						}
					}
				}
			}
		}
		$object = array(
			"totalCount" => count($comp_array),
			"start" => 0,
			"companies" => array()
		);
		$object['companies'] = $comp_array;

		ajx_extra_data($object);
		ajx_current("empty");
	} // allowed_users_to_assign
	
} // TaskController

?>