<?php

/***************************************************************************
 *	Authors:
 *   - Reece Pegues
 *   - Opengoo Development Team
 * 	 - Sadysta (forums.opengoo.org) - iCal Server
 *   - Ras2000 (forums.opengoo.org) - Calendar starting on Mon or Sun 	 
 ***************************************************************************/

require_once ROOT.'/environment/classes/event/CalFormatUtilities.php';

/**
* Controller that is responsible for handling project events related requests
*
* @version 1.0
* @author Marcos Saiz <marcos.saiz@gmail.com>
* @adapted from Reece calendar <http://reececalendar.sourceforge.net/>.
* Acknowledgements at the bottom.
*/

class EventController extends ApplicationController {

	/**
	* Construct the EventController
	*
	* @access public
	* @param void
	* @return EventController
	*/
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');	
		// Reece calendar initialization
		//define('CAL_SECURITY_BIT',1);
		$_SESSION['cal_loginfailed'] = 0;
		$_SESSION['cal_user'] = logged_user()->getUsername();
		$_SESSION['cal_userid'] = logged_user()->getId();
		cal_add_to_links('c=event');
		cal_load_permissions();
	} // __construct
     	
	/**
	* Show events index page (list recent events)
	*
	* @param void
	* @return null
	*/
	function index($view_type = null, $user_filter = null, $status_filter = null) {
		//auth check in cal_query_get_eventlist		
		if( (!(logged_user()->isAdministrator())) && ((active_project() && !(logged_user()->isProjectUser(active_project()))))){	    	
			flash_error(lang('no access permissions'));
			$this->redirectTo('dashboard');
			return ;
	    }		
		ajx_set_no_toolbar(true);
		ajx_replace(true);
				 
		$this->getActualDateToShow($day, $month, $year);
		
		if ($view_type == null)
			$this->getUserPreferences($view_type, $user_filter, $status_filter);
				  
	    $tag = active_tag();
		tpl_assign('tags',$tag);
		
		$this->setTemplate('calendar');
		$this->setViewVariables($view_type, $user_filter, $status_filter);
	}
	
	function registerInvitations($data, $event) {
		// Invitations
		foreach ($data['users_to_invite'] as $id => $assist) {
			$conditions = array('event_id' => $event->getId(), 'user_id' => $id);
			//insert only if not exists 
			if (EventInvitations::findById($conditions) == null) { 
	            $invitation = new EventInvitation();
	            $invitation->setEventId($event->getId());
	            $invitation->setUserId($id);
	            $invitation->setInvitationState($assist);
	            $invitation->save();
            }
		}
	}
	
	function change_invitation_state($attendance = null, $event_id = null, $user_id = null) {
		$from_post = $attendance == null || $event_id == null; 
		if ($attendance == null) $attendance = array_var($_POST, 'event_attendance');
		if ($event_id == null) $event_id = array_var($_POST, 'event_id');
		if ($user_id == null) $user_id = array_var($_POST, 'user_id');
		
		if ($attendance == null || $event_id == null) {
			flash_error('Missing parameters');
			ajx_current("back");
		} else {
			$conditions = array('event_id' => $event_id, 'user_id' => $user_id);
			$inv = EventInvitations::findById($conditions);
			if ($inv != null) {
				$inv->setInvitationState($attendance);
				$inv->save();
			}
			if ($from_post) {
				// Notify creator (only when invitation is accepted or declined)
				if ($inv->getInvitationState() == 1 || $inv->getInvitationState() == 2) {
					$event = ProjectEvents::findById(array('id' => $event_id));
					$user = Users::findById(array('id' => $user_id));
					session_commit();
					Notifier::notifEventAssistance($event, $inv, $user);
				}
				flash_success(lang('success edit event', ''));
				ajx_current("back");
			}
		}
	}
	
	
	function getData($event_data){
		// get the day
			if (array_var($event_data, 'start_value') != '') {
				$date_from_widget = array_var($event_data, 'start_value');
				$dtv = getDateValue($date_from_widget);
				$day = $dtv->getDay();
	       		$month = $dtv->getMonth();
	       		$year = $dtv->getYear();
				
			} else {
				$month = isset($_GET['month'])?$_GET['month']:date('n', DateTimeValueLib::now()->getTimestamp());
				$day = isset($_GET['day'])?$_GET['day']:date('j', DateTimeValueLib::now()->getTimestamp());
				$year = isset($_GET['year'])?$_GET['year']:date('Y', DateTimeValueLib::now()->getTimestamp());
			}
       		
			if (array_var($event_data, 'start_time') != '') {
				$this->parseTime(array_var($event_data, 'start_time'), $hour, $minute);
			} else {
				$hour = array_var($event_data, 'hour');
	       		$minute = array_var($event_data, 'minute');
				if(array_var($event_data, 'pm') == 1) $hour += 12;
			}
			if (array_var($event_data, 'type_id') == 2 && $hour == 24) $hour = 23;
			
			$gmt_time = mktime($hour, $minute, 0, $month, $day, $year);
			$dt_start = new DateTimeValue($gmt_time - logged_user()->getTimezone() * 3600);
			
			// repeat defaults
			$repeat_d = 0;
			$repeat_m = 0;
			$repeat_y = 0;
			$repeat_h = 0;
			$rend = '';		
			// get the options
			$forever = 0;
			$jump = array_var($event_data,'occurance_jump');
			
			if(array_var($event_data,'repeat_option') == 1) $forever = 1;
			elseif(array_var($event_data,'repeat_option') == 2) $rnum = array_var($event_data,'repeat_num');
			elseif(array_var($event_data,'repeat_option') == 3) $rend = getDateValue(array_var($event_data,'repeat_end'));
			// verify the options above are valid
			if(isset($rnum) && $rnum !="") {
				if(!is_numeric($rnum) || $rnum < 1 || $rnum > 1000) {
					throw new Exception(CAL_EVENT_COUNT_ERROR);
				}
			} else $rnum = 0;
			if($jump != ""){
				if(!is_numeric($jump) || $jump < 1 || $jump > 1000) {
					throw new Exception(CAL_REPEAT_EVERY_ERROR);
				}
			} else $jump = 1;
			
		
		    // check for repeating options
			// 1=repeat once, 2=repeat daily, 3=weekly, 4=monthy, 5=yearly, 6=holiday repeating
			$oend = null;
			switch(array_var($event_data,'occurance')){
				case "1":
					$forever = 0;
					$repeat_d = 0;
					$repeat_m = 0;
					$repeat_y = 0;
					$repeat_h = 0;
					break;
				case "2":
					$repeat_d = $jump;
					if(isset($forever) && $forever == 1) $oend = null;
					else $oend = $rend;
					break;
				case "3":
					$repeat_d = 7 * $jump;
					if(isset($forever) && $forever == 1) $oend = null;
					else $oend = $rend;
					break;
				case "4":
					$repeat_m = $jump;
					if(isset($forever) && $forever == 1) $oend = null;
					else $oend = $rend;
					break;
				case "5":
					$repeat_y = $jump;
					if(isset($forever) && $forever == 1) $oend = null;
					else $oend = $rend;
					break;
				case "6":
					$repeat_h = 1;
					if(array_var($event_data, 'cal_holiday_lastweek')) $repeat_h = 2;
					break;
			}
			$repeat_number = $rnum;
			
		 	// get duration
			$durationhour = array_var($event_data,'durationhour');
			$durationmin = array_var($event_data,'durationmin');
			
			// get event type:  2=full day, 3=time/duratin not specified, 4=time not specified
			$typeofevent = array_var($event_data,'type_id');
			if(!is_numeric($typeofevent) OR ($typeofevent!=1 AND $typeofevent!=2 AND $typeofevent!=3)) $typeofevent = 1;

			if ($durationhour == 0 && $durationmin < 15 && $typeofevent != 2) {
				throw new Exception(lang('duration must be at least 15 minutes'));
			}
									
			// calculate timestamp and durationstamp
			// By putting through mktime(), we don't have to check for sql injection here and ensure the date is valid at the same time.
			$timestamp = $dt_start->format('Y-m-d H:i:s');
			if ($hour + $durationhour > 24)
				$dt_duration = DateTimeValueLib::make(0, 0, 0, $dt_start->getMonth(), $dt_start->getDay(), $dt_start->getYear());
			else
				$dt_duration = DateTimeValueLib::make($dt_start->getHour() + $durationhour, $dt_start->getMinute() + $durationmin, 0, $dt_start->getMonth(), $dt_start->getDay(), $dt_start->getYear());
			$durationstamp = $dt_duration->format('Y-m-d H:i:s');
			
			// organize the data expected by the query function
			$data = array();
			$data['repeat_num'] = $rnum;
			$data['repeat_h'] = $repeat_h;
			$data['repeat_d'] = $repeat_d;
			$data['repeat_m'] = $repeat_m;
			$data['repeat_y'] = $repeat_y;
			$data['repeat_forever'] = $forever;
			$data['repeat_end'] =  $oend;
			$data['start'] = $timestamp;
			$data['subject'] =  array_var($event_data,'subject');
			$data['description'] =  array_var($event_data,'description');
			$data['type_id'] = $typeofevent;
			$data['duration'] = $durationstamp;
			
			$data['users_to_invite'] = array();
			// owner user always is invited and confirms assistance
			$data['users_to_invite'][logged_user()->getId()] = 1; 

			$compstr = 'invite_user_';
			foreach ($event_data as $k => $v) {
				if (str_starts_with($k, $compstr) && ($v == 'checked' || $v == 'on')) {
					$data['users_to_invite'][substr($k, strlen($compstr))] = 0; // Pending Answer
				}
			}
			
			if (isset($event_data['confirmAttendance'])) {
				$data['confirmAttendance'] = array_var($event_data, 'confirmAttendance');
			}			
			
			if (isset($event_data['send_notification'])) {
				$data['send_notification'] = array_var($event_data,'send_notification') == 'checked';
			}
			return $data;
	}
	
	function add(){		
		if(! (ProjectEvent::canAdd(logged_user(), active_or_personal_project()))){	    	
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return ;
	    }
	    $this->setTemplate('event');
		$event = new ProjectEvent();		
		$event_data = array_var($_POST, 'event');
		$event_subject = array_var($_GET, 'subject'); //if sent from pupup
		
		$month = isset($_GET['month'])?$_GET['month']:date('n', DateTimeValueLib::now()->getTimestamp() + logged_user()->getTimezone() * 3600);
		$day = isset($_GET['day'])?$_GET['day']:date('j', DateTimeValueLib::now()->getTimestamp() + logged_user()->getTimezone() * 3600);
		$year = isset($_GET['year'])?$_GET['year']:date('Y', DateTimeValueLib::now()->getTimestamp() + logged_user()->getTimezone() * 3600);
		
		$user_filter = isset($_GET['user_filter']) ? $_GET['user_filter'] : logged_user()->getId();
		
		if(!is_array($event_data)) {
			// if data sent from quickadd popup (via get) we se it, else default
			if (isset($_GET['start_time'])) $this->parseTime($_GET['start_time'], $hour, $minute);
			else {
				$hour = isset($_GET['hour']) ? $_GET['hour'] : date('G', DateTimeValueLib::now()->getTimestamp() + logged_user()->getTimezone() * 3600);
				$minute = isset($_GET['minute']) ? $_GET['minute'] : round((date('i') / 15), 0) * 15; //0,15,30 and 45 min
			}
			if(!user_config_option('time_format_use_24')) {
				if($hour >= 12){
					$pm = 1;
					$hour = $hour - 12;
				} else $pm = 0;
			}
			$event_data = array(
				'month' => isset($_GET['month']) ? $_GET['month'] : date('n', DateTimeValueLib::now()->getTimestamp() + logged_user()->getTimezone() * 3600),
				'year' => isset($_GET['year']) ? $_GET['year'] : date('Y', DateTimeValueLib::now()->getTimestamp() + logged_user()->getTimezone() * 3600),
				'day' => isset($_GET['day']) ? $_GET['day'] : date('n', DateTimeValueLib::now()->getTimestamp() + logged_user()->getTimezone() * 3600),
				'hour' => $hour,
				'minute' => $minute,
				'pm' => (isset($pm) ? $pm : 0),
				'typeofevent' => isset($_GET['type_id']) ? $_GET['type_id'] : 1,
				'subject' => $event_subject,
				'durationhour' => isset($_GET['durationhour']) ? $_GET['durationhour'] : 1,
				'durationmin' => isset($_GET['durationmin']) ? $_GET['durationmin'] : 0,
			); // array
		} // if
		
		tpl_assign('event', $event);
		tpl_assign('event_data', $event_data);
		tpl_assign('active_projects', logged_user()->getActiveProjects());

		if (is_array(array_var($_POST, 'event'))) {
			try {
				$data = $this->getData($event_data);
				
				// run the query to set the event data 
				$projId = array_var($event_data, 'project_id');      
				if($projId != '') {
					$project = Projects::findById($projId );
				}
				else {
			 		$project = active_or_personal_project();
				}

	        	$event->setProjectId($project->getId());
			    $event->setFromAttributes($data);
			    

			    if(!logged_user()->isMemberOfOwnerCompany()) $event->setIsPrivate(false);  
		
			    DB::beginWork();
	          	$event->save();
	          	$event->setTagsFromCSV(array_var($event_data, 'tags'));   
	            
	            $this->registerInvitations($data, $event);
	            if (isset($data['confirmAttendance'])) {
	            	$this->change_invitation_state($data['confirmAttendance'], $event->getId(), $user_filter);
	            }
	            
				if (isset($data['send_notification']) && $data['send_notification']) {
					$users_to_inv = array();
            		foreach ($data['users_to_invite'] as $us => $v) {
            			if ($us != logged_user()->getId()) {
            				$users_to_inv[] = Users::findById(array('id' => $us));
            			}
            		}
            		Notifier::notifEvent($event, $users_to_inv, 'new', logged_user());
		        }
		        
			    $object_controller = new ObjectController();
			    $object_controller->link_to_new_object($event);
				$object_controller->add_subscribers($event);
				$object_controller->add_custom_properties($event);
				$object_controller->add_reminders($event);
				
				ApplicationLogs::createLog($event, $event->getWorkspaces(), ApplicationLogs::ACTION_ADD);
				
				if (array_var($_POST, 'popup', false)) {
	          		$event->subscribeUser(logged_user());
					ajx_current("reload");
	          	} else {
	          		ajx_current("back");
	          	}
	         	DB::commit();
	          	
	          	flash_success(lang('success add event', $event->getObjectName()));
	          	ajx_add("overview-panel", "reload");
	        } catch(Exception $e) {
	          	DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
	        } // try
		    
		}
	}
	
	function delete(){
		//check auth
		$event = ProjectEvents::findById(get_id());
	    if(!$event->canDelete(logged_user())){	    	
			flash_error(lang('no access permissions'));
			$this->redirectTo('event');
			return ;
	    }
		$this->setTemplate('viewdate');
		$tag = active_tag();
		tpl_assign('tags',$tag);
		try {
			
			$notifications = array();
			$invs = EventInvitations::findAll(array ('conditions' => 'event_id = ' . $event->getId()));
			if (is_array($invs)) {
				foreach ($invs as $inv) {
					if ($inv->getUserId() != logged_user()->getId()) 
						$notifications[] = Users::findById(array('id' => $inv->getUserId()));
				}
			} else {
				if ($invs->getUserId() != logged_user()->getId()) 
					$notifications[] = Users::findById(array('id' => $invs->getUserId()));
			}
			Notifier::notifEvent($event, $notifications, 'deleted', logged_user());
			
			DB::beginWork();
			// delete event
			$event->trash();
			ApplicationLogs::createLog($event, $event->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
			DB::commit();

			flash_success(lang('success delete event', $event->getSubject()));
			
			if (array_var($_POST, 'popup', false)) {
				ajx_current("reload");
          	} else {
          		ajx_current("back");
          	}
          	ajx_add("overview-panel", "reload");          	
		} catch(Exception $e) {
			DB::rollback();
			if (Env::isDebugging()) {
				Logger::log($e->getTraceAsString());
			}
			flash_error(lang('error delete event'));
			ajx_current("empty");
		} // try
	}
	
	function viewdate($view_type = null, $user_filter = null, $status_filter = null){
		$tag = active_tag();
		tpl_assign('tags',$tag);	
		tpl_assign('cal_action','viewdate');
		ajx_set_no_toolbar(true);
		
		$this->getActualDateToShow($day, $month, $year);
		
	    if ($view_type == null)
	        $this->getUserPreferences($view_type, $user_filter, $status_filter);
		
		$this->setTemplate('viewdate');
		$this->setViewVariables($view_type, $user_filter, $status_filter);
	}
	
	function viewweek($view_type = null, $user_filter = null, $status_filter = null){
		$tag = active_tag();
		tpl_assign('tags',$tag);	
		tpl_assign('cal_action','viewdate');
		ajx_set_no_toolbar(true);
		
		$this->getActualDateToShow($day, $month, $year);
		
	    if ($view_type == null)
	    	$this->getUserPreferences($view_type, $user_filter, $status_filter);
	    
	    $this->setTemplate('viewweek');
		$this->setViewVariables($view_type, $user_filter, $status_filter);
	}
	
	private function getActualDateToShow(&$day, &$month, &$year) {
		$day = isset($_GET['day']) ? $_GET['day'] : (isset($_SESSION['day']) ? $_SESSION['day'] : date('j', DateTimeValueLib::now()->getTimestamp() + logged_user()->getTimezone() * 3600));
		$month = isset($_GET['month']) ? $_GET['month'] : (isset($_SESSION['month']) ? $_SESSION['month'] : date('n', DateTimeValueLib::now()->getTimestamp() + logged_user()->getTimezone() * 3600));
	    $year = isset($_GET['year']) ? $_GET['year'] : (isset($_SESSION['year']) ? $_SESSION['year'] : date('Y', DateTimeValueLib::now()->getTimestamp() + logged_user()->getTimezone() * 3600));
	}
	
	function setViewVariables($view_type, $user_filter, $status_filter) {
		//Get Users Info
		if (logged_user()->isMemberOfOwnerCompany())
			$users = Users::getAll();
		else $users = logged_user()->getCompany()->getUsers();
		
		//Get Companies Info
		if (logged_user()->isMemberOfOwnerCompany())
			$companies = Companies::getCompaniesWithUsers();
		else $companies = array(logged_user()->getCompany());
		
		$usr = Users::findById($user_filter);
		$user_filter_comp = $usr != null ? $usr->getCompanyId() : 0;
		
		tpl_assign('users', $users);
		tpl_assign('companies', $companies);
		tpl_assign('userPreferences', array(
				'view_type' => $view_type,
				'user_filter' => $user_filter,
				'status_filter' => $status_filter,
				'user_filter_comp' => $user_filter_comp
		));
	}
	
	function getUserPreferences(&$view_type = null, &$user_filter = null, &$status_filter = null) {
		$view_type = array_var($_GET,'view_type');
		if (is_null($view_type) || $view_type == '') {
			$view_type = user_config_option('calendar view type', 'viewweek');
		}
		if (user_config_option('calendar view type', '') != $view_type)
			set_user_config_option('calendar view type', $view_type, logged_user()->getId());
		
		$user_filter = array_var($_GET,'user_filter');
		if (is_null($user_filter) || $user_filter == '') {
			$user_filter = user_config_option('calendar user filter', 0);
		}
		if ($user_filter == 0) $user_filter = logged_user()->getId(); 	
		if (user_config_option('calendar user filter', '') != $user_filter)
			set_user_config_option('calendar user filter', $user_filter, logged_user()->getId());
			
		$status_filter = array_var($_GET,'status_filter');
		if (is_null($status_filter)) {
			$status_filter = user_config_option('calendar status filter', ' 0 1 3');
		}
		if (user_config_option('calendar status filter', '') != $status_filter)
			set_user_config_option('calendar status filter', $status_filter, logged_user()->getId());
	}
	
	function view_calendar() {
		$this->getUserPreferences($view_type, $user_filter, $status_filter);
		if($view_type == 'viewdate') $this->viewdate($view_type, $user_filter, $status_filter);
		else if($view_type == 'index') $this->index($view_type, $user_filter, $status_filter);
		else $this->viewweek($view_type, $user_filter, $status_filter);
	}
	
	
	function viewevent(){
		//check auth
		$this->addHelper('textile');
		ajx_set_no_toolbar(true);
	    $event = ProjectEvents::findById(get_id());
	    if (isset($event) && $event != null) {
		    if(!$event->canView(logged_user())){
				flash_error(lang('no access permissions'));
				$this->redirectTo('event');
				return ;
		    }
			$this->setTemplate('viewevent');
			$tag = active_tag();
			tpl_assign('tags',$tag);	
			tpl_assign('event',$event);
			tpl_assign('cal_action','viewevent');	
			tpl_assign('view', array_var($_GET, 'view','month'));	
			tpl_assign('active_projects',logged_user()->getActiveProjects());
			ajx_extra_data(array("title" => $event->getSubject(), 'icon'=>'ico-calendar'));
	    } else {
	    	flash_error(lang('event dnx'));
			ajx_current("empty");
			return ;
	    }
	}

	function cal_error($text){
		$output = "<center><span class='failure'>$text</span></center><br>";
		return $output;
	}
	
		
	function edit() {
		$this->setTemplate('event');
		$event = ProjectEvents::findById(get_id());
		
		$user_filter = isset($_GET['user_id']) ? $_GET['user_id'] : logged_user()->getId();
		
		$inv = EventInvitations::findById(array('event_id' => $event->getId(), 'user_id' => $user_filter));
		if ($inv != null) {
			$event->addInvitation($inv);
		}
		$event->addInvitation($inv);
		
		if(!$event->canEdit(logged_user())){	    	
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return ;
	    }
	    
		tpl_assign('active_projects',logged_user()->getActiveProjects());
	    
		$event_data = array_var($_POST, 'event');
		if(!is_array($event_data)) {
				
			$tag_names = $event->getTagNames();
			$setlastweek = false;
			$rsel1=false;$rsel2=false; $rsel3=false;
			$forever= $event->getRepeatForever();
			$occ = 1;
			if($event->getRepeatD() > 0){ $occ = 2; $rjump = $event->getRepeatD();}
			if($event->getRepeatD() > 0 AND $event->getRepeatD()%7==0){ $occ = 3; $rjump = $event->getRepeatD()/7;}
			if($event->getRepeatM() > 0){ $occ = 4; $rjump = $event->getRepeatM();}
			if($event->getRepeatY() > 0){ $occ = 5; $rjump = $event->getRepeatY();}
			if($event->getRepeatH() > 0){ $occ = 6;}
			if($event->getRepeatH() == 2){ $setlastweek = true;}
			if($event->getRepeatEnd()) { $rend = $event->getRepeatEnd();	}
			if($event->getRepeatNum() > 0) $rnum = $event->getRepeatNum();
			if(!isset($rjump) || !is_numeric($rjump)) $rjump = 1;
			// decide which repeat type it is
			if($forever) $rsel1 = true; //forever
			else if(isset($rnum) AND $rnum>0) $rsel2 = true; //repeat n-times
			else if(isset($rend) AND $rend instanceof DateTimeValue) $rsel3 = true; //repeat until
			
			//if(isset($rend) AND $rend=="9999-00-00") $rend = "";
			// organize the time and date data for the html select drop downs.
			$thetime = $event->getStart()->getTimestamp() + logged_user()->getTimezone()*3600;
			$durtime = $event->getDuration()->getTimestamp() + logged_user()->getTimezone()*3600 - $thetime;
			$hour = date('G', $thetime);
			// format time to 24-hour or 12-hour clock.
			if(!user_config_option('time_format_use_24')){
				if($hour >= 12){
					$pm = 1;
					$hour = $hour - 12;
				}else $pm = 0;
			}
				
			$event_data = array(
	          'subject' => $event->getSubject(),
	          'description' => $event->getDescription(),
	          'name' => $event->getCreatedById(),
	          'username' => $event->getCreatedById(),
	          'typeofevent' => $event->getTypeId(),
	          'forever' => $event->getRepeatForever(),
	          'usetimeandduration' => ($event->getTypeId())==3?0:1,
	          'occ' => $occ,
	          'rjump' => $rjump,
	          'setlastweek' => $setlastweek,
	          'rend' => isset($rend)?$rend:NULL,
	          'rnum' => isset($rnum)?$rnum:NULL,
	          'rsel1' => $rsel1,
	          'rsel2' => $rsel2,
	          'rsel3' => $rsel3,
	          'thetime' => $event->getStart()->getTimestamp(),
			  'hour' => $hour,
			  'minute' => date('i', $thetime),
			  'month' => date('n', $thetime),
			  'year' => date('Y', $thetime),
			  'day' => date('j', $thetime),
			  'durtime' => ($event->getDuration()->getTimestamp() - $thetime),
			  'durationmin' => ($durtime / 60) % 60,
			  'durationhour' => ($durtime / 3600) % 24,
			  'durday' => floor($durtime / 86400),
			  'pm' => isset($pm) ? $pm : 0,
	          'tags' => is_array($tag_names) ? implode(', ', $tag_names) : ''
			); // array
		} // if
	
		tpl_assign('event_data', $event_data);
		tpl_assign('event', $event);

		if(is_array(array_var($_POST, 'event'))) {
			try {
				$data = $this->getData($event_data);
				// run the query to set the event data 
				$projId = array_var($event_data,'project_id');
				$old_project_id = $event->getProjectId();
				if($projId != '' && $projId != $old_project_id) {
					$project = Projects::findById($projId);
					if(!$event->canAdd(logged_user(),$project)) {
						flash_error(lang('no access permissions'));
						ajx_current("empty");
						return;
					} // if
	        		$event->setProjectId($project->getId());
				}
			    $event->setFromAttributes($data); 
			    
			    $this->registerInvitations($data, $event);
				if (isset($data['confirmAttendance'])) {
	            	$this->change_invitation_state($data['confirmAttendance'], $event->getId(), $user_filter);
	            }

	            if (isset($data['send_notification']) && $data['send_notification']) {
					$users_to_inv = array();
            		foreach ($data['users_to_invite'] as $us => $v) {
            			if ($us != logged_user()->getId()) {
            				$users_to_inv[] = Users::findById(array('id' => $us));
            			}
            		}
            		Notifier::notifEvent($event, $users_to_inv, 'modified', logged_user());
	            }
				    
			    if(!logged_user()->isMemberOfOwnerCompany()) $event->setIsPrivate(false);  				
	          
	          	DB::beginWork();
	         	$event->save();
	         	$event->setTagsFromCSV(array_var($event_data, 'tags')); 
			 	
			 	$object_controller = new ObjectController();
			    $object_controller->link_to_new_object($event);
				$object_controller->add_subscribers($event);
				$object_controller->add_custom_properties($event);
				$object_controller->add_reminders($event);
			 	
	          	ApplicationLogs::createLog($event, $event->getWorkspaces(), ApplicationLogs::ACTION_EDIT);
	          	DB::commit();
	          	flash_success(lang('success edit event', $event->getObjectName()));

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
	          //tpl_assign('error', $e);
	        } // try
		} // if
	} // edit
	
	
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
	
	function allowed_users_view_events() {
		$comp_array = array();
		$actual_user_id = isset($_GET['user']) ? $_GET['user'] : logged_user()->getId();
		$wspace_id = isset($_GET['ws_id']) ? $_GET['ws_id'] : 0;
		$ws = Projects::findById($wspace_id);
		
		$companies = $ws->getCompanies();
		
		$i = 0;
		foreach ($companies as $comp) {
			$users = $comp->getUsersOnProject($ws);
			if (is_array($users)) {
				
				foreach ($users as $k => $user) { // removing event creator from notification list
					$proj_us = ProjectUsers::findById(array('project_id' => $wspace_id, 'user_id' => $user->getId()));
					if ($user->getId() == $actual_user_id || $proj_us == null || !$proj_us->getCanReadEvents()) {
						unset($users[$k]);
					}
				}
				if (count($users) > 0) {
					$comp_data = array(
									'id' => $i++,
									'object_id' => $comp->getId(),
									'name' => $comp->getName(),
									'users' => array() 
					);
					foreach ($users as $user) {
						$comp_data['users'][] = array('id' => $user->getId(), 'name' => $user->getDisplayName());			
					}
					$comp_array[] = $comp_data;
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
	}
	
	function icalendar_import() {
		if (isset($_GET['from_menu']) && $_GET['from_menu'] == 1) unset($_SESSION['history_back']);
		if (isset($_SESSION['history_back'])) {
			if ($_SESSION['history_back'] > 0) $_SESSION['history_back'] = $_SESSION['history_back'] - 1;
			if ($_SESSION['history_back'] == 0) unset($_SESSION['history_back']);
			ajx_current("back");
		} else {
			$ok = false;
			$this->setTemplate('cal_import');
				
			$filedata = array_var($_FILES, 'cal_file');
			if (is_array($filedata)) {
				
				$filename = $filedata['tmp_name'].'vcal';
				copy($filedata['tmp_name'], $filename);
				
				$events_data = CalFormatUtilities::decode_ical_file($filename);
				unset($events_data[0]); //cal headers
				if (count($events_data)) {
					DB::beginWork();		
					foreach ($events_data as $ev_data) {
						$event = new ProjectEvent();
				 		$project = active_or_personal_project();
						if ($ev_data['subject'] == '') $ev_data['subject'] = lang('no subject');
			
			        	$event->setProjectId($project->getId());
					    $event->setFromAttributes($ev_data);
					    
					    $event->save();
					    $object_controller = new ObjectController();
					    $object_controller->add_subscribers($event);
					    ApplicationLogs::createLog($event, null, ApplicationLogs::ACTION_ADD);
					    
					    $this->registerInvitations($ev_data, $event);
						if (isset($ev_data['confirmAttendance'])) {
							if ($event->getCreatedBy() instanceof User)
			            		$this->change_invitation_state($ev_data['confirmAttendance'], $event->getId(), $event->getCreatedBy()->getId());
			            }
					}
					DB::commit();
					
					$ok = true;
					flash_success(lang('success import events', count($events_data)));
					$_SESSION['history_back'] = 1;
					
				} else {
					flash_error(lang('no events to import'));
				}
				unset($filename);
				if (!$ok) ajx_current("empty");				
			}
			else if (array_var($_POST, 'atimportform', 0)) ajx_current("empty");
		}
	}
	
	function icalendar_export() {
		$this->setTemplate('cal_export');
		$calendar_name = array_var($_POST, 'calendar_name');			
		if ($calendar_name != '') {
			$from = getDateValue(array_var($_POST, 'from_date'));
			$to = getDateValue(array_var($_POST, 'to_date'));
			$tags = '';
			
			$events = ProjectEvents::getRangeProjectEvents($from, $to, $tags, active_project());
			
			$buffer = CalFormatUtilities::generateICalInfo($events, $calendar_name);
			
			$filename = rand().'.tmp';
			$handle = fopen(ROOT.'/tmp/'.$filename, 'wb');
			fwrite($handle, $buffer);
			fclose($handle);
			
			$_SESSION['calendar_export_filename'] = $filename;
			$_SESSION['calendar_name'] = $calendar_name;
			flash_success(lang('success export calendar', count($events)));
			ajx_current("back", 2);
		} else {
			unset($_SESSION['calendar_export_filename']);
			unset($_SESSION['calendar_name']);
			return;
		}
	}
	
	function download_exported_file() {
		$filename = array_var($_SESSION, 'calendar_export_filename', '');
		$calendar_name = array_var($_SESSION, 'calendar_name', '');
		if ($filename != '') {
			$path = ROOT.'/tmp/'.$filename;
			$size = filesize($path);
			
			unset($_SESSION['calendar_export_filename']);
			download_file($path, 'text/ics', $calendar_name.'_events.ics', $size, false);
			unlink($path);
			die();
		} else $this->setTemplate('cal_export');
	}
	
	function generate_ical_export_url() {
		$ws = active_project();
		if ($ws == null) {
			$cal_name = logged_user()->getDisplayName();
			$ws_ids = 0;
		} else {
			$cal_name = Projects::findById($ws->getId())->getName();
			if (isset($_GET['inc_subws']) && $_GET['inc_subws'] == 'true') {
				$ws_ids = $ws->getAllSubWorkspacesCSV(true, logged_user());
				$ws_ids = str_replace(" ", "", $ws_ids);
			} else {
				$ws_ids = $ws->getId();
			}			
		}
		$token = logged_user()->getToken();
		$url = ROOT_URL . "/" . PUBLIC_FOLDER . "/tools/ical_export.php?cal=$ws_ids&n=$cal_name&t=$token";
		
		$obj = array("url" => $url);
		ajx_extra_data($obj);
		ajx_current("empty");		
	}
	
} // EventController

/***************************************************************************
 *           Parts of the code for this class were extracted from
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: EventController.class.php,v 1.85.2.2 2009/05/25 17:13:26 alvarotm01 Exp $
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/
/*
	Code is from:
	Copyright (c) Reece Pegues
	sitetheory.com

    Reece PHP Calendar is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or 
	any later version if you wish.

    You should have received a copy of the GNU General Public License
    along with this file; if not, write to the Free Software
    Foundation Inc, 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
	
*/
?>