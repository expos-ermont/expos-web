<?php
/*
	
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



/* ##################################################################
  cal_query_get_event()
   gets a particular event's information from it's id number.
###################################################################*/
//function cal_query_get_event($id){
//	return ProjectEvents::findById($id);
//	global $cal_db;
//	// set up variables
//	$uid = logged_user()->getId(); // $_SESSION['cal_userid'];
//	if(!is_numeric($uid)) $uid = 0;
//	$limitation = "";
//	// if can't view other's events, set this limitaiton in the query
//	if(!logged_user()->isAdministrator()){
//		if(active_project() instanceof Project ){ //list events for current project, except if perm=0
//			$limitation = " and  ( ( not exists  (SELECT permission FROM ".TABLE_PREFIX."object_permissions as p " .
//				" WHERE p.rel_object_manager='ProjectEvents' and p.rel_object_id = ".TABLE_PREFIX."project_events.id and p.user_id=$uid and permission=0)) ".
//				" AND  ( project_id=" . active_project()->getId() ." )) ";
//		}
//		else{ //list all events for the projects I have permissions on, except if perm=0
//			$projs=get_current_user()->getActiveProjects();
//			$proj_ids = "-1";
//			foreach ($projs as $p){
//				$proj_ids .= "," . $p->getId() ;	
//			}
//			$limitation = " and  (not exists (SELECT permission FROM ".TABLE_PREFIX."object_permissions as p " .
//					" WHERE p.rel_object_manager='ProjectEvents' and p.rel_object_id = ".TABLE_PREFIX."project_events.id and p.user_id=$uid and permission=0) " .
//					" AND ( project_id in (" .$proj_ids .") )) ";
//		}
//	}
//	else {
//		if(active_project() instanceof Project ){ //list events for current project, except if perm=0
//			$limitation = " AND  ( project_id=" . active_project()->getId() ." ) ";
//		}
//	}
//
//	// access database:
//	if(!is_numeric($id)) return NULL;
//	$q="SELECT UNIX_TIMESTAMP(`start`) AS start_since_epoch,
//		UNIX_TIMESTAMP(duration) AS end_since_epoch,  private, subject, created_by_id, updated_on,
//		description, eventtype, type_id, repeat_d, repeat_m, repeat_y, repeat_h, repeat_end, repeat_num, typecolor,
//		updated_by_id, UNIX_TIMESTAMP(updated_on) as modstamp , created_on  
//		FROM ".TABLE_PREFIX."project_events left outer join ".TABLE_PREFIX."eventtypes 
//		ON ".TABLE_PREFIX."project_events.type_id=".TABLE_PREFIX."eventtypes.id 
//		WHERE ".TABLE_PREFIX."project_events.id = '$id' 
//		$limitation
//		";
//	$result = $cal_db->sql_query($q);
//	if(!$result AND DEBUG){
//		echo CAL_QUERY_GETEVENT_ERROR;
//		echo "<br><br>";
//		echo $cal_db->sql_error();
//	}
//	return $result;
//}


/* ##################################################################
  cal_query_search()
   given certain parameters, you can search events
###################################################################*/
function cal_query_search($params){
	global $cal_db;
	// if not allowed to view events return null
	if(!cal_permission("read")) return NULL;
	// set up limitation variables
	$uid =  logged_user()->getId();
	if(!is_numeric($uid)) $uid = 0;
	$limitation = "";
	// if can't view other's events, set this limitaiton in the query
	if(!cal_permission("readothers")){
		$limitation .= " AND created_by_id = ".$uid." ";
	}
	// if can't view own events, set this limitation in the query
	if(!cal_permission("read")){
		$limitation .= " AND created_by_id != ".$uid." ";
	}
	// get search parameters from the query and escape the strings to avoid sql injection
	$sort = $cal_db->sql_escapestring($params['sort']);
	$order = $cal_db->sql_escapestring($params['order']);
	$fromdate = $cal_db->sql_escapestring($params['from']);
	$todate = $cal_db->sql_escapestring($params['to']);
	$ss = $cal_db->sql_escapestring($params['string']);
	// check permissions
	$uid =  logged_user()->getId();
	if(!is_numeric($uid)) $uid = 0;
	if(!cal_permission("readothers")){
		$limitation = " AND created_by_id = ".uid." ";
	}else $limitation = "";
	// write up the query
	$query = "
		SELECT UNIX_TIMESTAMP(stamp) AS start_since_epoch, ev.id, private, subject, typecolor, started_on,
		SUBSTRING(description,1,300) as descshort, LENGTH(description) as len, eventtype, ROUND(MATCH (subject,description) AGAINST ('$ss'), 1) AS score 
		FROM ".TABLE_PREFIX."project_events ev 
		LEFT JOIN ".TABLE_PREFIX."eventtypes et 
		ON ev.type_id=et.id 
		WHERE `start` >= '$fromdate' 
		AND `start` <= '$todate' 
		AND MATCH (subject,description) AGAINST ('$ss') > 0 
		$limitation 
		ORDER BY $sort $order LIMIT 200";
	// run the query and return result
	$result = $cal_db->sql_query($query);
	if(!$result AND DEBUG){
		echo $cal_db->sql_error();
	}
	// result is checked by search page, so just pass whatever back
	return $result;
}


