<?php

/**
 * BaseMailContent class
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
abstract class BaseMailContent extends ProjectDataObject {
  
  	protected $objectTypeIdentifier = 'mc';

	// -------------------------------------------------------
	//  Access methods
	// -------------------------------------------------------

	/**
	 * Return value of 'id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getId() {
		return $this->getColumnValue('id');
	} // getId()

	/**
	 * Set value of 'id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setId($value) {
		return $this->setColumnValue('id', $value);
	} // setId()

	/**
	 * Return value of 'account_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getAccountId() {
		return $this->getColumnValue('account_id');
	} // getAccountId()

	/**
	 * Set value of 'account_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setAccountId($value) {
		return $this->setColumnValue('account_id', $value);
	} // setAccountId()

	/**
	 * Return value of 'uid' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getUid() {
		return $this->getColumnValue('uid');
	} // getUid()

	/**
	 * Set value of 'uid' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setUid($value) {
		return $this->setColumnValue('uid', $value);
	} // setUid()

	/**
	 * Return value of 'from' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getFrom() {
		return $this->getColumnValue('from');
	} // getFrom()

	/**
	 * Set value of 'from' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setFrom($value) {
		return $this->setColumnValue('from', $value);
	} // setFrom()

	/**
	 * Return value of 'from_name' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getFromName() {
		return $this->getColumnValue('from_name');
	} // getFromName()

	/**
	 * Set value of 'from_name' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setFromName($value) {
		return $this->setColumnValue('from_name', $value);
	} // setFromName()
	
	/**
	 * Return value of 'to' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getTo() {
		return $this->getColumnValue('to');
	} // getTo()

	/**
	 * Set value of 'to' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setTo($value) {
		return $this->setColumnValue('to', $value);
	} // setTo()

	/**
	 * Return value of 'subject' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getSubject() {
		return $this->getColumnValue('subject');
	} // getSubject()

	/**
	 * Set value of 'subject' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setSubject($value) {
		return $this->setColumnValue('subject', $value);
	} // setSubject()

	/**
	 * Return value of 'date' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getDate() {
		return $this->getColumnValue('date');
	} // getDate()

	/**
	 * Set value of 'date' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setDate($value) {
		return $this->setColumnValue('date', $value);
	} // setDate()

	/**
	 * Return value of 'sent_date' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getSentDate() {
		return $this->getColumnValue('sent_date');
	} // getSentDate()

	/**
	 * Set value of 'sent_date' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setSentDate($value) {
		return $this->setColumnValue('sent_date', $value);
	} // setSentDate()
	
	/**
	 * Return value of 'content' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getContent() {
		return $this->getColumnValue('content');
	} // getContent()

	/**
	 * Set value of 'content' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setContent($value) {
		return $this->setColumnValue('content', $value);
	} // setContent()

	/**
	 * Return value of 'body_plain' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getBodyPlain() {
		return $this->getColumnValue('body_plain');
	} // getBodyPlain()

	/**
	 * Set value of 'body_plain' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setBodyPlain($value) {
		return $this->setColumnValue('body_plain', $value);
	} // setBodyPlain()

	/**
	 * Return value of 'body_html' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getBodyHtml() {
		return $this->getColumnValue('body_html');
	} // getBodyHtml()

	/**
	 * Set value of 'body_html' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setBodyHtml($value) {
		return $this->setColumnValue('body_html', $value);
	} // setBodyHtml()

	/**
	 * Return value of 'has_attachments' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getHasAttachments() {
		return $this->getColumnValue('has_attachments');
	} // getHasAttachments()

	/**
	 * Set value of 'has_attachments' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setHasAttachments($value) {
		return $this->setColumnValue('has_attachments', $value);
	} // setHasAttachments()

	/**
	 * Return value of 'is_deleted' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getIsDeleted() {
		return $this->getColumnValue('is_deleted');
	} // getIsDeleted()

	/**
	 * Set value of 'is_deleted' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setIsDeleted($value) {
		return $this->setColumnValue('is_deleted', $value);
	} // setIsDeleted()

	/**
	 * Return value of 'is_shared' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getIsShared() {
		return $this->getColumnValue('is_shared');
	} // getIsShared()

	/**
	 * Set value of 'is_shared' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setIsShared($value) {
		return $this->setColumnValue('is_shared', $value);
	} // setIsShared()
	
	/**
	 * Return value of 'size' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getSize() {
		return $this->getColumnValue('size');
	} // getSize()

	/**
	 * Set value of 'size' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setSize($value) {
		return $this->setColumnValue('size', $value);
	} // setSize()
	
	
	/**
	 * Return value of 'state' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getState() {
		return $this->getColumnValue('state');
	} // getState()

	/**
	 * Set value of 'state' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setState($value) {
		return $this->setColumnValue('state', $value);
	} // setState()

	
    /**
    * Return value of 'is_private' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsPrivate() {
      return $this->getColumnValue('is_private');
    } // getIsPrivate()
    
    /**
    * Set value of 'is_private' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsPrivate($value) {
      return $this->setColumnValue('is_private', $value);
    } // setIsPrivate() 
    
    
  
    /**
    * Return value of 'created_on' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getCreatedOn() {
      return $this->getColumnValue('created_on');
    } // getCreatedOn()
    
    /**
    * Set value of 'created_on' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setCreatedOn($value) {
      return $this->setColumnValue('created_on', $value);
    } // setCreatedOn() 
    
    /**
    * Return value of 'created_by_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getCreatedById() {
      return $this->getColumnValue('created_by_id');
    } // getCreatedById()
    
    /**
    * Set value of 'created_by_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setCreatedById($value) {
      return $this->setColumnValue('created_by_id', $value);
    } // setCreatedById() 

    /** Return value of 'trashed_on' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getTrashedOn() {
      return $this->getColumnValue('trashed_on');
    } // getTrashedOn()
    
    /**
    * Set value of 'trashed_on' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setTrashedOn($value) {
      return $this->setColumnValue('trashed_on', $value);
    } // setTrashedOn() 
    
    /**
    * Return value of 'trashed_by_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getTrashedById() {
      return $this->getColumnValue('trashed_by_id');
    } // getTrashedById()
    
    /**
    * Set value of 'trashed_by_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setTrashedById($value) {
      return $this->setColumnValue('trashed_by_id', $value);
    } // setTrashedById()
    
       
    /**
    * Return value of 'imap_folder_name' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getImapFolderName() {
      return $this->getColumnValue('imap_folder_name');
    } // getImapFolderName()
    
    /**
    * Set value of 'imap_folder_name' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setImapFolderName($value) {
      return $this->setColumnValue('imap_folder_name', $value);
    } // setImapFolderName()
    
        /**
    * Return value of 'account_email' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getAccountEmail() {
      return $this->getColumnValue('account_email');
    } // getAccountEmail()
    
    /**
    * Set value of 'account_email' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setAccountEmail($value) {
      return $this->setColumnValue('account_email', $value);
    } // setAccountEmail()
    
    /**
    * Return value of 'content_file_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getContentFileId() {
      return $this->getColumnValue('content_file_id');
    } // getContentFileId()
    
    /**
    * Set value of 'content_file_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setContentFileId($value) {
      return $this->setColumnValue('content_file_id', $value);
    } // setContentFileId()
  
    
	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return MailContents
	 */
	function manager() {
		if(!($this->manager instanceof MailContents)) $this->manager = MailContents::instance();
		return $this->manager;
	} // manager

} // BaseMailContent

?>