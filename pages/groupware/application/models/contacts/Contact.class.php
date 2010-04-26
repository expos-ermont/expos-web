<?php

/**
 * Contact class
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
class Contact extends BaseContact {
	
	/**
	 * Contacts are searchable
	 *
	 * @var boolean
	 */
	protected $is_searchable = true;

	/**
	 * Array of searchable columns
	 *
	 * @var array
	 */
	protected $searchable_columns = array('firstname', 'lastname', 'email', 'email2', 'email3');
	
	/**
    * This project object is taggable
    *
    * @var boolean
    */
    protected $is_taggable = true;
    /**
    * Returns true if this project is taggable
    *
    * @param void
    * @return boolean
    */
    function isTaggable() {
      return $this->is_taggable;
    } // isTaggable
    
	private $user;
	
	private $company;
    
    /**
    * Construct contact object
    *
    * @param void
    * @return User
    */
    function __construct() {
      parent::__construct();
    } // __construct
    
    /**
    * Check if this contact is member of specific company
    *
    * @access public
    * @param Company $company
    * @return boolean
    */
    function isMemberOf(Company $company) {
      return $this->getCompanyId() == $company->getId();
    } // isMemberOf
    
    // ---------------------------------------------------
    //  IMs
    // ---------------------------------------------------
    
    /**
    * Return true if this contact have at least one IM address
    *
    * @access public
    * @param void
    * @return boolean
    */
    function hasImValue() {
      return ContactImValues::count('`contact_id` = ' . DB::escape($this->getId()));
    } // hasImValue
    
    /**
    * Return all IM values
    *
    * @access public
    * @param void
    * @return array
    */
    function getImValues() {
      return ContactImValues::getByContact($this);
    } // getImValues
    
    /**
    * Return value of specific IM. This function will return null if IM is not found
    *
    * @access public
    * @param ImType $im_type
    * @return string
    */
    function getImValue(ImType $im_type) {
      $im_value = ContactImValues::findById(array('contact_id' => $this->getId(), 'im_type_id' => $im_type->getId()));
      return $im_value instanceof ContactImValue && (trim($im_value->getValue()) <> '') ? $im_value->getValue() : null;
    } // getImValue
    
    /**
    * Return default IM value. If value was not found NULL is returned
    *
    * @access public
    * @param void
    * @return string
    */
    function getDefaultImValue() {
      $default_im_type = $this->getDefaultImType();
      return $this->getImValue($default_im_type);
    } // getDefaultImValue
    
    /**
    * Return default contact IM type. If there is no default contact IM type NULL is returned
    *
    * @access public
    * @param void
    * @return ImType
    */
    function getDefaultImType() {
      return ContactImValues::getDefaultContactImType($this);
    } // getDefaultImType
    
    /**
    * Clear all IM values
    *
    * @access public
    * @param void
    * @return boolean
    */
    function clearImValues() {
      return ContactImValues::instance()->clearByContact($this);
    } // clearImValues
    
    // ---------------------------------------------------
    //  Retrive
    // ---------------------------------------------------
    
    /**
    * Return owner company
    *
    * @access public
    * @param void
    * @return Company
    */
    function getCompany() {
    	if(is_null($this->company)) {
      		$this->company = Companies::findById($this->getCompanyId());
    	}
      	return $this->company;
    } // getCompany
    
    /**
    * Return assigned User
    *
    * @access public
    * @param void
    * @return User
    */
    function getUser() {
    	if(is_null($this->user)) {
        $this->user = Users::findById($this->getUserId());
      } // if
      return $this->user;
    } // getCompany
    
    /**
    * Return display name for this contact.
    *
    * @access public
    * @param void
    * @return string
    */
    function getDisplayName() {
      $mn = "";
      if (parent::getMiddlename() != "")
      	$mn = " " . parent::getMiddlename();
      $display = parent::getFirstName(). $mn ." ".parent::getLastName();
      return trim($display);
    } // getDisplayName
    
    /**
    * Return display name with last name first for this contact
    *
    * @access public
    * @param void
    * @return string
    */
    function getReverseDisplayName() {
    	$mn = "";
    	if (parent::getMiddlename() != "")
    		$mn = " " . parent::getMiddlename();
    	if (parent::getLastName() != "")
    		$display = parent::getLastName().", ".parent::getFirstName() . $mn;
    	else
    		$display = parent::getFirstName() . $mn;
    	return trim($display);
    } // getReverseDisplayName
    
    /**
    * Returns true if we have title value set
    *
    * @access public
    * @param void
    * @return boolean
    */
    function hasTitle() {
      return trim($this->getTitle()) <> '';
    } // hasTitle
    

    /**
    * Returns true if contact has an assigned user
    *
    * @access public
    * @param void
    * @return boolean
    */
    function hasUser() {
    	return ($this->getUserId() > 0 && $this->getUser() instanceOf User);
    } // hasTitle
    
    /**
    * Returns true if contact has an assigned company
    *
    * @access public
    * @param void
    * @return boolean
    */
    function hasCompany() {
    	return ($this->getCompanyId() > 0 && $this->getCompany() instanceOf Company);
    } // hasTitle
    
    
    // ---------------------------------------------------
    //  Picture file
    // ---------------------------------------------------
    
    /**
    * Set contact picture from $source file
    *
    * @param string $source Source file
    * @param integer $max_width Max picture widht
    * @param integer $max_height Max picture height
    * @param boolean $save Save user object when done
    * @return string
    */
    function setPicture($source, $max_width = 50, $max_height = 50, $save = true) {
      if(!is_readable($source)) return false;
      
      do {
        $temp_file = ROOT . '/cache/' . sha1(uniqid(rand(), true));
      } while(is_file($temp_file));
      
      try {
        Env::useLibrary('simplegd');
        
        $image = new SimpleGdImage($source);
        $thumb = $image->scale($max_width, $max_height, SimpleGdImage::BOUNDARY_DECREASE_ONLY, false);
        $thumb->saveAs($temp_file, IMAGETYPE_PNG);
        
        $public_filename = PublicFiles::addFile($temp_file, 'png');
        if($public_filename) {
          $this->setPictureFile($public_filename);
          if($save) {
            $this->save();
          } // if
        } // if
        
        $result = true;
      } catch(Exception $e) {
        $result = false;
      } // try
      
      // Cleanup
      if(!$result && $public_filename) {
        PublicFiles::deleteFile($public_filename);
      } // if
      @unlink($temp_file);
      
      return $result;
    } // setPicture
    
    /**
    * Delete picture
    *
    * @param void
    * @return null
    */
    function deletePicture() {
      if($this->hasPicture()) {
        PublicFiles::deleteFile($this->getPictureFile());
        $this->setPictureFile('');
      } // if
    } // deletePicture
    
    /**
    * Return path to the picture file. This function just generates the path, does not check if file really exists
    *
    * @access public
    * @param void
    * @return string
    */
    function getPicturePath() {
      return PublicFiles::getFilePath($this->getPictureFile());
    } // getPicturePath
    
    /**
    * Return URL of picture
    *
    * @access public
    * @param void
    * @return string
    */
    function getPictureUrl() {
      return $this->hasPicture() ? PublicFiles::getFileUrl($this->getPictureFile()) : get_image_url('avatar.gif');
    } // getPictureUrl
    
    /**
    * Check if this user has uploaded picture
    *
    * @access public
    * @param void
    * @return boolean
    */
    function hasPicture() {
      return (trim($this->getPictureFile()) <> '') && is_file($this->getPicturePath());
    } // hasPicture
    
    // ---------------------------------------------------
    //  Utils
    // ---------------------------------------------------
    
    /**
    * This function will generate new user password, set it and return it
    *
    * @param boolean $save Save object after the update
    * @return string
    */
    function resetPassword($save = true) {
      $new_password = substr(sha1(uniqid(rand(), true)), rand(0, 25), 13);
      $this->setPassword($new_password);
      if($save) {
        $this->save();
      } // if
      return $new_password;
    } // resetPassword
    
    /**
    * Set password value
    *
    * @param string $value
    * @return boolean
    */
    function setPassword($value) {
      do {
        $salt = substr(sha1(uniqid(rand(), true)), rand(0, 25), 13);
        $token = sha1($salt . $value);
      } while(Users::tokenExists($token));
      
      $this->setToken($token);
      $this->setSalt($salt);
      $this->setTwister(StringTwister::getTwister());
    } // setPassword
    
    /**
    * Return twisted token
    *
    * @param void
    * @return string
    */
    function getTwistedToken() {
      return StringTwister::twistHash($this->getToken(), $this->getTwister());
    } // getTwistedToken
    
    /**
    * Check if $check_password is valid user password
    *
    * @param string $check_password
    * @return boolean
    */
    function isValidPassword($check_password) {
      return  sha1($this->getSalt() . $check_password) == $this->getToken();
    } // isValidPassword
    
    /**
    * Check if $twisted_token is valid for this user account
    *
    * @param string $twisted_token
    * @return boolean
    */
    function isValidToken($twisted_token) {
      return StringTwister::untwistHash($twisted_token, $this->getTwister()) == $this->getToken();
    } // isValidToken
    
    /**
    * Return name of home country
    *
    * @access public
    * @param void
    * @return string
    */
    function getHCountryName() {
      return lang('country ' . $this->getHCountry());
    } // getHCountryName
    
    /**
    * Return name of work country
    *
    * @access public
    * @param void
    * @return string
    */
    function getWCountryName() {
      return lang('country ' . $this->getWCountry());
    } // getWCountryName
    
    /**
    * Return name of other country
    *
    * @access public
    * @param void
    * @return string
    */
    function getOCountryName() {
      return lang('country ' . $this->getOCountry());
    } // getOCountryName

    // ---------------------------------------------------
    //  URLs
    // ---------------------------------------------------
    
    /**
    * Return view contact URL of this contact
    *
    * @access public
    * @param void
    * @return string
    */
    function getViewUrl() {
      return get_url('contact', 'card', $this->getId());
    } // getAccountUrl
    
    /**
    * Return URL that will be used to create a user based on the info of this contact
    *
    * @access public
    * @param void
    * @return string
    */
    function getCreateUserUrl() {
      return get_url('contact', 'create_user', $this->getId());
    } //  getCreateUserUrl
    
    /**
    * Show contact card page
    *
    * @access public
    * @param void
    * @return null
    */
    function getCardUrl() {
      return get_url('contact', 'card', $this->getId());
    } // getCardUrl
    
    /**
    * Return edit contact URL
    *
    * @access public
    * @param void
    * @return string
    */
    function getEditUrl() {
      return get_url('contact', 'edit', $this->getId());
    } // getEditUrl
    
    /**
    * Return add contact URL
    *
    * @access public
    * @param void
    * @return string
    */
    function getAddUrl() {
      return get_url('contact', 'add');
    } // getEditUrl
    
    /**
    * Return delete contact URL
    *
    * @access public
    * @param void
    * @return string
    */
    function getDeleteUrl() {
      return get_url('contact', 'delete', $this->getId());
    } // getDeleteUrl
    
    /**
    * Return update picture URL
    *
    * @param string
    * @return string
    */
    function getUpdatePictureUrl($redirect_to = null) {
      $attributes = array('id' => $this->getId());
      if(trim($redirect_to) <> '') {
        $attributes['redirect_to'] = str_replace('&amp;', '&', trim($redirect_to));
      } // if
      
      return get_url('contact', 'edit_picture', $attributes);
    } // getUpdatePictureUrl
    
    /**
    * Return delete picture URL
    *
    * @param void
    * @return string
    */
    function getDeletePictureUrl($redirect_to = null) {
      $attributes = array('id' => $this->getId());
      if(trim($redirect_to) <> '') {
        $attributes['redirect_to'] = str_replace('&amp;', '&', trim($redirect_to));
      } // if
      
      return get_url('contact', 'delete_picture', $attributes);
    } // getDeletePictureUrl
    
    /**
    * Return assign to project URL
    *
    * @param void
    * @return string
    */
    function getAssignToProjectUrl($redirect_to = null) {
      $attributes = array('id' => $this->getId());
      if(trim($redirect_to) <> '') {
        $attributes['redirect_to'] = str_replace('&amp;', '&', trim($redirect_to));
      } // if
      
      return get_url('contact', 'assign_to_project', $attributes);
    } // getDeletePictureUrl
    
    // ---------------------------------------------------
    //  System functions
    // ---------------------------------------------------
    
    /**
    * Validate data before save
    *
    * @access public
    * @param array $errors
    * @return void
    */
    function validate(&$errors) {
      
      // Validate username if present
      if(!$this->validatePresenceOf('lastname') && !$this->validatePresenceOf('firstname')) {
        $errors[] = lang('contact identifier required');
      } 
      if (!$this->validateUniquenessOf('firstname','lastname' )) { // if
      	$errors[] = lang('name must be unique');
      }
      
      //if email address is entered, it must be unique
		if($this->validatePresenceOf('email')) {
			$this->setEmail(trim($this->getEmail()));
			if(!$this->validateFormatOf('email', EMAIL_FORMAT)) $errors[] = lang('invalid email address');
			if(!$this->validateUniquenessOf('email')) $errors[] = lang('email address must be unique');
		}
    } // validate
    
    /**
    * Delete this object
    *
    * @param void
    * @return boolean
    */
    function delete() {
      if($this->getUserId() && !can_manage_security(logged_user())) {
        return false;
      } // if
      
      $roles = $this->getRoles();
	  if($roles){	      
	      foreach ($roles as $role)
	      	$role->delete();
	  }
      $this->deletePicture();
      return parent::delete();
    } // delete

    
    // ---------------------------------------------------
    //  ApplicationDataObject implementation
    // ---------------------------------------------------
    
    /**
    * Return object name
    *
    * @access public
    * @param void
    * @return string
    */
    function getObjectName() {
      return $this->getDisplayName();
    } // getObjectName
    
    /**
    * Return object type name
    *
    * @param void
    * @return string
    */
    function getObjectTypeName() {
      return 'contact';
    } // getObjectTypeName
    
    /**
    * Return object URl
    *
    * @access public
    * @param void
    * @return string
    */
    function getObjectUrl() {
      return $this->getCardUrl();
    } // getObjectUrl
	
	// ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------
    
    
    /**
    * Returns true if $user can access this contact
    *
    * @param User $user
    * @return boolean
    */
    function canView(User $user) {
		if (can_manage_contacts($user, true))
			return true;
		else {
			if ($user->getId() == $this->getUserId())
				return true;
				
			if (can_read($user,$this))
				return true;
				
			else{
				$roles = $this->getRoles();
				if ($roles){
					foreach ($roles as $role){
						if ($role->canView($user))
							return true;
					}
				}
				return false;
			}
		}
    } // canView
    
    /**
     * Check if specific user can add contacts
     *
     * @access public
     * @param User $user
     * @param Project $project
     * @return booelean
     */
    function canAdd(User $user, Project $project) {
    	if ($project instanceof Project) { 
    		return can_manage_contacts($user, true) || $user->getProjectPermission($project, ProjectUsers::CAN_WRITE_CONTACTS);
    	} else {
			return can_manage_contacts($user, true);
    	}
    } // canAdd
    
    /**
    * Check if specific user can edit this contact
    *
    * @access public
    * @param User $user
    * @return boolean
    */
    function canEdit(User $user) {
    	if($this->getUserId()){
    		// a contact that has a user assigned to it can be modified by anybody that can manage security (this is: users and permissions) or the user himself.
    		return can_manage_contacts($user, true) || can_manage_security($user) || $this->getUserId() == $user->getId() || $this->canWriteByRoles($user);
    	}
		else 
			return can_manage_contacts($user, true) || $this->canWriteByRoles($user);
    } // canEdit
    
    /**
    * Check if specific user can delete this contact
    *
    * @access public
    * @param User $user
    * @return boolean
    */
    function canDelete(User $user) {
		return can_manage_contacts($user, true) || $this->canWriteByRoles($user);
    } // canDelete
    
    function canLinkObject(User $user){
		if (can_manage_contacts($user, true))
			return true;
		else {
			$roles = $this->getRoles();
			foreach ($roles as $role){
				if ($role->canView($user))
					return true;
			}
			return false;
		}   	
    }
    
	// ---------------------------------------------------
    //  Roles
    // ---------------------------------------------------
    
    /**
    * Return all roles for this contact
    *
    * @access public
    * @return array
    */
    function getRoles()
    {
    	if ($this->getId() == '') return array();
    	return ProjectContacts::getRolesByContact($this);
    }
    
    /**
     * Return the role for this contact in a specific project
     *
     * @param Project $project
     * @return ProjectContact
     */
    function getRole(Project $project)
    {
    	if (!$project instanceof Project) {
    		return null;
    	}
    	return ProjectContacts::getRole($this,$project);
    }
    
    function canWriteByRoles(User $user){
    	$roles = $this->getRoles();
    	if (is_array($roles)) {
    		foreach ($roles as $pc) {
    			$p = $pc->getProject();
    			if ($user->getProjectPermission($p, ProjectUsers::CAN_WRITE_CONTACTS)) {
    				return true;
    			}
    		}
    	}
    	return false;
    }

	// ---------------------------------------------------
    //  Addresses
    // ---------------------------------------------------

    
    
    function getFullHomeAddress()
    {
    	$line1 = $this->getHAddress();
    	$line2 = '';
    	$line3 = '';
    	
    	if ($this->getHCity() != '')
    		$line2 = $this->getHCity();
    	
    	if ($this->getHState() != '')
    	{
    		if ($line2 != '')
    			$line2 .= ', ';
    		$line2 .= $this->getHState();
    	}
    	
    	if ($this->getHZipcode() != '')
    	{
    		if ($line2 != '')
    			$line2 .= ', ';
    		$line2 .= $this->getHZipcode();
    	}
    	
    	if ($this->getHCountry() != '')
	    	$line3 = $this->getHCountryName();
	    	
	    $result = $line1;
	    if ($line2 != '')
	    	$result .= "\n" . $line2;
	    if ($line3 != '')
	    	$result .= "\n" . $line3;
    	
    	return $result;
    }

    /**
     * Returns the full work address
     *
     * @return string
     */
    function getFullWorkAddress()
    {
    	$line1 = $this->getWAddress();
    	
    	$line2 = '';
    	if ($this->getWCity() != '')
    		$line2 = $this->getWCity();
    	
    	if ($this->getWState() != '')
    	{
    		if ($line2 != '')
    			$line2 .= ', ';
    		$line2 .= $this->getWState();
    	}
    	
    	if ($this->getWZipcode() != '')
    	{
    		if ($line2 != '')
    			$line2 .= ', ';
    		$line2 .= $this->getWZipcode();
    	}
    	
    	$line3 = '';
    	if ($this->getWCountry() != '')
	    	$line3 = $this->getWCountryName();
	    	
	    $result = $line1;
	    if ($line2 != '')
	    	$result .= "\n" . $line2;
	    if ($line3 != '')
	    	$result .= "\n" . $line3;
    	
    	return $result;
    }
    