/* ##################################################################
  cal_query_getuser()
   returns user info given a username
###################################################################*/
function cal_query_getuser($username = ""){
	global $cal_db;
	// protect agains sql injection
	$user = $cal_db->sql_escapestring($username);
	// build the query
	$q = "select * from ".TABLE_PREFIX."accounts where user='$user'";
	// get result 
	$r = $cal_db->sql_query($q);
	if(!$r AND DEBUG){
		echo $cal_db->sql_error();
	}
	return $r;
}



/* ##################################################################
  cal_query_getallusers()
   returns all user info
   used by the admin section to list all users out
###################################################################*/
function cal_query_getallusers(){
	global $cal_db;
	// build the query
	$q = "select * from ".TABLE_PREFIX."accounts";
	// get result 
	$r = $cal_db->sql_query($q);
	if(!$r AND DEBUG){
		echo $cal_db->sql_error();
	}
	return $r;
}


/* ##################################################################
  cal_query_getoptions()
   returns system options
###################################################################*/
function cal_query_getoptions(){
/*	global $cal_db;
	$q = "select opname,opvalue from ".TABLE_PREFIX."options";
	$r = $cal_db->sql_query($q);
	if(!$r AND DEBUG){
		echo $cal_db->sql_error();
	}
	return $r;*/
	return  null;
}


/* ##################################################################
  cal_query_permissions()
   returns a user's permission given an ID
###################################################################*/
function cal_query_permissions($id){
/*	global $cal_db;
	if(!is_numeric($id)) $id = 0;
	// make the query to get all the permissions
	$q = "select pname, pvalue from ".TABLE_PREFIX."permissions where created_by_id=$id";
	// call database
	$r = $cal_db->sql_query($q);
	if(!$r AND DEBUG){
		echo $cal_db->sql_error();
	}*/
	return false;
}

	
	

