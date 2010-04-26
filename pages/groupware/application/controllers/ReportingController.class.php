<?php

/**
 * Controller that is responsible for handling project events related requests
 *
 * @version 1.0
 * @author Marcos Saiz <marcos.saiz@gmail.com>
 * @adapted from Reece calendar <http://reececalendar.sourceforge.net/>.
 * Acknowledgements at the bottom.
 */

class ReportingController extends ApplicationController {

	/**
	 * Construct the ReportingController
	 *
	 * @access public
	 * @param void
	 * @return ReportingController
	 */
	function __construct()
	{
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct

	function chart_details()
	{
		$pcf = new ProjectChartFactory();
		$chart = $pcf->loadChart(get_id());
		$chart->ExecuteQuery();
		tpl_assign('chart', $chart);
		ajx_set_no_toolbar(true);
	}

	/**
	 * Show reporting index page
	 *
	 * @param void
	 * @return null
	 */
	function add_chart()
	{
		$factory = new ProjectChartFactory();
		$types = $factory->getChartTypes();

		$chart_data = array_var($_POST, 'chart');
		if(!is_array($chart_data)) {
			$chart_data = array(
				'type_id' => 1,
				'display_id' => 20,
				'show_in_project' => 1,
				'show_in_parents' => 0
			); // array
		} // if
		tpl_assign('chart_data', $chart_data);


		if (is_array(array_var($_POST, 'chart'))) {
			$chart = $factory->getChart(array_var($chart_data, 'type_id'));
			$chart->setDisplayId(array_var($chart_data, 'display_id'));
			$chart->setTitle(array_var($chart_data, 'title'));

			if (array_var($chart_data, 'save') == 1){
				$chart->setFromAttributes($chart_data);

				try {
					DB::beginWork();
					$chart->save();
					DB::commit();
					flash_success(lang('success add chart', $chart->getTitle()));
					ajx_current('back');
				} catch(Exception $e) {
					DB::rollback();
					flash_error($e->getMessage());
					ajx_current("empty");
				}
				return;
			}

			$chart->ExecuteQuery();
			tpl_assign('chart', $chart);
			ajx_replace(true);
		}
		tpl_assign('chart_displays', $factory->getChartDisplays());
		tpl_assign('chart_list', $factory->getChartTypes());
	}

	function delete_chart(){
		$chart = ProjectCharts::findById(get_id());
		if(!($chart instanceof ProjectChart)) {
			flash_error(lang('chart dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$chart->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		try {

			DB::beginWork();
			$chart->trash();
			ApplicationLogs::createLog($chart, $chart->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
			DB::commit();

			flash_success(lang('success deleted chart', $chart->getTitle()));
			ajx_current("back");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete chart'));
			ajx_current("empty");
		} // try
	}

	/**
	 * Show reporting add chart page
	 *
	 * @param void
	 * @return null
	 */
	function index()
	{
		ajx_set_no_toolbar(true);
	}

	function list_all()
	{
		ajx_current("empty");

		$pid = array_var($_GET, 'active_project', 0);
		$project = Projects::findById($pid);
		$isProjectView = ($project instanceof Project);
			
		$start = array_var($_GET,'start');
		$limit = array_var($_GET,'limit');
		if (! $start) {
			$start = 0;
		}
		if (! $limit) {
			$limit = config_option('files_per_page');
		}
		$order = array_var($_GET,'sort');
		$orderdir = array_var($_GET,'dir');
		$tag = array_var($_GET,'tag');
		$page = (integer) ($start / $limit) + 1;
		$hide_private = !logged_user()->isMemberOfOwnerCompany();

		if (array_var($_GET,'action') == 'delete') {
			$ids = explode(',', array_var($_GET, 'charts'));
			list($succ, $err) = ObjectController::do_delete_objects($ids, 'ProjectCharts');
			if ($err > 0) {
				flash_error(lang('error delete objects', $err));
			} else {
				flash_success(lang('success delete objects', $succ));
			}
		} else if (array_var($_GET, 'action') == 'tag') {
			$ids = explode(',', array_var($_GET, 'charts'));
			$tagTag = array_var($_GET, 'tagTag');
			list($succ, $err) = ObjectController::do_tag_object($tagTag, $ids, 'ProjectCharts');
			if ($err > 0) {
				flash_error(lang('error tag objects', $err));
			} else {
				flash_success(lang('success tag objects', $succ));
			}
		}

		if($page < 0) $page = 1;

		//$conditions = logged_user()->isMemberOfOwnerCompany() ? '' : ' `is_private` = 0';
		if ($tag == '' || $tag == null) {
			$tagstr = " '1' = '1'"; // dummy condition
		} else {
			$tagstr = "(select count(*) from " . TABLE_PREFIX . "tags where " .
			TABLE_PREFIX . "project_charts.id = " . TABLE_PREFIX . "tags.rel_object_id and " .
			TABLE_PREFIX . "tags.tag = '".$tag."' and " . TABLE_PREFIX . "tags.rel_object_manager ='ProjectCharts' ) > 0 ";
		}
		$permission_str = ''; /* ' AND (' . permissions_sql_for_listings(ProjectCharts::instance(),
		ACCESS_LEVEL_READ,
		logged_user()) . ')';*/

		if ($isProjectView) {
			$pids = $project->getAllSubWorkspacesCSV(true, logged_user());
		} else {
			$pids = logged_user()->getActiveProjectIdsCSV();
		}
		$project_str = " AND `project_id` IN ($pids) ";

		list($charts, $pagination) = ProjectCharts::paginate(
		array("conditions" => '`trashed_by_id` = 0 AND ' . $tagstr . $permission_str . $project_str ,
	        		'order' => '`title` ASC'),
		config_option('files_per_page', 10),
		$page
		); // paginate

		tpl_assign('totalCount', $pagination->getTotalItems());
		tpl_assign('charts', $charts);
		tpl_assign('pagination', $pagination);
		tpl_assign('tags', Tags::getTagNames());

		$object = array(
			"totalCount" => $pagination->getTotalItems(),
			"charts" => array()
		);

		$factory = new ProjectChartFactory();
		$types = $factory->getChartDisplays();

		if (isset($charts))
		{
			foreach ($charts as $c) {
				if ($c->getProject() instanceof Project)
				$tags = project_object_tags($c);
				else
				$tags = "";
					
				$object["charts"][] = array(
				"id" => $c->getId(),
				"name" => $c->getTitle(),
				"type" => $types[$c->getDisplayId()],
				"tags" => $tags,
				"project" => $c->getProject()?$c->getProject()->getName():'',
				"projectId" => $c->getProjectId()
				);
			}
		}
		ajx_extra_data($object);
		tpl_assign("listing", $object);
	}



	// ---------------------------------------------------
	//  Tasks Reports
	// ---------------------------------------------------

	function total_task_times_p(){
		$comp = logged_user()->getCompany();
		$users = ( $comp instanceof Company ? $comp->getUsers() : owner_company()->getUsers());
		$workspaces = logged_user()->getActiveProjects();

		tpl_assign('workspaces', $workspaces);
		tpl_assign('users', $users);
		tpl_assign('has_billing',BillingCategories::count() > 0);
	}

	function total_task_times($report_data = null, $task = null){
		$this->setTemplate('report_wrapper');

		if (!$report_data) {
			$report_data = array_var($_POST, 'report');
			// save selections into session
			$_SESSION['total_task_times_report_data'] = $report_data;
		}

		$user = Users::findById(array_var($report_data, 'user'));
		$workspace = Projects::findById(array_var($report_data, 'project_id'));
		if ($workspace instanceof Project){
			if (array_var($report_data, 'include_subworkspaces'))
			$workspacesCSV = $workspace->getAllSubWorkspacesCSV(false,logged_user());
			else
			$workspacesCSV = $workspace->getId();
		} else {
			$workspacesCSV = logged_user()->getActiveProjectIdsCSV();
		}

		$st = DateTimeValueLib::now();
		$et = DateTimeValueLib::now();
		switch (array_var($report_data, 'date_type')){
			case 1: //Today
				$now = DateTimeValueLib::now();
				$st = DateTimeValueLib::make(0,0,0,$now->getMonth(),$now->getDay(),$now->getYear());
				$et = DateTimeValueLib::make(23,59,59,$now->getMonth(),$now->getDay(),$now->getYear());break;
			case 2: //This week
				$now = DateTimeValueLib::now();
				$monday = $now->getMondayOfWeek();
				$nextMonday = $now->getMondayOfWeek()->add('w',1)->add('d',-1);
				$st = DateTimeValueLib::make(0,0,0,$monday->getMonth(),$monday->getDay(),$monday->getYear());
				$et = DateTimeValueLib::make(23,59,59,$nextMonday->getMonth(),$nextMonday->getDay(),$nextMonday->getYear());break;
			case 3: //Last week
				$now = DateTimeValueLib::now();
				$monday = $now->getMondayOfWeek()->add('w',-1);
				$nextMonday = $now->getMondayOfWeek()->add('d',-1);
				$st = DateTimeValueLib::make(0,0,0,$monday->getMonth(),$monday->getDay(),$monday->getYear());
				$et = DateTimeValueLib::make(23,59,59,$nextMonday->getMonth(),$nextMonday->getDay(),$nextMonday->getYear());break;
			case 4: //This month
				$now = DateTimeValueLib::now();
				$st = DateTimeValueLib::make(0,0,0,$now->getMonth(),1,$now->getYear());
				$et = DateTimeValueLib::make(23,59,59,$now->getMonth(),1,$now->getYear())->add('M',1)->add('d',-1);break;
			case 5: //Last month
				$now = DateTimeValueLib::now();
				$now->add('M',-1);
				$st = DateTimeValueLib::make(0,0,0,$now->getMonth(),1,$now->getYear());
				$et = DateTimeValueLib::make(23,59,59,$now->getMonth(),1,$now->getYear())->add('M',1)->add('d',-1);break;
			case 6: //Date interval
				$st = getDateValue(array_var($report_data, 'start_value'));
				$st = $st->beginningOfDay();
				$et = getDateValue(array_var($report_data, 'end_value'));
				$et = $et->beginningOfDay()->add('d',1);
				break;
		}

		$st = new DateTimeValue($st->getTimestamp() - logged_user()->getTimezone() * 3600);
		$et = new DateTimeValue($et->getTimestamp() - logged_user()->getTimezone() * 3600);
		$timeslotType = array_var($report_data, 'timeslot_type', 0);
		$group_by = array();
		for ($i = 1; $i  <= 3; $i++){
			if ($timeslotType == 0)
			$gb = array_var($report_data, 'group_by_' . $i);
			else
			$gb = array_var($report_data, 'alt_group_by_' . $i);

			if ($gb != '0')
			$group_by[] = $gb;
		}

		$timeslotsArray = Timeslots::getTaskTimeslots($workspace, $user, $workspacesCSV, $st, $et, array_var($report_data, 'task_id', 0), $group_by,null,0,0,$timeslotType);
		$unworkedTasks = null;
		if (array_var($report_data, 'include_unworked') == 'checked') {
			$unworkedTasks = ProjectTasks::getPendingTasks(logged_user(), $workspace);
			tpl_assign('unworkedTasks', $unworkedTasks);
		}

		tpl_assign('timeslot_type', $timeslotType);
		tpl_assign('group_by', $group_by);
		tpl_assign('timeslotsArray', $timeslotsArray);
		tpl_assign('workspace', $workspace);
		tpl_assign('start_time', $st);
		tpl_assign('end_time', $et);
		tpl_assign('user', $user);
		tpl_assign('post', $report_data);
		tpl_assign('template_name', 'total_task_times');
		tpl_assign('title',lang('task time report'));
	}

	function total_task_times_by_task_print(){
		$this->setLayout("html");

		$task = ProjectTasks::findById(get_id());

		$st = DateTimeValueLib::make(0,0,0,1,1,1900);
		$et = DateTimeValueLib::make(23,59,59,12,31,2036);

		$timeslotsArray = Timeslots::getTaskTimeslots(null,null,null,$st,$et, get_id());

		tpl_assign('estimate', $task->getTimeEstimate());
		//tpl_assign('timeslots', $timeslots);
		tpl_assign('timeslotsArray', $timeslotsArray);
		tpl_assign('workspace', $task->getProject());
		tpl_assign('template_name', 'total_task_times');
		tpl_assign('title',lang('task time report'));
		tpl_assign('task_title', $task->getTitle());
		$this->setTemplate('report_printer');
	}

	function total_task_times_print(){
		$this->setLayout("html");

		$report_data = json_decode(str_replace("'",'"', array_var($_POST, 'post')),true);

		$this->total_task_times($report_data);
		$this->setTemplate('report_printer');
	}

	function total_task_times_vs_estimate_comparison_p(){
		$users = owner_company()->getUsers();
		$workspaces = logged_user()->getActiveProjects();

		tpl_assign('workspaces', $workspaces);
		tpl_assign('users', $users);
	}

	function total_task_times_vs_estimate_comparison($report_data = null, $task = null){
		$this->setTemplate('report_wrapper');

		if (!$report_data)
		$report_data = array_var($_POST, 'report');

		$workspace = Projects::findById(array_var($report_data, 'project_id'));
		if ($workspace instanceof Project){
			if (array_var($report_data, 'include_subworkspaces'))
			$workspacesCSV = $workspace->getAllSubWorkspacesCSV(false,logged_user());
			else
			$workspacesCSV = $workspace->getId();
		}
		else {
			$workspacesCSV = logged_user()->getActiveProjectIdsCSV();
		}

		$start = getDateValue(array_var($report_data, 'start_value'));
		$end = getDateValue(array_var($report_data, 'end_value'));

		$st = $start->beginningOfDay();
		$et = $end->endOfDay();
		$st = new DateTimeValue($st->getTimestamp() - logged_user()->getTimezone() * 3600);
		$et = new DateTimeValue($et->getTimestamp() - logged_user()->getTimezone() * 3600);

		$timeslots = Timeslots::getTimeslotsByUserWorkspacesAndDate($st,$et,'ProjectTasks',null,$workspacesCSV,array_var($report_data, 'task_id',0));

		tpl_assign('timeslots', $timeslots);
		tpl_assign('workspace', $workspace);
		tpl_assign('start_time', $st);
		tpl_assign('end_time', $et);
		tpl_assign('user', $user);
		tpl_assign('post', $report_data);
		tpl_assign('template_name', 'total_task_times');
		tpl_assign('title',lang('task time report'));
	}

	// ---------------------------------------------------
	//  Custom Reports
	// ---------------------------------------------------

	function add_custom_report(){
		tpl_assign('url', get_url('reporting', 'add_custom_report'));
		$report_data = array_var($_POST, 'report');
		if(is_array($report_data)){
			tpl_assign('report_data', $report_data);
			$conditions = array_var($_POST, 'conditions');
			if(!is_array($conditions))
			$conditions = array();
			tpl_assign('conditions', $conditions);
			$columns = array_var($_POST, 'columns');
			if(is_array($columns) && count($columns) > 0){
				tpl_assign('columns', $columns);
				$newReport = new Report();

				if(!$newReport->canAdd(logged_user())) {
					flash_error(lang('no access permissions'));
					ajx_current("empty");
					return;
				} // if

				$newReport->setName($report_data['name']);
				$newReport->setDescription($report_data['description']);
				$newReport->setObjectType($report_data['object_type']);
				$newReport->setOrderBy($report_data['order_by']);
				$newReport->setIsOrderByAsc($report_data['order_by_asc'] == 'asc');
				try{
					DB::beginWork();
					$newReport->save();
					$allowed_columns = $this->get_allowed_columns($report_data['object_type']);

					foreach($conditions as $condition){
						foreach ($allowed_columns as $ac){
							if ($condition['field_name'] == $ac['id']){
								$newCondition = new ReportCondition();
								$newCondition->setReportId($newReport->getId());
								$newCondition->setCustomPropertyId($condition['custom_property_id']);
								$newCondition->setFieldName($condition['field_name']);
								$newCondition->setCondition($condition['condition']);
								
								$condValue = array_key_exists('value', $condition) ? $condition['value'] : '';
								if($condition['field_type'] == 'boolean'){
									$newCondition->setValue(array_key_exists('value', $condition));
								}else if($condition['field_type'] == 'date'){
									if ($condValue != '') {
										$dtFromWidget = DateTimeValueLib::dateFromFormatAndString(user_config_option('date_format', 'd/m/Y'), $condValue);
										$newCondition->setValue(date("m/d/Y", $dtFromWidget->getTimestamp()));
									}
								}else{
									$newCondition->setValue($condValue);
								}
								$newCondition->setIsParametrizable(isset($condition['is_parametrizable']));
								$newCondition->save();
							}
						}
					}

					foreach($columns as $column){
						foreach ($allowed_columns as $ac){
							if ($column == $ac['id']){
								$newColumn = new ReportColumn();
								$newColumn->setReportId($newReport->getId());
								if(is_numeric($column)){
									$newColumn->setCustomPropertyId($column);
								}else{
									$newColumn->setFieldName($column);
								}
								$newColumn->save();
								break;
							}
						}
					}
					DB::commit();
					flash_success(lang('custom report created'));
					ajx_current('back');
				}catch(Exception $e){
					DB::rollback();
					flash_error($e->getMessage());
					ajx_current("empty");
				}
			}
		}
		$selected_type = array_var($_GET, 'type', '');
		$types = array(
		array("", lang("select one")),
		array("Companies", lang("companies")),
		array("Contacts", lang("contacts")),
		array("MailContents", lang("email type")),
		array("ProjectEvents", lang("events")),
		array("ProjectFiles", lang("file")),
		array("ProjectMilestones", lang("milestone")),
		array("ProjectMessages", lang("message")),
		array("ProjectTasks", lang("task")),
		array("Users", lang("user")),
		array("ProjectWebpages", lang("webpage")),
		array("Projects", lang("workspace")),
		);
		tpl_assign('object_types', $types);
		tpl_assign('selected_type', $selected_type);
	}

	function edit_custom_report(){
		$report_id = array_var($_GET, 'id');
		$report = Reports::getReport($report_id);

		if(!$report->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		if(is_array(array_var($_POST, 'report'))) {
			try{
				$report_data = array_var($_POST, 'report');
				DB::beginWork();
				$report->setName($report_data['name']);
				$report->setDescription($report_data['description']);
				$report->setObjectType($report_data['object_type']);
				$report->setOrderBy($report_data['order_by']);
				$report->setIsOrderByAsc($report_data['order_by_asc'] == 'asc');
				$report->save();
				$conditions = array_var($_POST, 'conditions');
				if (!is_array($conditions))
				$conditions = array();
				foreach($conditions as $condition){
					$newCondition = new ReportCondition();
					if($condition['id'] > 0){
						$newCondition = ReportConditions::getCondition($condition['id']);
					}
					if($condition['deleted'] == "1"){
						$newCondition->delete();
						continue;
					}
					$newCondition->setReportId($report_id);
					$custom_prop_id = isset($condition['custom_property_id']) ? $condition['custom_property_id'] : 0;
					$newCondition->setCustomPropertyId($custom_prop_id);
					$newCondition->setFieldName($condition['field_name']);
					$newCondition->setCondition($condition['condition']);
					if($condition['field_type'] == 'boolean'){
						$newCondition->setValue(isset($condition['value']) && $condition['value']);
					}else if($condition['field_type'] == 'date'){
						if ($condition['value'] == '') $newCondition->setValue('');
						else {
							$dtFromWidget = DateTimeValueLib::dateFromFormatAndString(user_config_option('date_format', 'd/m/Y'), $condition['value']);
							$newCondition->setValue(date("m/d/Y", $dtFromWidget->getTimestamp()));
						}
					}else{
						$newCondition->setValue(isset($condition['value']) ? $condition['value'] : '');
					}
					$newCondition->setIsParametrizable(isset($condition['is_parametrizable']));
					$newCondition->save();
				}
				ReportColumns::delete('report_id = ' . $report_id);
				$columns = array_var($_POST, 'columns');
				foreach($columns as $column){
					$newColumn = new ReportColumn();
					$newColumn->setReportId($report_id);
					if(is_numeric($column)){
						$newColumn->setCustomPropertyId($column);
					}else{
						$newColumn->setFieldName($column);
					}
					$newColumn->save();
				}
				DB::commit();
				flash_success(lang('custom report updated'));
				ajx_current('back');
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		}else{
			$this->setTemplate('add_custom_report');
			tpl_assign('url', get_url('reporting', 'edit_custom_report', array('id' => $report_id)));
			if($report instanceof Report){
				tpl_assign('id', $report_id);
				$report_data = array(
					'name' => $report->getName(),
					'description' => $report->getDescription(),
					'object_type' => $report->getObjectType(),
					'order_by' => $report->getOrderBy(),
					'order_by_asc' => $report->getIsOrderByAsc()
				);
				tpl_assign('report_data', $report_data);
				$conditions = ReportConditions::getAllReportConditions($report_id);
				tpl_assign('conditions', $conditions);
				$columns = ReportColumns::getAllReportColumns($report_id);
				$colIds = array();
				foreach($columns as $col){
					if($col->getCustomPropertyId() > 0){
						$colIds[] = $col->getCustomPropertyId();
					}else{
						$colIds[] = $col->getFieldName();
					}
				}
				tpl_assign('columns', $colIds);
			}

			$selected_type = $report->getObjectType();
			$types = array(
			array("", lang("select one")),
			array("Companies", lang("companies")),
			array("Contacts", lang("contacts")),
			array("MailContents", lang("email type")),
			array("ProjectEvents", lang("events")),
			array("ProjectFiles", lang("file")),
			array("ProjectMilestones", lang("milestone")),
			array("ProjectMessages", lang("message")),
			array("ProjectTasks", lang("task")),
			array("Users", lang("user")),
			array("ProjectWebpages", lang("webpage")),
			array("Projects", lang("workspace")),
			);
			tpl_assign('object_types', $types);
			tpl_assign('selected_type', $selected_type);
		}
	}

	function view_custom_report(){
		$report_id = array_var($_GET, 'id');
		tpl_assign('id', $report_id);
		if(isset($report_id)){
			$report = Reports::getReport($report_id);
			$conditions = ReportConditions::getAllReportConditions($report_id);
			$paramConditions = array();
			foreach($conditions as $condition){
				if($condition->getIsParametrizable()){
					$paramConditions[] = $condition;
				}
			}
			eval('$managerInstance = ' . $report->getObjectType() . "::instance();");
			$externalCols = $managerInstance->getExternalColumns();
			$externalFields = array();
			foreach($externalCols as $extCol){
				$externalFields[$extCol] = $this->get_ext_values($extCol);
			}
			$params = array_var($_GET, 'params');
			if(count($paramConditions) > 0 && !isset($params)){
				$this->setTemplate('custom_report_parameters');
				tpl_assign('model', $report->getObjectType());
				tpl_assign('title', $report->getName());
				tpl_assign('description', $report->getDescription());
				tpl_assign('conditions', $paramConditions);
				tpl_assign('external_fields', $externalFields);
			}else{
				$this->setTemplate('report_wrapper');
				tpl_assign('template_name', 'view_custom_report');
				tpl_assign('title', $report->getName());
				$offset = array_var($_GET, 'offset');
				if(!isset($offset)) $offset = 0;
				$limit = array_var($_GET, 'limit');
				if(!isset($limit)) $limit = 50;
				$results = Reports::executeReport($report_id, $params, $offset, $limit);
				if(!isset($results['columns'])) $results['columns'] = array(); 
				tpl_assign('columns', $results['columns']);
				if(!isset($results['rows'])) $results['rows'] = array();
				tpl_assign('rows', $results['rows']);
				if(!isset($results['pagination'])) $results['pagination'] = '';
				tpl_assign('pagination', $results['pagination']);
				tpl_assign('types', self::get_report_column_types($report_id));
				tpl_assign('post', $params);
				tpl_assign('model', $report->getObjectType());
				tpl_assign('description', $report->getDescription());
				tpl_assign('conditions', $conditions);
				tpl_assign('parameters', $params);
			}
		}
	}

	function view_custom_report_print(){
		$this->setLayout("html");

		$params = json_decode(str_replace("'",'"', array_var($_POST, 'post')),true);

		$report_id = array_var($_POST, 'id');
		$report = Reports::getReport($report_id);
		$results = Reports::executeReport($report_id, $params);
		if(isset($results['columns'])) tpl_assign('columns', $results['columns']);
		if(isset($results['rows'])) tpl_assign('rows', $results['rows']);

		tpl_assign('types', self::get_report_column_types($report_id));
		tpl_assign('template_name', 'view_custom_report');
		tpl_assign('title', $report->getName());
		tpl_assign('model', $report->getObjectType());
		tpl_assign('description', $report->getDescription());
		$conditions = ReportConditions::getAllReportConditions($report_id);
		tpl_assign('conditions', $conditions);
		tpl_assign('parameters', $params);
		$this->setTemplate('report_printer');
	}

	function delete_custom_report(){
		$report_id = array_var($_GET, 'id');
		$report = Reports::getReport($report_id);

		if(!$report->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		try{
			DB::beginWork();
			$report->delete();
			DB::commit();
			ajx_current("reload");
		}catch(Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
			ajx_current("empty");
		} // try
	}

	function get_object_fields(){
		$fields = $this->get_allowed_columns(array_var($_GET, 'object_type'));

		ajx_current("empty");
		ajx_extra_data(array('fields' => $fields));
	}

	function get_external_field_values(){
		$field = array_var($_GET, 'external_field');
		$values = $this->get_ext_values($field);
		ajx_current("empty");
		ajx_extra_data(array('values' => $values));
	}

	private function get_ext_values($field){
		$values = array(array('id' => '', 'name' => '-- ' . lang('select') . ' --'));
		if($field == 'company_id' || $field == 'assigned_to_company_id'){
			$companies = Companies::getVisibleCompanies(logged_user());
			foreach($companies as $company){
				$values[] = array('id' => $company->getId(), 'name' => $company->getName());
			}
		}else if($field == 'user_id' || $field == 'created_by_id' || $field == 'updated_by_id' || $field == 'assigned_to_user_id' || $field == 'completed_by_id'){
			$users = Users::getVisibleUsers(logged_user());
			foreach($users as $user){
				$values[] = array('id' => $user->getId(), 'name' => $user->getDisplayName());
			}
		}else if($field == 'milestone_id'){
			$milestones = ProjectMilestones::getActiveMilestonesByUser(logged_user());
			foreach($milestones as $milestone){
				$values[] = array('id' => $milestone->getId(), 'name' => $milestone->getName());
			}
		}
		return $values;
	}

	private function get_allowed_columns($object_type) {
		$fields = array();
		if(isset($object_type)){
			$customProperties = CustomProperties::getAllCustomPropertiesByObjectType($object_type);
			$objectFields = array();
			foreach($customProperties as $cp){
				if(!$cp->getIsMultipleValues()){
					$fields[] = array('id' => $cp->getId(), 'name' => $cp->getName(), 'type' => $cp->getType(), 'values' => $cp->getValues());
				}
			}
			eval('$managerInstance = ' . $object_type . "::instance();");
			$objectColumns = $managerInstance->getColumns();
			$objectFields = array();
			$objectColumns = array_diff($objectColumns, $managerInstance->getSystemColumns());
			foreach($objectColumns as $column){
				$objectFields[$column] = $managerInstance->getColumnType($column);
			}

			foreach($objectFields as $name => $type){
				if($type == DATA_TYPE_FLOAT || $type == DATA_TYPE_INTEGER){
					$type = 'numeric';
				}else if($type == DATA_TYPE_STRING){
					$type = 'text';
				}else if($type == DATA_TYPE_BOOLEAN){
					$type = 'boolean';
				}else if($type == DATA_TYPE_DATE || $type == DATA_TYPE_DATETIME){
					$type = 'date';
				}
				$fields[] = array('id' => $name, 'name' => lang('field ' . $object_type . ' ' .$name), 'type' => $type);
			}

			$externalFields = $managerInstance->getExternalColumns();
			foreach($externalFields as $extField){
				$fields[] = array('id' => $extField, 'name' => lang('field ' . $object_type . ' '.$extField), 'type' => 'external');
			}
		}
		usort($fields, array(&$this, 'compare_FieldName'));
		return $fields;
	}

	function compare_FieldName($field1, $field2){
		return strnatcmp($field1['name'], $field2['name']);
	}

	private function get_report_column_types($report_id) {
		$col_types = array();
		$report = Reports::getReport($report_id);
		$model = $report->getObjectType();
		$manager = new $model();

		$columns = ReportColumns::getAllReportColumns($report_id);

		foreach ($columns as $col) {
			$cp_id = $col->getCustomPropertyId();
			if ($cp_id == 0)
			$col_types[$col->getFieldName()] = $manager->getColumnType($col->getFieldName());
			else {
				$cp = CustomProperties::getCustomProperty($cp_id);
				if ($cp)
				$col_types[$cp->getName()] = $cp->getOgType();
			}
		}

		return $col_types;
	}
}
?>
