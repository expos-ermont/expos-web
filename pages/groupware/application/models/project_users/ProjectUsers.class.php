<?php

  /**
  * ProjectUsers, generated on Wed, 15 Mar 2006 22:57:46 +0100 by 
  * DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class ProjectUsers extends BaseProjectUsers {
    
    /** All available user permissions **/
    const CAN_READ_MESSAGES   = 'can_read_messages';
    const CAN_READ_TASKS      = 'can_read_tasks';
    const CAN_READ_MILESTONES = 'can_read_milestones';
    const CAN_READ_MAILS      = 'can_read_mails';
    const CAN_READ_COMMENTS      = 'can_read_comments';
    const CAN_READ_CONTACTS    = 'can_read_contacts';
    const CAN_READ_WEBLINKS    = 'can_read_weblinks';
    const CAN_READ_FILES      = 'can_read_files';
    const CAN_READ_EVENTS     = 'can_read_events';
    const CAN_WRITE_MESSAGES   = 'can_write_messages';
    const CAN_WRITE_TASKS      = 'can_write_tasks';
    const CAN_WRITE_MILESTONES = 'can_write_milestones';
    const CAN_WRITE_MAILS      = 'can_write_mails';
    const CAN_WRITE_COMMENTS      = 'can_write_comments';
    const CAN_WRITE_CONTACTS    = 'can_write_contacts';
    const CAN_WRITE_WEBLINKS    = 'can_write_weblinks';
    const CAN_WRITE_FILES      = 'can_write_files';
    const CAN_WRITE_EVENTS     = 'can_write_events';
    const CAN_ASSIGN_TO_OWNERS  = 'can_assign_to_owners';
    const CAN_ASSIGN_TO_OTHER   = 'can_assign_to_other';
  
    /**
    * Return all users that are involved in specific project
    *
    * @access public
    * @param Project $project
    * @param string $additional_conditions
    * @return array
    */
    function getUsersByProject(Project $project, $additional_conditions = null) {
      $users_table = Users::instance()->getTableName(true);
      $project_users_table=  ProjectUsers::instance()->getTableName(true);
      
      $users = array();
      
      $sql = "SELECT $users_table.* FROM $users_table, $project_users_table WHERE ($users_table.`id` = $project_users_table.`user_id` AND $project_users_table.`project_id` = " . DB::escape($project->getId()) . ')';
      if(trim($additional_conditions) <> '') 
      	$sql .= " AND ($additional_conditions) ";
      $sql .= " ORDER BY $users_table.`display_name`";
      
      $rows = DB::executeAll($sql);
      if(is_array($rows)) {
        foreach($rows as $row) {
          $users[] = Users::instance()->loadFromRow($row);
        } // foreach
      } // if
      
      return count($users) ? $users : null;
    } // getUsersByProject
    
    /**
    * Return users of specific company involeved in specific project
    *
    * @access public
    * @param Company $company
    * @param Project $project
    * @return array
    */
    function getCompanyUsersByProject(Company $company, Project $project) {
      $users_table = Users::instance()->getTableName(true);
      return self::getUsersByProject($project, "$users_table.`company_id` = " . DB::escape($company->getId()));
    } // getCompanyUsersByProject
    
    /**
    * Return all projects that this user is part of
    *
    * @access public
    * @param User $user
    * @param 
    * @return array
    */
    function getProjectsByUser(User $user, $additional_conditions = null) {
      $projects_table = Projects::instance()->getTableName(true);
      $project_users_table=  ProjectUsers::instance()->getTableName(true);
      
      $projects = array();
      
      $sql = "SELECT $projects_table.* FROM $projects_table, $project_users_table WHERE ($projects_table.`id` = $project_users_table.`project_id` AND $project_users_table.`user_id` = " . DB::escape($user->getId()) . ')';
      if(trim($additional_conditions) <> '') {
        $sql .= " AND ($additional_conditions)";
      } // if
      $sql .= " ORDER BY $projects_table.`name`";
      
      $rows = DB::executeAll($sql);
      if(is_array($rows)) {
        foreach($rows as $row) {
          $projects[] = Projects::instance()->loadFromRow($row);
        } // foreach
      } // if
      
      return count($projects) ? $projects : null;
    } // getProjectsByUser 
    
    /**
    * Return all users associated with specific project
    *
    * @access public
    * @param Project $project
    * @return boolean
    */
    static function clearByProject(Project $project) {
      return self::delete(array('`project_id` = ?', $project->getId()));
    } // clearByProject
    
    /**
    * Clear permission by user
    *
    * @param User $user
    * @return boolean
    */
    static function clearByUser(User $user, $ids = null) {
    	$ids_condition = "";
    	if (!is_null($ids) && $ids != '')
    		$ids_condition = " and `project_id` in (" . $ids . ")";
      	return self::delete(array('`user_id` = ?' . $ids_condition, $user->getId()));
    } // clearByUser
    
    /**
    * This function will return array of permission columns in table. Permission column name is 
    * used as permission ID in rest of the script
    *
    * @access public
    * @param void
    * @return array
    */
    function getPermissionColumns() {
      return array(			  
			self::CAN_READ_MESSAGES,
			self::CAN_READ_TASKS    ,
			self::CAN_READ_MILESTONES,
			self::CAN_READ_MAILS     ,
			self::CAN_READ_COMMENTS  ,
			self::CAN_READ_CONTACTS  ,
			self::CAN_READ_WEBLINKS  ,
			self::CAN_READ_FILES     ,
			self::CAN_READ_EVENTS    ,
			self::CAN_WRITE_MESSAGES ,
			self::CAN_WRITE_TASKS    ,
			self::CAN_WRITE_MILESTONES,
			self::CAN_WRITE_MAILS      ,
			self::CAN_WRITE_COMMENTS   ,
			self::CAN_WRITE_CONTACTS   ,
			self::CAN_WRITE_WEBLINKS   ,
			self::CAN_WRITE_FILES      ,
			self::CAN_WRITE_EVENTS     ,
			self::CAN_ASSIGN_TO_OWNERS ,
			self::CAN_ASSIGN_TO_OTHER  ,
      ); // array
    } // getPermissionColumns
    
    /**
    * Return permission name => permission text array
    *
    * @param void
    * @return array
    */
    static function getNameTextArray() {
      return array(			  
			self::CAN_READ_MESSAGES=> lang('can read messages'),
			self::CAN_WRITE_MESSAGES => lang('can write messages'),
			self::CAN_READ_TASKS    => lang('can read tasks'),
			self::CAN_WRITE_TASKS    => lang('can write tasks'),
			self::CAN_READ_MILESTONES=> lang('can read milestones'),
			self::CAN_WRITE_MILESTONES=> lang('can write milestones'),
			self::CAN_READ_MAILS     => lang('can read mails'),
			self::CAN_WRITE_MAILS      => lang('can write mails'),
			self::CAN_READ_COMMENTS  => lang('can read comments'),
			self::CAN_WRITE_COMMENTS   => lang('can write comments'),
			self::CAN_READ_CONTACTS  => lang('can read contacts'),
			self::CAN_WRITE_CONTACTS   => lang('can write contacts'),
			self::CAN_READ_WEBLINKS  => lang('can read weblinks'),
			self::CAN_WRITE_WEBLINKS   => lang('can write weblinks'),
			self::CAN_READ_FILES     => lang('can read files'),
			self::CAN_WRITE_FILES      => lang('can write files'),
			self::CAN_READ_EVENTS    => lang('can read events'),
			self::CAN_WRITE_EVENTS     => lang('can write events'),
			self::CAN_ASSIGN_TO_OWNERS => lang('can assign to owners'),
			self::CAN_ASSIGN_TO_OTHER  => lang('can assign to other'),
      ); // array
    } // getNameTextArray
    
    function getByUserAndProject($project, $user) {
    	return ProjectUsers::findOne(array('conditions' => array('`user_id` = ? AND `project_id` = ? ',  $user->getId() , $project->getId())));
    }
    
  } // ProjectUsers 

?>