///* ##################################################################
//		CAN NOW BE FOUND IN PROJECT EVENTS
//  cal_query_get_eventlist()
//   Returns all events that should be listed for the given day/month/year combination.
//###################################################################*/
//function cal_query_get_eventlist($day,$month,$year,$tag){
//// checks OpenGoo permissions with $limitation VARIABLE
//// permissions given if object_permission.permission>0 for the event or the calendar
//	global $cal_db;
//	// make sure we have all the data we need
//	if(!is_numeric($day) OR !is_numeric($month) OR !is_numeric($year)){
//		return NULL;
//	}
//	// fix any date issues
//	$year = date("Y",mktime(0,0,1,$month, $day, $year));
//	$month = date("m",mktime(0,0,1,$month, $day, $year));
//	$day = date("d",mktime(0,0,1,$month, $day, $year));
//	// get special dates
//	// set up limitation variables
//	$uid =  logged_user()->getId();
//	if(!is_numeric($uid)) $uid = 0;
//	//permission check
//	$limitation='';
//	if(!logged_user()->isAdministrator()){
//		if(active_project() instanceof Project ){ //list events for current project, except if perm=0
////			$limitation = " and  ( ( not exists  (SELECT permission FROM ".TABLE_PREFIX."object_permissions as p " .
////				" WHERE p.rel_object_manager='ProjectEvents' and p.rel_object_id = ".TABLE_PREFIX."project_events.id and p.user_id=$uid and permission=0)) ".
//			$limitation =	" AND  ( project_id=" . active_project()->getId() . " ) "; /// missing 1 parenthesis if previous lines uncommented!!
//		}
//		else{ //list all events for the projects I have permissions on, except if perm=0
//			$projs=logged_user()->getActiveProjects();
//			$proj_ids = "-1";
//			foreach ($projs as $p){
//				$proj_ids .= "," . $p->getId() ;	
//			}
////			$limitation = " and  (not exists (SELECT permission FROM ".TABLE_PREFIX."object_permissions as p " .
////					" WHERE p.rel_object_manager='ProjectEvents' and p.rel_object_id = ".TABLE_PREFIX."project_events.id and p.user_id=$uid and permission=0) " .
//			$limitation = " AND ( project_id in (" .$proj_ids .") ) "; /// missing 1 parenthesis if previous lines uncommented!!
//		}
//	}
//	else {
//		if(active_project() instanceof Project ){
//			$limitation = "AND  ( project_id=" . active_project()->getId() ." )";
//		}
//		else
//			$limitation= " AND ( project_id in (". logged_user()->getActiveProjectIdsCSV() .") )";
//	}
//	//filter by tags
//	if(isset($tag) && $tag && $tag!='')
//    		$tag_str = " AND exists (SELECT * from " . TABLE_PREFIX . "tags t WHERE tag='".$tag."' AND  ".TABLE_PREFIX."project_events.id=t.rel_object_id AND t.rel_object_manager='ProjectEvents') ";
//	else
//		$tag_str='';
//	$extra = "";
//	// build the query
//	$q = "SELECT UNIX_TIMESTAMP(`start`) as start_since_epoch,
//		UNIX_TIMESTAMP(duration) as end_since_epoch, private, created_by_id, subject,
//		description, eventtype, ".TABLE_PREFIX."project_events.id, 
//		repeat_d, repeat_m, repeat_y, repeat_h, repeat_end, type_id, 
//		typename, typedesc, typecolor, repeat_num, special_id, created_on
//		FROM ".TABLE_PREFIX."project_events left outer join ".TABLE_PREFIX."eventtypes 
//		
//		ON ".TABLE_PREFIX."project_events.type_id=".TABLE_PREFIX."eventtypes.id 
//		WHERE
//		(
//			-- 
//			-- THIS RETURNS EVENTS ON THE ACTUAL DAY IT'S SET FOR (ONE TIME EVENTS)
//			-- 
//			(
//				duration >= '$year-$month-$day 00:00:00' 
//				AND `start` <= '$year-$month-$day 23:59:59' 
//			) 
//			-- 
//			-- THIS RETURNS REGULAR REPEATING EVENTS - DAILY, WEEKLY, MONTHLY, OR YEARLY.
//			-- 
//			OR 
//			(
//				DATE(`start`) <= '$year-$month-$day' 
//				AND
//				(
//					(
//						MOD( DATEDIFF(DATE(`start`), '$year-$month-$day') ,repeat_d) = 0
//						AND
//						(
//							ADDDATE(DATE(`start`), INTERVAL ((repeat_num-1)*repeat_d) DAY) >= '$year-$month-$day' 
//							OR
//							repeat_forever = 1
//							OR
//							repeat_end >= '$year-$month-$day'
//						)
//					)
//					OR
//					(
//						MOD( PERIOD_DIFF(DATE_FORMAT(`start`,'%Y%m'),DATE_FORMAT('$year-$month-$day','%Y%m')) ,repeat_m) = 0
//						AND 
//						DAY(`start`)= '$day'
//						AND
//						(
//							ADDDATE(DATE(`start`), INTERVAL ((repeat_num-1)*repeat_m) MONTH) >= '$year-$month-$day' 
//							OR
//							repeat_forever = 1
//							OR
//							repeat_end >= '$year-$month-$day'
//						)
//					)
//					OR
//					(
//						MOD( (YEAR(DATE(`start`))-YEAR('$year-$month-$day')) ,repeat_y) = 0
//						AND
//						MONTH(`start`)='$month' 
//						AND 
//						DAY(`start`) = '$day'
//						AND
//						(
//							ADDDATE(DATE(`start`), INTERVAL ((repeat_num-1)*repeat_y) YEAR) >= '$year-$month-$day' 
//							OR
//							repeat_forever = 1
//							OR
//							repeat_end >= '$year-$month-$day'
//						)
//					)
//				)		
//			)
//			-- 
//			-- THIS RETURNS EVENTS SET TO BE A CERTAIN DAY OF THE WEEK IN A CERTAIN WEEK OF THE MONTH NUMBERED 1-4
//			-- 
//			OR
//			(
//				repeat_h = 1
//				AND
//				MONTH(`start`) = $month 
//				AND 
//				(
//					(
//						DAYOFWEEK('$year-$month-01') <= DAYOFWEEK(`start`)
//						AND 
//						( DAYOFWEEK(`start`) - (DAYOFWEEK('$year-$month-01') - 1) + ( FLOOR((DAY(`start`)-1)/7) * 7) ) = $day
//					)
//					OR
//					(
//						DAYOFWEEK('$year-$month-01') > DAYOFWEEK(`start`)
//						AND 
//						( ( 7 - ( DAYOFWEEK('$year-$month-01') - 1 ) + DAYOFWEEK(`start`) ) + ( FLOOR((DAY(`start`)-1)/7) * 7 ) ) = $day
//					)
//				)			
//			)
//			-- 
//			-- THIS RETURNS EVENTS SET TO BE A CERTAIN DAY OF THE WEEK IN THE LAST WEEK OF THE MONTH.
//			-- 
//			OR
//			(
//				repeat_h = 2
//				AND
//				MONTH(`start`) = $month 
//				AND 
//				DAY('$year-$month-$day') > (DAY(LAST_DAY('$year-$month-$day')) - 7) 
//				AND 
//				DAYOFWEEK(`start`) = DAYOFWEEK('$year-$month-$day')
//			)
//			$extra
//		)
//		$limitation  $tag_str
//		ORDER BY `start`";
//	$result = $cal_db->sql_query($q);
//	if(!$result AND DEBUG){
//		echo $cal_db->sql_error();
//	}
//	return $result;
//}






