<?php

  /**
  * group class
  *
  * @author Marcos Saiz <marcos.saiz@gmail.com>
  */


  class Group extends BaseGroup {

    const CONST_MINIMUM_GROUP_ID = 10000000;  
    const CONST_ADMIN_GROUP_ID = 10000000; 
	
    /**
     * REturn true if the actual group is the Administrators group (which cannot be deleted)
     *
     * @return boolean
     */
    function isAdministratorGroup(){
    	return Group::CONST_ADMIN_GROUP_ID == $this->getId();
    } //isAdministratorGroup
    /**
    * Return array of all group members
    *
    * @access public
    * @param void
    * @return array
    */
    function getUsers($group_id) {
      return GroupUsers::getUsersByGroup($group_id); 
//      findAll(array(
//        'conditions' => '`group_id` = ' . DB::escape($this->getId())
//      )); // findAll
    } // getUsers
    
    /**
    * Return number of group users
    *
    * @access public
    * @param void
    * @return integer
    */
    function countUsers() {
      return GroupUsers::count('`group_id` = ' . DB::escape($this->getId()));
    } // countUsers
    
    function setAllPermissions($value) {
    	$this->setCanEditCompanyData($value);
    	$this->setCanManageConfiguration($value);
    	$this->setCanManageSecurity($value);
    	$this->setCanManageWorkspaces($value);
    	$this->setCanManageContacts($value);
    	$this->setCanManageTemplates($value);
    	$this->setCanManageReports($value);
    }
//    /**
//    * Return array of group users on specific project
//    *
//    * @access public
//    * @param Project $project
//    * @return array
//    */
//    function getUsersOnProject(Project $project) {
//      return ProjectUsers::getgroupUsersByProject($this, $project);
//    } // getUsersOnProject
//    
//    /**
//    * Return users that have auto assign value set to true
//    *
//    * @access public
//    * @param void
//    * @return array
//    */
//    function getAutoAssignUsers() {
//      return Users::findAll(array(
//        'conditions' => '`group_id` = ' . DB::escape($this->getId()) . ' AND `auto_assign` > ' . DB::escape(0)
//      )); // findAll
//    } // getAutoAssignUsers
//    
//    /**
//    * Return all client groups
//    *
//    * @access public
//    * @param void
//    * @return array
//    */
//    function getClientgroups() {
//      return groups::getgroupClients($this);
//    } // getClientgroups
//    
//    /**
//    * Return number of client groups
//    *
//    * @access public
//    * @param void
//    * @return integer
//    */
//    function countClientgroups() {
//      return groups::count('`client_of_id` = ' . DB::escape($this->getId()));
//    } // countClientgroups
//    
//    /**
//    * Return all projects that this group is member of
//    *
//    * @access public
//    * @param void
//    * @return array
//    */
//    function getProjects() {
//      return $this->isOwner() ? Projects::getAll() : Projectgroups::getProjectsBygroup($this);
//    } // getProjects
//    
//    /**
//    * Return total number of projects
//    *
//    * @access public
//    * @param void
//    * @return integer
//    */
//    function countProjects() {
//      if($this->isOwner()) {
//        return Projects::count(); // all
//      } else {
//        return Projectgroups::count('`group_id` = ' . DB::escape($this->getId()));
//      } // if
//    } // countProjects
//    
//    /**
//    * Return active projects that are owned by this group
//    *
//    * @param void
//    * @return null
//    */
//    function getActiveProjects() {
//      if(is_null($this->active_projects)) {
//        if($this->isOwner()) {
//          $this->active_projects = Projects::findAll(array(
//            'conditions' => '`completed_on` = ' . DB::escape(EMPTY_DATETIME)
//          )); // findAll
//        } else {
//          $this->active_projects = Projectgroups::getProjectsBygroup($this, '`completed_on` = ' . DB::escape(EMPTY_DATETIME));
//        } // if
//      } // if
//      return $this->active_projects;
//    } // getActiveProjects
//    
//    /**
//    * Return all completed projects
//    *
//    * @param void
//    * @return null
//    */
//    function getCompletedProjects() {
//      if(is_null($this->completed_projects)) {
//        if($this->isOwner()) {
//          $this->completed_projects = Projects::findAll(array(
//            'conditions' => '`completed_on` > ' . DB::escape(EMPTY_DATETIME)
//          )); // findAll
//        } else {
//          $this->completed_projects = Projectgroups::getProjectsBygroup($this, '`completed_on` > ' . DB::escape(EMPTY_DATETIME));
//        } // if
//      } // if
//      return $this->completed_projects;
//    } // getCompletedProjects
//    
//    /**
//    * Return all milestones scheduled for today
//    *
//    * @param void
//    * @return array
//    */
//    function getTodayMilestones() {
//      return ProjectMilestones::getTodayMilestonesBygroup($this);
//    } // getTodayMilestones
//    
//    /**
//    * Return all late milestones
//    *
//    * @param void
//    * @return array
//    */
//    function getLateMilestones() {
//      return ProjectMilestones::getLateMilestonesBygroup($this);
//    } // getLateMilestones
//    
//    /**
//    * Check if this group is owner group
//    *
//    * @param void
//    * @return boolean
//    */
//    function isOwner() {
//      if($this->isNew()) {
//        return false;
//      } else {
//        return $this->getClientOfId() == 0;
//      } // if
//    } // isOwner
//    
//    /**
//    * Check if this group is part of specific project
//    *
//    * @param Project $project
//    * @return boolean
//    */
//    function isProjectgroup(Project $project) {
//      if($this->isOwner() && ($project->getgroupId() == $this->getId())) {
//        return true;
//      } // uf
//      return Projectgroups::findById(array('project_id' => $project->getId(), 'group_id' => $this->getId())) instanceof Projectgroup;
//    } // isProjectgroup
//    
//    /**
//    * This function will return true if we have data to show group address (address, city, country and zipcode)
//    *
//    * @access public
//    * @param void
//    * @return boolean
//    */
//    function hasAddress() {
//      return trim($this->getAddress()) <> '' &&
//             trim($this->getCity()) <> '' &&
//             //trim($this->getZipcode()) <> '' &&
//             trim($this->getCountry()) <> '';
//    } // hasAddress
//    
//    /**
//    * Check if this group have valid homepage address set
//    *
//    * @access public
//    * @param void
//    * @return boolean
//    */
//    function hasHomepage() {
//      return trim($this->getHomepage()) <> '' && is_valid_url($this->getHomepage());
//    } // hasHomepage
//    
//    /**
//    * Return name of country
//    *
//    * @access public
//    * @param void
//    * @return string
//    */
//    function getCountryName() {
//      return lang('country ' . $this->getCountry());
//    } // getCountryName
//    
//    /**
//    * Returns true if group info is updated by the user since group is created. group can be created
//    * with empty group info
//    *
//    * @access public
//    * @param void
//    * @return boolean
//    */
//    function isInfoUpdated() {
//      return $this->getCreatedOn()->getTimestamp() < $this->getUpdatedOn()->getTimestamp();
//    } // isInfoUpdated
//    
//    /**
//    * Set homepage URL
//    * 
//    * This function is simple setter but it will check if protocol is specified for given URL. If it is not than 
//    * http will be used. Supported protocols are http and https for this type or URL
//    *
//    * @param string $value
//    * @return null
//    */
//    function setHomepage($value) {
//      if(trim($value) == '') {
//        return parent::setHomepage('');
//      } // if
//      
//      $check_value = strtolower($value);
//      if(!str_starts_with($check_value, 'http://') && !str_starts_with($check_value, 'https://')) {
//        return parent::setHomepage('http://' . $value);
//      } else {
//        return parent::setHomepage($value);
//      } // if
//    } // setHomepage
//    
//    // ---------------------------------------------------
//    //  Permissions
//    // ---------------------------------------------------
//    
    /**
    * Check if specific user can update this group
    *
    * @access public
    * @param User $user
    * @return boolean
    */
    function canEdit(User $user) {
      return $user->isAccountOwner() || $user->isAdministrator() || $user->isMemberOf(owner_company());
    } // canEdit
    
    /**
    * Check if specific user can delete this group
    *
    * @access public
    * @param User $user
    * @return boolean
    */
    function canDelete(User $user) {
      return ($user->isAccountOwner() || $user->isAdministrator()) && !$this->isAdministratorGroup() ;
    } // canDelete
    
    /**
    * Returns true if specific user can add group
    *
    * @access public
    * @param User $user
    * @return boolean
    */
    function canAdd(User $user, Company $company) {
      return ($user->isAdministrator() || $user->isMemberOf($company));
      	
      //return $user->isAccountOwner() || $user->isAdministrator($this);
    } // canAddClient
    
    /**
    * Check if this user can add new account to this group
    *
    * @access public
    * @param User $user
    * @return boolean
    */
    function canAddUser(User $user) {
      return User::canEdit($user);
    } // canAddUser
//    
//    /**
//    * Check if user can update permissions of this group
//    *
//    * @param User $user
//    * @return boolean
//    */
//    function canUpdatePermissions(User $user) {
//      if($this->isOwner()) {
//        return false; // owner group!
//      } // if
//      return $user->isAdministrator();
//    } // canUpdatePermissions
//    
//    // ---------------------------------------------------
//    //  URLs
//    // ---------------------------------------------------
//    
//    /**
//    * Show group card page
//    *
//    * @access public
//    * @param void
//    * @return null
//    */
//    function getCardUrl() {
//      return get_url('group', 'card', $this->getId());
//    } // getCardUrl
//    
    /**
    * Return view group URL
    *
    * @access public
    * @param void
    * @return string
    */
    function getViewUrl() {
        return get_url('group', 'view_group', array( 'id' => $this->getId()));
    } // getViewUrl
    
    /**
    * Edit group url
    *
    * @access public
    * @param void
    * @return null
    */
    function getEditUrl() {
      return get_url('group', 'edit_group', array('id' => $this->getId()));
    } // getEditUrl
    
    /**
    * Return delete group URL
    *
    * @access public
    * @param void
    * @return string
    */
    function getDeleteGroupUrl() {
      return get_url('group', 'delete', array('id' => $this->getId()));
    } // getDeleteClientUrl
//    
//    /**
//    * Return update permissions URL
//    *
//    * @param void
//    * @return string
//    */
//    function getUpdatePermissionsUrl() {
//      return get_url('group', 'update_permissions', $this->getId());
//    } // getUpdatePermissionsUrl
//    
    /**
    * Return add user URL
    *
    * @access public
    * @param void
    * @return string
    */
    function getAddUserUrl() {
      return get_url('group', 'add_user', array('id' => $this->getId()));
    } // getAddUserUrl
//    
//    /**
//    * Return update avatar URL
//    *
//    * @access public
//    * @param void
//    * @return string
//    */
//    function getEditLogoUrl() {
//      return get_url('group', 'edit_logo', $this->getId());
//    } // getEditLogoUrl
//    
//    /**
//    * Return delete logo URL
//    *
//    * @access public
//    * @param void
//    * @return string
//    */
//    function getDeleteLogoUrl() {
//      return get_url('group', 'delete_logo', $this->getId());
//    } // getDeleteLogoUrl
//    
//    // ---------------------------------------------------
//    //  Logo
//    // ---------------------------------------------------
//    
//    /**
//    * Set logo value
//    *
//    * @param string $source Source file
//    * @param integer $max_width
//    * @param integer $max_height
//    * @param boolean $save Save object when done
//    * @return null
//    */
//    function setLogo($source, $max_width = 50, $max_height = 50, $save = true) {
//      if(!is_readable($source)) return false;
//      
//      do {
//        $temp_file = ROOT . '/cache/' . sha1(uniqid(rand(), true));
//      } while(is_file($temp_file));
//      
//      try {
//        Env::useLibrary('simplegd');
//        
//        $image = new SimpleGdImage($source);
//        $thumb = $image->scale($max_width, $max_height, SimpleGdImage::BOUNDARY_DECREASE_ONLY, false);
//        $thumb->saveAs($temp_file, IMAGETYPE_PNG);
//        
//        $public_filename = PublicFiles::addFile($temp_file, 'png');
//        if($public_filename) {
//          $this->setLogoFile($public_filename);
//          if($save) {
//            $this->save();
//          } // if
//        } // if
//        
//        $result = true;
//      } catch(Exception $e) {
//        $result = false;
//      } // try
//      
//      // Cleanup
//      if(!$result && $public_filename) {
//        PublicFiles::deleteFile($public_filename);
//      } // if
//      @unlink($temp_file);
//      
//      return $result;
//    } // setLogo
//    
//    /**
//    * Delete logo
//    *
//    * @param void
//    * @return null
//    */
//    function deleteLogo() {
//      if($this->hasLogo()) {
//        PublicFiles::deleteFile($this->getLogoFile());
//        $this->setLogoFile('');
//      } // if
//    } // deleteLogo
//    
//    /**
//    * Returns path of group logo. This function will not check if file really exists
//    *
//    * @access public
//    * @param void
//    * @return string
//    */
//    function getLogoPath() {
//      return PublicFiles::getFilePath($this->getLogoFile());
//    } // getLogoPath
//    
//    /**
//    * description
//    *
//    * @access public
//    * @param void
//    * @return string
//    */
//    function getLogoUrl() {
//      return $this->hasLogo() ? PublicFiles::getFileUrl($this->getLogoFile()) : get_image_url('logo.gif');
//    } // getLogoUrl
//    
//    /**
//    * Returns true if this group have logo file value and logo file exists
//    *
//    * @access public
//    * @param void
//    * @return boolean
//    */
//    function hasLogo() {
//      return trim($this->getLogoFile()) && is_file($this->getLogoPath());
//    } // hasLogo
//    
//    // ---------------------------------------------------
//    //  System functions
//    // ---------------------------------------------------
//  
//    /**
//    * Validate this object before save
//    *
//    * @param array $errors
//    * @return boolean
//    */
//    function validate(&$errors) {
//      if(!$this->validatePresenceOf('name')) {
//        $errors[] = lang('group name required');
//      } // if
//      
//      if($this->validatePresenceOf('email')) {
//        if(!is_valid_email($this->getEmail())) {
//          $errors[] = lang('invalid email address');
//        } // if
//      } // if
//      
//      if($this->validatePresenceOf('homepage')) {
//        if(!is_valid_url($this->getHomepage())) {
//          $errors[] = lang('group homepage invalid');
//        } // if
//      } // if
//    } // validate
    
    /**
    * Delete this group and all related data
    *
    * @access public
    * @param void
    * @return boolean
    * @throws Error
    */
    function delete() {
      if ($this->isAdministratorGroup()  ) {
        throw new Error(lang('error delete group'));
        return false;
      } // if
      
      return parent::delete();
    } // delete
    
//    // ---------------------------------------------------
//    //  ApplicationDataObject implementation
//    // ---------------------------------------------------
//    
//    /**
//    * Return object URl
//    *
//    * @access public
//    * @param void
//    * @return string
//    */
//    function getObjectUrl() {
//      return logged_user()->isAdministrator() ? $this->getViewUrl() : $this->getCardUrl();
//    } // getObjectUrl
//    
//    /**
//    * Return object type name
//    *
//    * @param void
//    * @return string
//    */
//    function getObjectTypeName() {
//      return 'group';
//    } // getObjectTypeName
    
  } // group 

?>