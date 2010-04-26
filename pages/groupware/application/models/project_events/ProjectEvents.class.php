<?php

/**
* ProjectEvents, generated on Tue, 04 Jul 2006 06:46:08 +0200 by 
* DataObject generation tool
*
* @author Marcos Saiz <marcos.saiz@gmail.com>
*/
class ProjectEvents extends BaseProjectEvents {
    
	const ORDER_BY_NAME = 'name';
	const ORDER_BY_POSTTIME = 'dateCreated';
	const ORDER_BY_MODIFYTIME = 'dateUpdated';
	
	/**
	 * Returns all events for the given date, tag and considers the active project
	 *
	 * @param DateTimeValue $date
	 * @param String $tags
	 * @return unknown
	 */
	static function getDayProjectEvents(DateTimeValue $date, $tags = '', $project = null, $user = -1, $inv_state = '-1'){
		$day = $date->getDay();
		$month = $date->getMonth();
		$year = $date->getYear();
		
		if(!is_numeric($day) OR !is_numeric($month) OR !is_numeric($year)){
			return NULL;
		}
		
		$tz_hm = "'". floor(logged_user()->getTimezone()).":".(abs(logged_user()->getTimezone()) % 1)*60 ."'";
		
		$date = new DateTimeValue($date->getTimestamp() - logged_user()->getTimezone() * 3600);
		$next_date = new DateTimeValue($date->getTimestamp() + 24*3600);
		
		$start_date_str = $date->format("Y-m-d H:i:s");
		$nextday_date_str = $next_date->format("Y-m-d H:i:s");
		
		// fix any date issues
		$year = date("Y",mktime(0,0,1,$month, $day, $year));
		$month = date("m",mktime(0,0,1,$month, $day, $year));
		$day = date("d",mktime(0,0,1,$month, $day, $year));
		//permission check
		$limitation='';

		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectEvents::instance(),ACCESS_LEVEL_READ, logged_user()) .')';

		if ($project instanceof Project ){
			$pids = $project->getAllSubWorkspacesCSV(true, logged_user());
		} else {
			$pids = logged_user()->getActiveProjectIdsCSV();
		}
		$limitation = " AND (`project_id` IN ($pids))";
		if (isset($tags) && $tags && $tags!='') {
	    		$tag_str = " AND exists (SELECT * from " . TABLE_PREFIX . "tags t WHERE tag=".DB::escape($tags)." AND  ".TABLE_PREFIX."project_events.id=t.rel_object_id AND t.rel_object_manager='ProjectEvents') ";
		} else {
			$tag_str= "";
		}