/* ##################################################################
  cal_query_delete_type()
   used by the admin section
   delete's an event type 
###################################################################*/
function cal_query_delete_type($id){
	global $cal_db;
	if(!is_numeric($id)) return FALSE;
	// make the query to get all the permissions
	$q = "DELETE FROM ".TABLE_PREFIX."eventtypes where id=".$id;
	$r = $cal_db->sql_query($q);
	// call database
	if(!$r AND DEBUG){
		echo $cal_db->sql_error();
	}
	if($r) return TRUE;
	return FALSE;
}


/* ##################################################################
  cal_query_set_eventtype()
   used by the admin section
   update's or adds an event type
###################################################################*/
function cal_query_set_eventtype($data, $id = "bogus"){
	global $cal_db;
	// get the data
	$name = $cal_db->sql_escapestring($data['name']);
	$color = $cal_db->sql_escapestring($data['color']);
	$desc = $cal_db->sql_escapestring($data['desc']);
	// decide if we are updating or inserting
	if(is_numeric($id) AND $id>0) $q = "UPDATE ".TABLE_PREFIX."eventtypes  SET typename='$name', typecolor='$color', typedesc='$desc' WHERE id=".$id;
	// if ID was not passed into function, insert a new event type
	elseif($id=="bogus") $q = "INSERT INTO ".TABLE_PREFIX."eventtypes (typename, typecolor, typedesc) VALUES('$name', '$color', '$desc')";
	// if ID was passed in, but was not valid, just return error
	else return FALSE;
	// run the query
	$r = $cal_db->sql_query($q);
	if(!$r AND DEBUG){
		echo $cal_db->sql_error();
	}
	if($r) return TRUE;
	return FALSE;
}



/* ##################################################################
  cal_query_get_eventtypes()
   used by the admin section
   gets all event types
###################################################################*/
function cal_query_get_eventtypes(){
	global $cal_db;
	// make the query
	$q = "select * from ".TABLE_PREFIX."eventtypes";
	// run the query
	$r = $cal_db->sql_query($q);
	if(!$r AND DEBUG){
		echo $cal_db->sql_error();
	}
	return $r;
}


?>
