<?php

/**
 * ProjectTasks, generated on Sat, 04 Mar 2006 12:50:11 +0100 by
 * DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ProjectTasks extends BaseProjectTasks {

	const ORDER_BY_ORDER = 'order';
	const ORDER_BY_STARTDATE = 'startDate';
	const ORDER_BY_DUEDATE = 'dueDate';
	const PRIORITY_HIGH = 300;
	const PRIORITY_NORMAL = 200;
	const PRIORITY_LOW = 100;

	/**
	 * Return tasks lists for the next two weeks which don't have due date and have not been completed.
	 *
	 * @param Project $project
	 * @return array
	 */
	static function getPendingTasks(User $user, $project, $tag = null) {
		if ($project instanceof Project)
			$project_ids = $project->getAllSubWorkspacesCSV();
		else
			$project_ids = $user->getActiveProjectIdsCSV();

		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectTasks::instance(),ACCESS_LEVEL_READ, logged_user(), 'project_id') .')';
		$tagStr = $tag? (" AND id in (SELECT rel_object_id from " . TABLE_PREFIX . "tags t WHERE tag='".$tag."' AND t.rel_object_manager='ProjectTasks')"):'';

		$objects = self::findAll(array(
				'conditions' => array('((`assigned_to_user_id` = ? AND `assigned_to_company_id` = ? ) ' .
					' OR (`assigned_to_user_id` = ? AND `assigned_to_company_id` = ?) ' .
					' OR (`assigned_to_user_id` = ? AND `assigned_to_company_id` = ?)) '.
					' AND `completed_on` = ? AND parent_id = ? AND (due_date > DATE(CURRENT_TIMESTAMP) OR due_date = \'00:00:00 00-00-0000\')' .
					' AND `is_template` = false ' .
					' AND project_id in (' . $project_ids . ')' . $permissions . $tagStr, $user->getId(), $user->getCompanyId(),
		0, $user->getCompanyId(), 0, 0, EMPTY_DATETIME,0, EMPTY_DATETIME),
        			'order' => 'priority DESC, `created_on` DESC'
        			));
        			return $objects;
	} // getAllFilesByProject

	/**
	 * Return tasks on which the user has an open timeslot
	 *
	 * @param User $user
	 * @param Project $project
	 * @return array
	 */
	static function getOpenTimeslotTasks(User $user, User $logged_user, $project = null, $tag = null, $assigned_to_company = null, $assigned_to_user = null) {
		if ($project)
			$project_ids = $project->getAllSubWorkspacesCSV(false, $logged_user);
		else{
			$project_ids = $logged_user->getActiveProjectIdsCSV();
		}

		$openTimeslot = " AND id in (SELECT object_id from " . TABLE_PREFIX . "timeslots t WHERE user_id="
		. $user->getId() . " AND t.object_manager='ProjectTasks' AND t.end_time='" . EMPTY_DATETIME . "')";

		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectTasks::instance(),ACCESS_LEVEL_READ, logged_user(), 'project_id') .')';
		$tagStr = $tag? (" AND id in (SELECT rel_object_id from " . TABLE_PREFIX . "tags t WHERE tag=".DB::escape($tag)." AND t.rel_object_manager='ProjectTasks')"):'';
	

		$assignedToStr = "";
		if ($assigned_to_company) {
			if ($assigned_to_company == -1)
				$assigned_to_company = 0;
			$assignedToStr .= " AND `assigned_to_company_id` = " . DB::escape($assigned_to_company) . " ";
		}
		if ($assigned_to_user) {
			if ($assigned_to_user == -1)
				$assigned_to_user = 0;
			$assignedToStr .= " AND `assigned_to_user_id` = " . DB::escape($assigned_to_user) . " ";
		}
		
		$objects = self::findAll(array(
  				'conditions' => array('`is_template` = false AND project_id in (' . $project_ids . ')' . $permissions . $tagStr . $assignedToStr . $openTimeslot),
        			'order' => 'due_date ASC, `created_on` DESC'
        			));
        			return $objects;
	} // getAllFilesByProject

	/*
	 * Return tasks for the next two weeks
	 *
	 * @param Project $project
	 * @return array
	 */
	static function getTasksForTwoWeeks() {
		$user =  logged_user();

		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectTasks::instance(),ACCESS_LEVEL_READ, logged_user(), 'project_id') .')';
		$objects = self::findAll(array(
  				'conditions' => array('((`assigned_to_user_id` = ? AND `assigned_to_company_id` = ? ) ' .
			  		' OR (`assigned_to_user_id` = ? AND `assigned_to_company_id` = ?) '.
			  		' OR (`assigned_to_user_id` = ? AND `assigned_to_company_id` = ?)) '.
					' AND `is_template` = false ' .
			  		' AND `completed_on` = ? AND parent_id = ? ' . $permissions, $user->getId(), $user->getCompanyId(),
		0, $user->getCompanyId(), 0, 0, EMPTY_DATETIME,0),
        		'order' => '`created_on`'
        		));
        		return $objects;
	} // getAllFilesByProject

	/**
	 * Return day tasks this user has access to
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getDayTasksByUser(DateTimeValue $date, User $user, $project = null, $tag = null, $assigned_to_company = null, $assigned_to_user = null) {
		if ($project instanceof Project)
		$project_ids = $project->getAllSubWorkspacesCSV();
		else
		$project_ids = $user->getActiveProjectIdsCSV();
		
		$date = $date->add('h', logged_user()->getTimezone());
		$from_date =   (new DateTimeValue($date->getTimestamp()));
		$from_date = $from_date->beginningOfDay();
		$to_date =  (new DateTimeValue($date->getTimestamp()));
		$to_date = $to_date->endOfDay();
		
		$assignedToStr = "";
		if ($assigned_to_company) {
			if ($assigned_to_company == -1)
				$assigned_to_company = 0;
			$assignedToStr .= " AND `assigned_to_company_id` = " . DB::escape($assigned_to_company) . " ";
		}
		if ($assigned_to_user) {
			if ($assigned_to_user == -1)
				$assigned_to_user = 0;
			$assignedToStr .= " AND `assigned_to_user_id` = " . DB::escape($assigned_to_user) . " ";
		}
		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectTasks::instance(),ACCESS_LEVEL_READ, logged_user(), 'project_id') .')';

		$tagStr = $tag? (" AND id in (SELECT rel_object_id from " . TABLE_PREFIX . "tags t WHERE tag=".DB::escape($tag)." AND t.rel_object_manager='ProjectTasks')"):'';

		$result = self::findAll(array(
        'conditions' => array('`is_template` = false AND `completed_on` = ? AND (`due_date` >= ? AND `due_date` < ?) AND `project_id` in (' .$project_ids . ')' . $tagStr . $permissions . $assignedToStr, EMPTY_DATETIME, $from_date, $to_date)
		)); // findAll
		return $result;
	} // getDayTasksByUser

	/**
	 * Return late tasks this user has access to
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getLateTasksByUser(User $user, $project = null, $tag = null, $assigned_to_company = null, $assigned_to_user = null) {
		if ($project instanceof Project)
		$project_ids = $project->getAllSubWorkspacesCSV();
		else
		$project_ids = $user->getActiveProjectIdsCSV();

		$to_date = DateTimeValueLib::now()->add('h', logged_user()->getTimezone())->beginningOfDay();
		
		$assignedToStr = "";
		if ($assigned_to_company) {
			if ($assigned_to_company == -1)
				$assigned_to_company = 0;
			$assignedToStr .= " AND `assigned_to_company_id` = " . DB::escape($assigned_to_company) . " ";
		}
		if ($assigned_to_user) {
			if ($assigned_to_user == -1)
				$assigned_to_user = 0;
			$assignedToStr .= " AND `assigned_to_user_id` = " . DB::escape($assigned_to_user) . " ";
		}
			
		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectTasks::instance(),ACCESS_LEVEL_READ, logged_user(), 'project_id') .')';

		$tagStr = $tag? (" AND id in (SELECT rel_object_id from " . TABLE_PREFIX . "tags t WHERE tag=".DB::escape($tag)." AND t.rel_object_manager='ProjectTasks')"):'';

		$result = self::findAll(array(
        'conditions' => array('`is_template` = false AND `completed_on` = ? AND `due_date` > \'00:00:00 00-00-0000\' AND `due_date` < ? AND `project_id` in (' .$project_ids . ')' . $tagStr . $permissions . $assignedToStr, EMPTY_DATETIME, $to_date),
      	'order' => '`due_date` ASC'
       )); // findAll
       return $result;
	} // getDayTasksByUser
	
	/**
	 * Returns all task templates
	 *
	 */
	static function getAllTaskTemplates($only_parent_task_templates = false){
		$conditions = " `is_template` = true " ;
		if($only_parent_task_templates)
			$conditions .= "  and `parent_id` = 0  ";
		$order_by = "`title` ASC";
		$tasks = ProjectTasks::find(array(
				'conditions' => $conditions,
				'order' => $order_by
		));
		if (!is_array($tasks)) $tasks = array();
		return $tasks;
	}
	
	/**
	 * Returns workspace task templates
	 *
	 */
	static function getWorkspaceTaskTemplates($workspace_id){		
		$table_name = new WorkspaceTemplate();
		$table_name = $table_name->getTableName(true);
		$conditions = " `is_template` = true AND `id` in (select `template_id` from " .  $table_name  . " where `workspace_id` = $workspace_id)";
		$order_by = "`title` ASC";
		$tasks = ProjectTasks::find(array(
				'conditions' => $conditions,
				'order' => $order_by
		));
		if (!is_array($tasks)) $tasks = array();
		return $tasks;
//		return ProjectTasks::getProjectTasks($workspace_id, null, 'ASC', 0, 0, null, null, null, null, null, null,true);
	}
	
	static function getProjectTasks($project = null, $order = null, $orderdir = 'ASC', $parent_id = null, $milestone_id = null, $tag = null, $assigned_to_company = null, $assigned_to_user = null, $assigned_by_user = null, $pending = false, $priority = "all", $is_template = false, $is_today = false, $is_late = false) {
		if ($order == self::ORDER_BY_STARTDATE) {
			$order_by = '`start_date` ' . $orderdir;
		} else if ($order == self::ORDER_BY_DUEDATE) {
			$order_by = '`due_date` ' . $orderdir;
		} else {
			// default
			$order_by = '`order` ' . $orderdir;
		} // if

		if ($project instanceof Project) {
			$pids = $project->getAllSubWorkspacesCSV(true, logged_user());
		} else {
			$pids = logged_user()->getActiveProjectIdsCSV();
		}
		$projectstr = " AND `project_id` IN ($pids) ";

		if ($parent_id === null) {
			$parentstr = "";
		} else {
			$parentstr = " AND `parent_id` = " . DB::escape($parent_id) . " ";
		}

		if ($milestone_id === null) {
			$milestonestr = "";
		} else {
			$milestonestr = " AND `milestone_id` = " . DB::escape($milestone_id) . " ";
		}

		if ($tag == '' || $tag == null) {
			$tagstr = "";
		} else {
			$tagstr = " AND (select count(*) from " . TABLE_PREFIX . "tags where " .
			TABLE_PREFIX . "project_tasks.id = " . TABLE_PREFIX . "tags.rel_object_id and " .
			TABLE_PREFIX . "tags.tag = ".DB::escape($tag)." and " . TABLE_PREFIX . "tags.rel_object_manager ='ProjectTasks' ) > 0 ";
		}

		$assignedToStr = "";
		if ($assigned_to_company) {
			if ($assigned_to_company == -1)
				$assigned_to_company = 0;
			$assignedToStr .= " AND `assigned_to_company_id` = " . DB::escape($assigned_to_company) . " ";
		}
		if ($assigned_to_user) {
			if ($assigned_to_user == -1)
				$assigned_to_user = 0;
			$assignedToStr .= " AND `assigned_to_user_id` = " . DB::escape($assigned_to_user) . " ";
		}

		$assignedByStr = "";
		if ($assigned_by_user) {
			$assignedByStr .= " AND (`created_by_id` = " . DB::escape($assigned_by_user) . " OR `updated_by_id` = " . DB::escape($assigned_by_user) . ") ";
		}

		if ($pending) {
			$pendingstr = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " ";
		} else {
			$pendingstr = "";
		}

		if (is_numeric($priority)) {
			$priostr = " AND `priority` = " . DB::escape($priority);
		} else {
			$priostr = "";
		}

		$permissionstr = ' AND ( ' . permissions_sql_for_listings(ProjectTasks::instance(), ACCESS_LEVEL_READ, logged_user()) . ') ';

		$otherConditions = $milestonestr . $parentstr . $projectstr . $tagstr . $assignedToStr . $assignedByStr . $pendingstr . $priostr . $permissionstr ;

		$conditions = array(' `is_template` = ' . DB::escape($is_template) . $otherConditions);

		$tasks = ProjectTasks::find(array(
				'conditions' => $conditions,
				'order' => $order_by
		));
		if (!is_array($tasks)) $tasks = array();
		return $tasks;
	} // getProjectTasks

	static function paginateProjectTasks($project = null, $order = null, $orderdir = 'ASC', $page = null, $tasks_per_page = null, $group_by_order = false, $parent_id = 0, $milestone_id = -1, $tag = null, $assigned_to_company = null, $assigned_to_user = null, $assigned_by_user = null, $pending = false) {
		if ($order == self::ORDER_BY_STARTDATE) {
			$order_by = '`start_date` ' . $orderdir;
		} else if ($order == self::ORDER_BY_DUEDATE) {
			$order_by = '`due_date` ' . $orderdir;
		} else {
			// default
			$order_by = '`order` ' . $orderdir;
		} // if

		if ((integer) $page < 1) {
			$page = 1;
		} // if
		if ((integer) $tasks_per_page < 1) {
			$tasks_per_page = 10;
		} // if

		if ($project instanceof Project) {
			$pids = $project->getAllSubWorkspacesCSV(true, logged_user());
		} else {
			$pids = logged_user()->getActiveProjectIdsCSV();
		}
		$projectstr = " AND `project_id` IN ($pids) ";

		$parentstr = " AND `parent_id` = " . DB::escape($parent_id) . " ";

		if ($milestone_id >= 0) {
			$milestonestr = " AND `milestone_id` = " . DB::escape($milestone_id) . " ";
		} else {
			$milestonestr = "";
		}

		if ($tag == '' || $tag == null) {
			$tagstr = "";
		} else {
			$tagstr = " AND (select count(*) from " . TABLE_PREFIX . "tags where " .
			TABLE_PREFIX . "project_tasks.id = " . TABLE_PREFIX . "tags.rel_object_id and " .
			TABLE_PREFIX . "tags.tag = ".DB::escape($tag)." and " . TABLE_PREFIX . "tags.rel_object_manager ='ProjectTasks' ) > 0 ";
		}

		$assignedToStr = "";
		if ($assigned_to_company) {
			$assignedToStr .= " AND `assigned_to_company_id` = " . DB::escape($assigned_to_company) . " ";
		}
		if ($assigned_to_user) {
			$assignedToStr .= " AND `assigned_to_user_id` = " . DB::escape($assigned_to_user) . " ";
		}

		$assignedByStr = "";
		if ($assigned_by_user) {
			$assignedByStr .= " AND (`created_by_id` = " . DB::escape($assigned_by_user) . " OR `updated_by_id` = " . DB::escape($assigned_by_user) . ") ";
		}

		if ($pending) {
			$pendingstr = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " ";
		} else {
			$pendingstr = "";
		}

		$permissionstr = ' AND ( ' . permissions_sql_for_listings(ProjectTasks::instance(), ACCESS_LEVEL_READ, logged_user()) . ') ';

		$otherConditions = $milestonestr . $parentstr . $projectstr . $tagstr . $assignedToStr . $assignedByStr . $pendingstr . $permissionstr;

		$conditions = array(' `is_template` = false ' . $otherConditions);

		list($tasks, $pagination) = ProjectTasks::paginate(array(
				'conditions' => $conditions,
				'order' => $order_by
		), $tasks_per_page, $page);
		if (!is_array($tasks)) $tasks = array();
		return array($tasks, $pagination);
	} // paginateProjectTasks

	function maxOrder($parentId = null, $milestoneId = null) {
		$condition = "`trashed_by_id` = 0 AND `is_template` = false";
		if (is_numeric($parentId)) {
			$condition .= " AND ";
			$condition .= " `parent_id` = " . DB::escape($parentId);
		}
		if (is_numeric($milestoneId)) {
			$condition .= " AND ";
			$condition .= " `milestone_id` = " . DB::escape($milestoneId);
		}
		$res = DB::execute("SELECT max(`order`) as `max` FROM `" . TABLE_PREFIX . "project_tasks` " .
		" WHERE " . $condition);
		if ($res->numRows() < 1) {
			return 0;
		} else {
			$row = $res->fetchRow();
			return $row["max"] + 1;
		}
	}

	/**
	 * Return Day tasks this user have access on
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getRangeTasksByUser(DateTimeValue $date_start, DateTimeValue $date_end, $assignedUser, $tags = '', $project = null){

		$from_date = new DateTimeValue($date_start->getTimestamp());
		$from_date = $from_date->beginningOfDay();
		$to_date = new DateTimeValue($date_end->getTimestamp());
		$to_date = $to_date->endOfDay();
			
		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectTasks::instance(),ACCESS_LEVEL_READ, logged_user(), 'project_id') .')';

		if ($project instanceof Project ){
			$pids = $project->getAllSubWorkspacesCSV(true, logged_user());
		} else {
			$pids = logged_user()->getActiveProjectIdsCSV();
		}
		$limitation = " AND (`project_id` IN ($pids))";
		if (isset($tags) && $tags && $tags!='') {
			$tag_str = " AND exists (SELECT * from " . TABLE_PREFIX . "tags t WHERE tag=".DB::escape($tags)." AND  ".TABLE_PREFIX."project_tasks.id=t.rel_object_id AND t.rel_object_manager='ProjectTasks') ";
		} else {
			$tag_str= "";
		}
		
		$assignedFilter = '';
		if ($assignedUser instanceof User) 
			$assignedFilter = ' AND (`assigned_to_user_id` = ' . $assignedUser->getId() . ' OR (`assigned_to_user_id` = 0 AND `assigned_to_company_id` = '. $assignedUser->getCompanyId() .')) ';
			
		$result = self::findAll(array(
        'conditions' => array('`is_template` = false AND `completed_on` = ? AND (`due_date` >= ? AND `due_date` < ?) ' . $assignedFilter . $permissions.$limitation.$tag_str, EMPTY_DATETIME, $from_date, $to_date)
		)); // findAll
		return $result;
	} // getDayTasksByUser

	/**
	 * Returns an unsaved copy of the task. Copies everything except open/closed state,
	 * anything that needs the task to have an id (like tags, properties, subtask),
	 * administrative info like who created the task and when, etc.
	 *
	 * @param ProjectTask $task
	 * @return ProjectTask
	 */
	function createTaskCopy(ProjectTask $task) {
		$new = new ProjectTask();
		$new->setMilestoneId($task->getMilestoneId());
		$new->setParentId($task->getParentId());
		$new->setTitle($task->getTitle());
		$new->setAssignedToCompanyId($task->getAssignedToCompanyId());
		$new->setAssignedToUserId($task->getAssignedToUserId());
		$new->setPriority($task->getPriority());
		$new->setProjectId($task->getProjectId());
		$new->setTimeEstimate($task->getTimeEstimate());
		$new->setText($task->getText());
		$new->setIsPrivate($task->getIsPrivate());
		$new->setOrder(ProjectTasks::maxOrder($new->getParentId(), $new->getMilestoneId()));
		$new->setStartDate($task->getStartDate());
		$new->setDueDate($task->getDueDate());
		return $new;
	}

	/**
	 * Copies subtasks from taskFrom to taskTo.
	 *
	 * @param ProjectTask $taskFrom
	 * @param ProjectTask $taskTo
	 */
	function copySubTasks(ProjectTask $taskFrom, ProjectTask $taskTo, $as_template = false) {
		foreach ($taskFrom->getSubTasks() as $sub) {
			$new = ProjectTasks::createTaskCopy($sub);
			$new->setIsTemplate($as_template);
			$new->setParentId($taskTo->getId());
			$new->setProjectId($taskTo->getProjectId());
			$new->setMilestoneId($taskTo->getMilestoneId());
			$new->setOrder(ProjectTasks::maxOrder($new->getParentId(), $new->getMilestoneId()));
			if ($sub->getIsTemplate()) {
				$new->setFromTemplateId($sub->getId());
			}
			$new->save();
			$new->copyCustomPropertiesFrom($sub);
			$new->setTagsFromCSV(implode(",", $sub->getTagNames()));
			ProjectTasks::copySubTasks($sub, $new, $as_template);
		}
	}

	function populateTimeslots($tasks_list){
		if (is_array($tasks_list) && count($tasks_list) > 0){
			$ids = array();
			$tasks = array();
			for ($i = 0; $i < count($tasks_list); $i++){
				$ids[] = $tasks_list[$i]->getId();
				$tasks[$tasks_list[$i]->getId()] = $tasks_list[$i];
				$tasks_list[$i]->timeslots = array();
				$tasks_list[$i]->timeslots_count = 0;
			}
			if (count($ids > 0)){
				$timeslots = Timeslots::findAll(array('conditions' => 'object_manager = \'ProjectTasks\' AND object_id in (' . implode(',', $ids) . ')'));
				for ($i = 0; $i < count($timeslots); $i++){
					$task = $tasks[$timeslots[$i]->getObjectId()];
					$task->timeslots[] = $timeslots[$i];
					$task->timeslots_count = count($task->timeslots);
				}
			}
		}
	}


	function populateTags($tasks_list){
		if (is_array($tasks_list) && count($tasks_list) > 0){
			$ids = array();
			$tasks = array();
			for ($i = 0; $i < count($tasks_list); $i++){
				$ids[] = $tasks_list[$i]->getId();
				$tasks[$tasks_list[$i]->getId()] = $tasks_list[$i];
				$tasks_list[$i]->tags = array();
			}
			if (count($ids > 0)){
				$tags = Tags::findAll(array('conditions' => 'rel_object_manager = \'ProjectTasks\' AND rel_object_id in (' . implode(',', $ids) . ')', 'order' => 'tag ASC'));
				for ($i = 0; $i < count($tags); $i++){
					$task = $tasks[$tags[$i]->getRelObjectId()];
					$task->tags[] = $tags[$i];
				}
			}
		}
	}
} // ProjectTasks
?>