		$conditions = "	(
				-- 
				-- THIS RETURNS EVENTS ON THE ACTUAL DAY IT'S SET FOR (ONE TIME EVENTS)
				-- 
				(
					`duration` > `start` AND `start` >= '$start_date_str' AND `duration` <= '$nextday_date_str'
					OR 
					`type_id` = 2 AND `start` >= '$start_date_str' AND `start` < '$nextday_date_str'
				) 
				-- 
				-- THIS RETURNS REGULAR REPEATING EVENTS - DAILY, WEEKLY, MONTHLY, OR YEARLY.
				-- 
				OR 
				(
					DATE(`start`) <= '$start_date_str' 
					AND
					(
						(
							MOD( DATEDIFF(ADDDATE(`start`, INTERVAL ".logged_user()->getTimezone()." HOUR), '$year-$month-$day') ,repeat_d) = 0
							AND
							(
								ADDDATE(DATE(`start`), INTERVAL ((repeat_num-1)*repeat_d) DAY) >= '$start_date_str' 
								OR
								repeat_forever = 1
								OR
								repeat_end >= '$year-$month-$day'
							)
						)
						OR
						(
							MOD( PERIOD_DIFF(DATE_FORMAT(`start`,'%Y%m'),DATE_FORMAT('$start_date_str','%Y%m')) ,repeat_m) = 0
							AND 
							`start` <= '$start_date_str' AND DAY(`start`) = $day 
							AND
							(
								ADDDATE(DATE(`start`), INTERVAL ((repeat_num-1)*repeat_m) MONTH) >= '$start_date_str' 
								OR
								repeat_forever = 1
								OR
								repeat_end >= '$year-$month-$day'
							)
						)
						OR
						(
							MOD( (YEAR(DATE(`start`))-YEAR('$start_date_str')) ,repeat_y) = 0
							AND 
							`start` <= '$start_date_str' AND DAY(`start`) = $day AND MONTH(`start`) = $month 
							AND
							(
								ADDDATE(DATE(`start`), INTERVAL ((repeat_num-1)*repeat_y) YEAR) >= '$start_date_str' 
								OR
								repeat_forever = 1
								OR
								repeat_end >= '$year-$month-$day'
							)
						)
					)		
				)
				-- 
				-- THIS RETURNS EVENTS SET TO BE A CERTAIN DAY OF THE WEEK IN A CERTAIN WEEK OF THE MONTH NUMBERED 1-4
				-- 
				OR
				(
					repeat_h = 1
					AND
					`start` >= '$start_date_str' AND `start` < ADDDATE('$start_date_str', INTERVAL 1 MONTH)
					AND 
					(
						(
							DAYOFWEEK('$year-$month-01') <= DAYOFWEEK( ADDDATE(`start`, INTERVAL $tz_hm HOUR_MINUTE) )
							AND 
							( DAYOFWEEK( ADDDATE(`start`, INTERVAL $tz_hm HOUR_MINUTE) ) - (DAYOFWEEK('$year-$month-01') - 1) + ( FLOOR((DAY( ADDDATE(`start`, INTERVAL $tz_hm HOUR_MINUTE) )-1)/7) * 7) ) = $day
						)
						OR
						(
							DAYOFWEEK('$year-$month-01') > DAYOFWEEK( ADDDATE(`start`, INTERVAL $tz_hm HOUR_MINUTE) )
							AND 
							( ( 7 - ( DAYOFWEEK('$year-$month-01') - 1 ) + DAYOFWEEK( ADDDATE(`start`, INTERVAL $tz_hm HOUR_MINUTE) ) ) + ( FLOOR((DAY( ADDDATE(`start`, INTERVAL $tz_hm HOUR_MINUTE) )-1)/7) * 7 ) ) = $day
						)
					)			
				)
				-- 
				-- THIS RETURNS EVENTS SET TO BE A CERTAIN DAY OF THE WEEK IN THE LAST WEEK OF THE MONTH.
				-- 
				OR
				(
					repeat_h = 2
					AND
					`start` >= '$start_date_str' AND `start` < ADDDATE('$start_date_str', INTERVAL 1 MONTH)
					AND 
					DAY('$year-$month-$day') > (DAY(LAST_DAY('$year-$month-$day')) - 7) 
					AND 
					DAYOFWEEK( ADDDATE(`start`, INTERVAL $tz_hm HOUR_MINUTE) ) = DAYOFWEEK('$year-$month-$day')
				)
			)
			$limitation  
			$permissions 
			$tag_str ";
		
		
		$result_events = self::findAll(array(
			'conditions' => $conditions,
			'order' => '`start`',
		));
		
		// Find invitations for events and logged user
		if (is_array($result_events) && count($result_events)) {
			ProjectEvents::addInvitations($result_events, $user);
			if (!($user == null && $inv_state == null)) {
				foreach ($result_events as $k => $event) {
					$conditions = '`event_id` = ' . $event->getId();
					if ($user != -1) $conditions .= ' AND `user_id` = ' . $user;
					$inv = EventInvitations::findAll(array ('conditions' => $conditions));
					if (!is_array($inv)) {
						if ($inv == null || (trim($inv_state) != '-1' && !strstr($inv_state, ''.$inv->getInvitationState()))) {
							unset($result_events[$k]);
						}
					} else {
						if (count($inv) > 0){
							foreach ($inv as $key => $v) {
								if ($v == null || (trim($inv_state) != '-1' && !strstr($inv_state, ''.$v->getInvitationState()))) {
									unset($result_events[$k]);
									break;
								}	
							}
						} else unset($result_events[$k]);
					}
				}
			}
		}
		
		return $result_events;
	}
	
	
	
	/**
	 * Returns all events for the given range, tag and considers the active project
	 *
	 * @param DateTimeValue $date
	 * @param String $tags
	 * @return unknown
	 */
	static function getRangeProjectEvents(DateTimeValue $start_date, DateTimeValue $end_date,  $tags = '', $project = null){
		
		$start_year = date("Y",mktime(0,0,1,$start_date->getMonth(), $start_date->getDay(), $start_date->getYear()));
		$start_month = date("m",mktime(0,0,1,$start_date->getMonth(), $start_date->getDay(), $start_date->getYear()));
		$start_day = date("d",mktime(0,0,1,$start_date->getMonth(), $start_date->getDay(), $start_date->getYear()));
		
		$end_year = date("Y",mktime(0,0,1,$end_date->getMonth(), $end_date->getDay(), $end_date->getYear()));
		$end_month = date("m",mktime(0,0,1,$end_date->getMonth(), $end_date->getDay(), $end_date->getYear()));
		$end_day = date("d",mktime(0,0,1,$end_date->getMonth(), $end_date->getDay(), $end_date->getYear()));
		
		if(!is_numeric($start_day) OR !is_numeric($start_month) OR !is_numeric($start_year) OR !is_numeric($end_day) OR !is_numeric($end_month) OR !is_numeric($end_year)){
			return NULL;
		}
		
		//permission check
		$limitation='';

		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectEvents::instance(),ACCESS_LEVEL_READ, logged_user()) .')';

		if ($project instanceof Project ){
			$pids = $project->getAllSubWorkspacesCSV(true, logged_user());
		} else {
			$pids = logged_user()->getActiveProjectIdsCSV();
		}
		$limitation = " AND (`project_id` IN ($pids))";
		if (isset($tags) && $tags && $tags!='') {
	    		$tag_str = " AND exists (SELECT * from " . TABLE_PREFIX . "tags t WHERE tag='".$tags."' AND  ".TABLE_PREFIX."project_events.id=t.rel_object_id AND t.rel_object_manager='ProjectEvents') ";
		} else {
			$tag_str= "";
		}
		
		$tz_hm = "'". floor(logged_user()->getTimezone()).":".(abs(logged_user()->getTimezone()) % 1)*60 ."'";
		
		$s_date = new DateTimeValue($start_date->getTimestamp() - logged_user()->getTimezone() * 3600);
		$e_date = new DateTimeValue($end_date->getTimestamp() - logged_user()->getTimezone() * 3600);
		$e_date->add("d", 1);
		
		$start_date_str = $s_date->format("Y-m-d H:i:s");
		$end_date_str = $e_date->format("Y-m-d H:i:s");
		
		$conditions = "	(
				-- 
				-- THIS RETURNS EVENTS ON THE ACTUAL DAY IT'S SET FOR (ONE TIME EVENTS)
				-- 
				(
					duration >= '$start_date_str' 
					AND `start` < '$end_date_str' 
				) 
				-- 
				-- THIS RETURNS REGULAR REPEATING EVENTS - DAILY, WEEKLY, MONTHLY, OR YEARLY.
				-- 
				OR 
				(
					DATE(`start`) < '$end_date_str'
					AND
					(							
						(
							ADDDATE(DATE(`start`), INTERVAL ((repeat_num-1)*repeat_d) DAY) >= '$start_date_str' 
							OR
							repeat_forever = 1
							OR
							repeat_end >= '$year-$month-$day'
						)
						OR
						(
							ADDDATE(DATE(`start`), INTERVAL ((repeat_num-1)*repeat_m) MONTH) >= '$start_date_str' 
							OR
							repeat_forever = 1
							OR
							repeat_end >= '$year-$month-$day'
						)
						OR
						(
							ADDDATE(DATE(`start`), INTERVAL ((repeat_num-1)*repeat_y) YEAR) >= '$start_date_str' 
							OR
							repeat_forever = 1
							OR
							repeat_end >= '$year-$month-$day'
						)
					)		
				)
				-- 
				-- THIS RETURNS EVENTS SET TO BE A CERTAIN DAY OF THE WEEK IN A CERTAIN WEEK OF THE MONTH NUMBERED 1-4
				-- 
				OR
				(
					repeat_h = 1
					AND
					`start` >= '$start_date_str' AND `start` < ADDDATE('$start_date_str', INTERVAL 1 MONTH)
					AND 
					(
						(
							DAYOFWEEK('$start_year-$start_month-01') <= DAYOFWEEK( ADDDATE(`start`, INTERVAL $tz_hm HOUR_MINUTE) )
							AND 
							( DAYOFWEEK( ADDDATE(`start`, INTERVAL $tz_hm HOUR_MINUTE) ) - (DAYOFWEEK('$start_year-$start_month-01') - 1) + ( FLOOR((DAY( ADDDATE(`start`, INTERVAL $tz_hm HOUR_MINUTE) )-1)/7) * 7) ) = $start_day
						)
						OR
						(
							DAYOFWEEK('$start_year-$start_month-01') > DAYOFWEEK( ADDDATE(`start`, INTERVAL $tz_hm HOUR_MINUTE) )
							AND 
							( ( 7 - ( DAYOFWEEK('$start_year-$start_month-01') - 1 ) + DAYOFWEEK( ADDDATE(`start`, INTERVAL $tz_hm HOUR_MINUTE) ) ) + ( FLOOR((DAY( ADDDATE(`start`, INTERVAL $tz_hm HOUR_MINUTE) )-1)/7) * 7 ) ) = $start_day
						)
					)			
				)
				-- 
				-- THIS RETURNS EVENTS SET TO BE A CERTAIN DAY OF THE WEEK IN THE LAST WEEK OF THE MONTH.
				-- 
				OR
				(
					repeat_h = 2
					AND
					MONTH(`start`) = $start_month 
					AND 
					DAY('$start_year-$start_month-$start_day') > (DAY(LAST_DAY('$start_year-$start_month-$start_day')) - 7) 
					AND 
					DAYOFWEEK( ADDDATE(`start`, INTERVAL $tz_hm HOUR_MINUTE) ) = DAYOFWEEK('$start_year-$start_month-$start_day')
				)				
			
			$limitation  
			$permissions 
			$tag_str )";
		
		$result_events = self::findAll(array(
			'conditions' => $conditions,
			'order' => '`start`',
		));
		
		// Find invitations for events and logged user
		ProjectEvents::addInvitations($result_events);	
		
		return $result_events;		
	}
	
	static function addInvitations($result_events, $user_id = -1) {
		if ($user_id == -1) $user_id = logged_user()->getId();
		if (isset($result_events) && is_array($result_events) && count($result_events)) {
			foreach ($result_events as $event) {
				$inv = EventInvitations::findById(array('event_id' => $event->getId(), 'user_id' => $user_id));
				if ($inv != null) {
					$event->addInvitation($inv);
				}
			}
		}
	}
	
	/**
	* Reaturn all calendar Events
	*
	* @param Project $project
	* @return array
	*/
	static function getAllEventsByProject($project = null) {
		if ($project instanceof Project) {
			$pids = $project->getAllSubWorkspacesCSV(true, logged_user());
		} else {
			$pids = logged_user()->getActiveProjectIdsCSV();
		}
		$cond_str = "`project_id` IN ($pids)";
		$result_events = self::findAll(array(
			'conditions' => array($cond_str)
		)); // findAll
		
		// Find invitations for events and logged user
		ProjectEvents::addInvitations($result_events);
		
		return $result_events;
	} // getAllEventsByProject
	
	  
} // ProjectEvents 

?>