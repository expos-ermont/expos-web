<?php

  /**
  * BaseMailAccount class
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
  abstract class BaseMailAccount extends DataObject {
  
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
    * Return value of 'user_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getUserId() {
      return $this->getColumnValue('user_id');
    } // getUserId()
    
    /**
    * Set value of 'user_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setUserId($value) {
      return $this->setColumnValue('user_id', $value);
    } // setUserId() 
    
    /**
     * Return value of 'name' field
     *
     * @access public
     * @param void
     * @return string
     */
    function getName() {
    	return $this->getColumnValue('name');
    } // getName()

    /**
     * Set value of 'name' field
     *
     * @access public
     * @param string $value
     * @return boolean
     */
    function setName($value) {
    	return $this->setColumnValue('name', $value);
    } // setName()

    /**
     * Return value of 'email' field
     *
     * @access public
     * @param void
     * @return string
     */
    function getEmail() {
    	return $this->getColumnValue('email');
    } // getEmail()

    /**
     * Set value of 'email' field
     *
     * @access public
     * @param string $value
     * @return boolean
     */
    function setEmail($value) {
    	return $this->setColumnValue('email', $value);
    } // setEmail()
    
    
    /**
     * Return value of 'email_addr' field
     *
     * @access public
     * @param void
     * @return string
     */
    function getEmailAddress() {
    	return $this->getColumnValue('email_addr');
    } // getEmailAddress()

    /**
     * Set value of 'email' field
     *
     * @access public
     * @param string $value
     * @return boolean
     */
    function setEmailAddress($value) {
    	return $this->setColumnValue('email_addr', $value);
    } // setEmailAddress()


    /**
     * Return value of 'password' field
     *
     * @access public
     * @param void
     * @return string
     */
    function getPassword() {
    	return $this->getColumnValue('password');
    } // getPassword()

    /**
     * Set value of 'password' field
     *
     * @access public
     * @param string $value
     * @return boolean
     */
    function setPassword($value) {
    	return $this->setColumnValue('password', $value);
    } // setPassword()

    /**
     * Return value of 'server' field
     *
     * @access public
     * @param void
     * @return string
     */
    function getServer() {
    	return $this->getColumnValue('server');
    } // getServer()

    /**
     * Set value of 'server' field
     *
     * @access public
     * @param string $value
     * @return boolean
     */
    function setServer($value) {
    	return $this->setColumnValue('server', $value);
    } // setServer()

    /**
     * Return value of 'smtp_server' server name field
     *
     * @access public
     * @param void
     * @return string
     */
    function getSmtpServer() {
    	return $this->getColumnValue('smtp_server');
    } // getsmtp()

    /**
     * Set value of 'smtp_server' server name field
     *
     * @access public
     * @param string $value
     * @return boolean
     */
    function setSmtpServer($value) {
    	return $this->setColumnValue('smtp_server', $value);
    } // setsmtp()

    /**
     * Return value of 'smtpPort' server name field
     *
     * @access public
     * @param void
     * @return string
     */
    function getSmtpPort() {
    	return $this->getColumnValue('smtp_port');
    } // getsmtpPort()

    /**
     * Set value of 'smtpPort' server name field
     *
     * @access public
     * @param string $value
     * @return boolean
     */
    function setSmtpPort($value) {
    	return $this->setColumnValue('smtp_port', $value);
    } // setsmtpPort()

    /**
     * Return value of 'smtpUsername' field
     *
     * @access public
     * @param void
     * @return string
     */
    function getSmtpUsername() {
    	return $this->getColumnValue('smtp_username');
    } // getsmtpUsername()

    /**
     * Set value of 'smtpUsername' field
     *
     * @access public
     * @param string $value
     * @return boolean
     */
    function setSmtpUsername($value) {
    	return $this->setColumnValue('smtp_username', $value);
    } // setsmtpUsername()

    /**
     * Return value of 'smtpPassword' field
     *
     * @access public
     * @param void
     * @return string
     */
    function getSmtpPassword() {
    	return $this->getColumnValue('smtp_password');
    } // getsmtpPassword()

    /**
     * Set value of 'smtpPassword' field
     *
     * @access public
     * @param string $value
     * @return boolean
     */
    function setSmtpPassword($value) {
    	return $this->setColumnValue('smtp_password', $value);
    } // setsmtpPassword()

    /**
     * Return value of 'smtp_use_auth' field
     *
     * @access public
     * @param void
     * @return string
     */
    function getSmtpUseAuth() {
    	return $this->getColumnValue('smtp_use_auth');
    } //  getSmtpUseAuth()

    /**
     * Set value of 'smtp_use_auth' field
     *
     * @access public
     * @param string $value
     * @return boolean
     */
    function  setSmtpUseAuth($value) {
    	return $this->setColumnValue('smtp_use_auth', $value);
    } //  setSmtpUseAuth()

    /**
     * Return value of 'is_imap' field
     *
     * @access public
     * @param void
     * @return boolean
     */
    function getIsImap() {
    	return $this->getColumnValue('is_imap');
    } // getIsImap()

    /**
     * Set value of 'is_imap' field
     *
     * @access public
     * @param boolean $value
     * @return boolean
     */
    function setIsImap($value) {
    	return $this->setColumnValue('is_imap', $value);
    } // setIsImap()

    /**
     * Return value of 'incoming_ssl' field
     *
     * @access public
     * @param void
     * @return boolean
     */
    function getIncomingSsl() {
    	return $this->getColumnValue('incoming_ssl');
    } // getIncomingSsl()

    /**
     * Set value of 'incoming_ssl' field
     *
     * @access public
     * @param boolean $value
     * @return boolean
     */
    function setIncomingSsl($value) {
    	return $this->setColumnValue('incoming_ssl', $value);
    } // setIncomingSsl()

    /**
     * Return value of 'incoming_ssl_port' field
     *
     * @access public
     * @param void
     * @return integer
     */
    function getIncomingSslPort() {
    	return $this->getColumnValue('incoming_ssl_port');
    } // getIncomingSslPort()

    /**
     * Set value of 'incoming_ssl_port' field
     *
     * @access public
     * @param integer $value
     * @return boolean
     */
    function setIncomingSslPort($value) {
    	return $this->setColumnValue('incoming_ssl_port', $value);
    } // setIncomingSslPort()

    /**
     * Return value of 'del_from_server' field
     *
     * @access public
     * @param void
     * @return integer
     */
    function getDelFromServer() {
    	return $this->getColumnValue('del_from_server');
    } // getDelFromServer()

    /**
     * Set value of 'del_from_server' field
     *
     * @access public
     * @param integer $value
     * @return boolean
     */
    function setDelFromServer($value) {
    	return $this->setColumnValue('del_from_server', $value);
    } // setDelFromServer()
    

    /**
     * Return value of 'outgoing_transport_type' field
     *
     * @access public
     * @param void
     * @return string
     */
    function getOutgoingTrasnportType() {
    	return $this->getColumnValue('outgoing_transport_type');
    } // getOutgoingTrasnportType()

    /**
     * Set value of 'outgoing_transport_type' field
     *
     * @access public
     * @param string $value
     * @return boolean
     */
    function setOutgoingTrasnportType($value) {
    	return $this->setColumnValue('outgoing_transport_type', $value);
    } // setOutgoingTrasnportType()
    
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return MailAccounts
    */
    function manager() {
      if(!($this->manager instanceof MailAccounts)) $this->manager = MailAccounts::instance();
      return $this->manager;
    } // manager
  
  } // BaseMailAccount 

?>