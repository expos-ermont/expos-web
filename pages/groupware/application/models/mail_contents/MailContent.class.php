<?php

/**
 * MailContent class
 * Generated on Wed, 15 Mar 2006 22:57:46 +0100 by DataObject generation tool
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
class MailContent extends BaseMailContent {

	/**
	 * Cache of account
	 *
	 * @var MailAccount
	 */
	private $account;

	//protected $project;
	protected $workspaces = null;

	/**
	 * This project object is taggable
	 *
	 * @var boolean
	 */
	protected $is_taggable = true;
	
	/**
	 * Mail contents are searchable
	 *
	 * @var boolean
	 */
	protected $is_searchable = true;

	/**
	 * Array of searchable columns
	 *
	 * @var array
	 */
	protected $searchable_columns = array('from', 'from_name', 'to', 'subject', 'body_plain', );
	 
	/**
	 * Project file is commentable object
	 *
	 * @var boolean
	 */
	protected $is_commentable = true;
	
	/**
	 * Return Project
	 *
	 * @access public
	 * @param void
	 * @return Project
	 */
	function getProject()
	{
		return null;
	}
	 
	/**
	 * Gets the owner mail account
	 *
	 * @return MailAccount
	 */
	function getAccount()
	{
		if (is_null($this->account)){
			$this->account = MailAccounts::findById($this->getAccountId());
		} //if
		return $this->account;
	}
	 
	/**
	 * Validate before save
	 *
	 * @access public
	 * @param array $errors
	 * @return null
	 */
	function validate(&$errors) {
		if(!$this->validatePresenceOf('uid')) {
			$errors[] = lang('uid required');
		} // if
		if(!$this->validatePresenceOf('account_id')) {
			$errors[] = lang('account id required');
		} // if
	} // validate

	function delete(){
		if (FileRepository::getBackend() instanceof FileRepository_Backend_FileSystem) {
			if ($this->getContentFileId() != '') {
				FileRepository::deleteFile($this->getContentFileId());
			}
		}
		return parent::delete();
	}

	function deleteContents()
	{
		$this->setContent("");
		$this->setBodyHtml("");
		$this->setBodyPlain("");
		$this->setFrom("");
		$this->setTo("");
		$this->setIsDeleted(true);
		$this->clearTags();
		$this->clearSearchIndex();
		$this->clearLinkedObjects();
		$readobj = ReadObjects::findOne(array(
						        'conditions' => array('`rel_object_id` = ? AND `rel_object_manager` = ?', $this->getId(), get_class($this->manager()))
						      )); // findOne
		if ($readobj!=null) $readobj->delete();
		return true;
	}

	function getTitle(){
		return $this->getSubject();
	}
	
	/**
	 * Returns the mail content. If it is in repository returns the file content,
	 * else tries to get the content from database (if column content exists).
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getContent() {
		if (FileRepository::getBackend() instanceof FileRepository_Backend_FileSystem && 
			FileRepository::isInRepository($this->getContentFileId())) {
				return FileRepository::getFileContent($this->getContentFileId());
		}
		else if ($this->columnExists('content'))
			return $this->getColumnValue('content');
		else return '';
	} // getContent()
	


	/**
	 * Returns if the field is classified
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getIsClassified() {
		$wspaces = $this->getWorkspaces();
		return (is_array($wspaces) && count($wspaces) > 0);
	} // getIsClassified()
	
	
	
	/**
	 * Returns if the mail is a draft
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getIsDraft() {
		return ($this->getColumnValue('state') == 2);
	} // getIsDraft()
	
	
	/**
	 * Returns if the mail was sent
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getIsSent() {
		return ($this->getColumnValue('state') == 1);
	} // getIsSent()
	
	
	
	/**
	 * Returns if the mail is read by the user
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getIsRead($user_id) {
		return ReadObjects::userHasRead($user_id,$this);
	} // getIsClassified()
	
	/**
	 * Mark the mail as read/unread
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function setIsRead($is_read, $user_id) {
		try{
			$readobj = ReadObjects::findOne(array(
						        'conditions' => array('`user_id` = ? and `rel_object_id` = ? AND `rel_object_manager` = ?', $user_id, $this->getId(), get_class($this->manager()))
						      )); // findOne
			if ($is_read){
				if ($readobj!=null){
					$readobj->setIsRead(1);
					$readobj->save();
				} else {
					$readobj = new ReadObject();
					$readobj->setIsRead(1);
					$readobj->setUserId($user_id);
					$readobj->setRelObjectId($this->getId());
					$readobj->setRelObjectManager(get_class($this->manager()));
					$readobj->save();
				}
			} else {				
				if ($readobj!=null) $readobj->delete();
			}
		} catch(Exception $e){
			throw $e;
		}	
	} // getIsClassified()


	// ---------------------------------------------------
	//  URLs
	// ---------------------------------------------------

	/**
	 * Return view mail URL of this mail
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getViewUrl() {
		return get_url('mail', 'view', $this->getId());
	} // getAccountUrl
	
	function getShowContentsUrl() {
		return get_url('mail', 'view', $this->getId());
	} // getAccountUrl

	/**
	 * Return delete mail URL of this mail
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('mail', 'delete', $this->getId());
	} // getDeleteUrl

	/**
	 * Return classify mail URL of this mail
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getClassifyUrl() {
		return get_url('mail', 'classify', array( 'id' => $this->getId(), 'type' => 'email'));
	} // getClassifyUrl

	/**
	 * Return unclassify mail URL of this mail
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getUnclassifyUrl() {
		return get_url('mail', 'unclassify', array( 'id' => $this->getId(), 'type' => 'email'));
	} // getClassifyUrl
	
	/**
	 * Return send mail URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getSendMailUrl() {
		return get_url('mail', 'add_mail');
	} // getClassifyUrl

	/**
	 * Return reply mail URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getReplyMailUrl() {
		return get_url('mail', 'reply_mail', array( 'id' => $this->getId(), 'type' => 'email'));
	} // getReplyMailUrl
	
	
	
	function getForwardMailUrl() {
		return get_url('mail', 'forward_mail', array( 'id' => $this->getId(), 'type' => 'email'));
	} // getReplyMailUrl
	
	
	
	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	/**
	 * Returns true if $user can view this email
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canView(User $user) {	
		return can_read($user,$this);
		//return $this->getAccount()->getUserId() == $user->getId() || $user->isAdministrator();
	} // canView


	/**
	 * Returns true if $user can edit this email
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canEdit(User $user) {	
		return can_write($user,$this);
//		return $this->getAccount()->getUserId() == $user->getId() || $user->isAdministrator();
	} // canEdit

	/**
	 * Check if specific user can add contacts to specific project
	 *
	 * @access public
	 * @param User $user
	 * @param Project $project
	 * @return booelean
	 */
	function canAdd(User $user, Project $project) {
		return can_add($user,$project,get_class(MailContents::instance()));
	} // canAdd

	/**
	 * Returns true if $user can delete this email
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canDelete(User $user) {
		return can_delete($user,$this);
//		return $this->getAccount()->getUserId() == $user->getId() || $user->isAdministrator();
	} // canView

	// ---------------------------------------------------
	//  ApplicationDataObject implementation
	// ---------------------------------------------------

    function addToSearchableObjects($wasNew){
    	$columns_to_drop = array();
    	if ($wasNew)
    		$columns_to_drop = $this->getSearchableColumns();
    	else {
	    	foreach ($this->getSearchableColumns() as $column_name){
	    		if ($this->isColumnModified($column_name))
	    			$columns_to_drop[] = $column_name;
	    	}
    	}
    	
    	if (count($columns_to_drop) > 0){
    		SearchableObjects::dropContentByObjectColumns($this,$columns_to_drop);
    		
	        foreach($columns_to_drop as $column_name) {
	          $content = $this->getSearchableColumnContent($column_name);
	          if(trim($content) <> '') {
	            $searchable_object = new SearchableObject();
	            
	            $searchable_object->setRelObjectManager(get_class($this->manager()));
	            $searchable_object->setRelObjectId($this->getObjectId());
	            $searchable_object->setColumnName($column_name);
	            $searchable_object->setContent($content);
	            $searchable_object->setProjectId(0);
	            $searchable_object->setIsPrivate(false);
	            $searchable_object->setUserId($this->getAccount()->getUserId());
	            
	            $searchable_object->save();
	          } // if
	        } // foreach
    	} // if
    	
    	if ($wasNew){
        	SearchableObjects::dropContentByObjectColumns($this,array('uid'));
        	$searchable_object = new SearchableObject();
            
            $searchable_object->setRelObjectManager(get_class($this->manager()));
            $searchable_object->setRelObjectId($this->getObjectId());
            $searchable_object->setColumnName('uid');
            $searchable_object->setContent($this->getUniqueObjectId());
	        $searchable_object->setProjectId(0);
            $searchable_object->setIsPrivate(false);
	        $searchable_object->setUserId($this->getAccount()->getUserId());
            
            $searchable_object->save();
        }
    }
	
	/**
	 * Return object name
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectName() {
		return $this->getName();
	} // getObjectName

	/**
	 * Return object type name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectTypeName() {
		if (isset($this->workspaces) && is_array($this->workspaces) && count($this->workspaces))
			return 'email';
		else
			return 'emailunclassified';
	} // getObjectTypeName

	/**
	 * Return object URl
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getViewUrl();
	} // getObjectUrl


	/**
	 * Return value of 'subject' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getName() {
		return $this->getColumnValue('subject');
	} // getSubject()


  function getDashboardObject(){
    	$projectId = "0";
    	$project = "";
    	$type = "emailunclassified";
    	$tags = project_object_tags($this);
    	
  		$deletedOn = $this->getTrashedOn() ? $this->getTrashedOn()->getTimestamp() : lang('n/a');
    	$deletedBy = Users::findById($this->getTrashedById());
    	if ($deletedBy instanceof User) {
    		$deletedBy = $deletedBy->getDisplayName();
    	} else {
    		$deletedBy = lang("n/a");
    	}
    	$sentTimestamp = $this->getSentDate() instanceof DateTimeValue ? $this->getSentDate()->getTimestamp() : 0;
    	return array(
				"id" => $this->getObjectTypeName() . $this->getId(),
				"object_id" => $this->getId(),
				"name" => $this->getObjectName(),
				"type" => $type,
				"tags" => $tags,
				"createdBy" => $this->getAccount()->getOwner()->getDisplayName(),
				"createdById" => $this->getAccount()->getOwner()->getId(),
				"dateCreated" => $sentTimestamp,
				"updatedBy" => $this->getAccount()->getOwner()->getDisplayName(),
				"updatedById" => $this->getAccount()->getOwner()->getId(),
				"dateUpdated" => $sentTimestamp,
				"project" => $this->getWorkspacesNamesCSV(logged_user()->getActiveProjectIdsCSV()),//$project,
				"projectId" => $this->getWorkspacesIdsCSV(logged_user()->getActiveProjectIdsCSV()),
    			"workspaceColors" => $this->getWorkspaceColorsCSV(logged_user()->getActiveProjectIdsCSV()),
				"url" => $this->getObjectUrl(),
				"manager" => get_class($this->manager()),
    			"deletedById" => $this->getTrashedById(),
    			"deletedBy" => $deletedBy,
    			"dateDeleted" => $deletedOn
		);
	}
	
	
}
?>