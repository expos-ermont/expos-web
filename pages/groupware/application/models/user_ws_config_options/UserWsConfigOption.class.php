<?php

  /**
  * UserWsConfigOption class
  *
  * @author Marcos Saiz <marcos.saiz@opengoo.org>
  */
  class UserWsConfigOption extends BaseUserWsConfigOption {
    
    /**
    * Config handler instance
    *
    * @var ConfigHandler
    */
    private $config_handler;
    
    /**
    * Return display name
    *
    * @param void
    * @return string
    */
    function getDisplayName() {
      return lang('user ws config option name ' . $this->getName());
    } // getDisplayName
    
    /**
    * Return display description
    *
    * @param void
    * @return string
    */
    function getDisplayDescription() {
      return Localization::instance()->lang('user ws config option desc ' . $this->getName(), '');
    } // getDisplayDescription
    
    /**
    * Return config handler instance
    *
    * @param void
    * @return ConfigHandler
    */
    function getConfigHandler() {
      if($this->config_handler instanceof ConfigHandler) return $this->config_handler;
      
      $handler_class = trim($this->getConfigHandlerClass());
      if(!$handler_class) throw new Error('Handler class is not set for "' . $this->getName() . '" config option');
      
      $handler = new $handler_class();
      if(!($handler instanceof ConfigHandler)) throw new Error('Handler class for "' . $this->getName() . '" config option is not valid');
      
      $handler->setConfigOption($this);
      $handler->setRawValue($this->getUserValue(logged_user()->getId()));
      $this->config_handler = $handler;
      return $this->config_handler;
    } // getConfigHandler
  
    /**
     * Returns user value for the config option, or else default is returned
     *
     */
    function getUserValue($user_id = 0, $workspace_id = 0, $default = null){
    	$val = UserWsConfigOptionValues::findById(array('option_id' => $this->getId(), 'user_id'=>$user_id,'workspace_id' => $workspace_id));
    	if (is_null($val)){
    		if ($user_id == 0 || $workspace_id == 0){
    			//Return default settings
    			if (!is_null($default))
    				return $default;
    			else
    				return $this->getDefaultValue();
    		} else {
    			//Search user global preferences
    			$val = UserWsConfigOptionValues::findById(array('option_id' => $this->getId(), 'user_id'=>$user_id,'workspace_id' => 0));
    			if (!$val){
    				//Search workspace global preferences
    				$val = UserWsConfigOptionValues::findById(array('option_id' => $this->getId(), 'user_id'=>0,'workspace_id' => $workspace_id));
    				if (!$val){
    					//Return default settings
    					if (!is_null($default))
    						return $default;
    					else
    						return $this->getDefaultValue();
    				} // if
    			} // if
    		} // if
    	} // if
		return $val->getValue();
    }
    
    /**
     * Set value  
     *
     */
    function setUserValue($new_value, $user_id = 0, $workpace_id = 0){
    	$val = UserWsConfigOptionValues::findById(array('option_id' => $this->getId(), 'user_id' => $user_id, 'workspace_id' => $workpace_id));
		if(!$val){
			// if value was not found, create it
			$val = new UserWsConfigOptionValue();
			$val->setOptionId($this->getId());
			$val->setUserId($user_id);
			$val->setWorkspaceId($workpace_id);
		}
		$val->setValue($new_value);
		$val->save();
    }
    
    /**
    * Return config default value
    *
    * @access public
    * @param void
    * @return mixed
    */
    function getValue() {
      $handler = $this->getConfigHandler();
      $handler->setRawValue(parent::getDefaultValue());
      return $handler->getDefaultValue();
    } // getDefaultValue
    
    /**
    * Set option value
    *
    * @access public
    * @param mixed $value
    * @return boolean
    */
    function setValue($value) {
      $handler = $this->getConfigHandler();
      $handler->setValue($value);
      return parent::setValue($handler->getRawValue());
    } //  setDefaultValue
    
    /**
    * Render this control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
      $handler = $this->getConfigHandler();
      return $handler->render($control_name);
    } // render
    
  } // UserWsConfigOption 

?>