/**
     * Returns the full work address
     *
     * @return string
     */
    function getFullOtherAddress()
    {
    	$line1 = $this->getOAddress();
    	$line2 = '';
    	$line3 = '';
    	
    	if ($this->getOCity() != '')
    		$line2 = $this->getOCity();
    	
    	if ($this->getOState() != '')
    	{
    		if ($line2 != '')
    			$line2 .= ', ';
    		$line2 .= $this->getOState();
    	}
    	
    	if ($this->getOZipcode() != '')
    	{
    		if ($line2 != '')
    			$line2 .= ', ';
    		$line2 .= $this->getOZipcode();
    	}
    	
    	if ($this->getOCountry() != '')
	    	$line3 = $this->getOCountryName();
	    	
	    $result = $line1;
	    if ($line2 != '')
	    	$result .= "\n" . $line2;
	    if ($line3 != '')
	    	$result .= "\n" . $line3;
    	
    	return $result;
    }
    
    function getDashboardObject(){
    	
    	$wsIds =  $this->getProjectIdsCSV();
    	
    	if($this->getUpdatedBy()){
    		$updated_by_id = $this->getUpdatedBy()->getObjectId();
    		$updated_by_name = $this->getUpdatedByDisplayName();
    		$updated_on=($this->getObjectUpdateTime())?$this->getObjectUpdateTime()->getTimestamp(): lang('n/a');
    	}else {
    		if($this->getCreatedBy())
    			$updated_by_id = $this->getCreatedBy()->getId();
    		else
    			$updated_by_id = lang('n/a');
    		$updated_by_name = $this->getCreatedByDisplayName();
    		$updated_on =($this->getObjectCreationTime())? $this->getObjectCreationTime()->getTimestamp(): lang('n/a');
    	}
    	
    	$deletedOn = $this->getTrashedOn() ? $this->getTrashedOn()->getTimestamp() : lang('n/a');
    	$deletedBy = Users::findById($this->getTrashedById());
    	if ($deletedBy instanceof User) {
    		$deletedBy = $deletedBy->getDisplayName();
    	} else {
    		$deletedBy = lang("n/a");
    	}
    	
    	return array(
				"id" => $this->getObjectTypeName() . $this->getId(),
				"object_id" => $this->getId(),
				"name" => $this->getObjectName(),
				"type" => $this->getObjectTypeName(),
				"tags" => project_object_tags($this),
				"createdBy" => $this->getCreatedByDisplayName(),// Users::findById($this->getCreatedBy())->getUsername(),
				"createdById" => $this->getCreatedById(),
				"dateCreated" => ($this->getObjectCreationTime())?$this->getObjectCreationTime()->getTimestamp():lang('n/a'),
				"updatedBy" => $updated_by_name,
				"updatedById" => $updated_by_id,
				"dateUpdated" => $updated_on,
				"wsIds" => $wsIds,
				"url" => $this->getObjectUrl(),
				"manager" => get_class($this->manager()),
    			"deletedById" => $this->getTrashedById(),
    			"deletedBy" => $deletedBy,
    			"dateDeleted" => $deletedOn
			);
    }
    
    /**
     * Returns CSVs of the workspaces the contact is assigned to 
     *
     * @return unknown
     */
    function getProjectIdsCSV($wsIds = null) {
    	$workspaces = ProjectContacts::getProjectsByContact($this);
    	$result = array();
    	if($workspaces){
	    	if (!is_null($wsIds)){
				foreach($workspaces as $w){
					if ($this->isInCsv($w->getId(),$wsIds))
						$result[] = $w->getId();
				}
			} else foreach ($workspaces as $w){
	    		$result[] = $w->getId();
	    	}
    	}
    	return implode(',',$result);
    }
    
    function getTagNames() {
      if(!$this->isTaggable()) throw new Error('Object not taggable');
      return Tags::getTagNamesByObject($this, get_class($this->manager()));
    } // getTagNames
    
}
?>