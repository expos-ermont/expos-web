<?php

/**
 * ProjectUser class
 * Generated on Wed, 15 Mar 2006 22:57:46 +0100 by DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ProjectUser extends BaseProjectUser {

	/**
	 * Sets all permissions to a value.
	 *
	 * @param boolean $value
	 */
	function setAllPermissions($value) {
		$this->setCanReadMessages($value);
		$this->setCanReadTasks($value);
		$this->setCanReadWeblinks($value);
		$this->setCanReadMilestones($value);
		$this->setCanReadMails($value);
		$this->setCanReadContacts($value);
		$this->setCanReadComments($value);
		$this->setCanReadFiles($value);
		$this->setCanReadEvents($value);
		$this->setCanWriteMessages($value);
		$this->setCanWriteTasks($value);
		$this->setCanWriteWeblinks($value);
		$this->setCanWriteMilestones($value);
		$this->setCanWriteMails($value);
		$this->setCanWriteContacts($value);
		$this->setCanWriteComments($value);
		$this->setCanWriteFiles($value);
		$this->setCanWriteEvents($value);
		$this->setCanAssignToOwners($value);
		$this->setCanAssignToOther($value);
	 } // setAllPermissions
	 
	 function setRadioPermissions($radio_array){
	 	$this->setCanReadMessages($radio_array[0]>=1);
		$this->setCanReadTasks($radio_array[1]>=1);
		$this->setCanReadMilestones($radio_array[2]>=1);
		$this->setCanReadMails($radio_array[3]>=1);
		$this->setCanReadComments($radio_array[4]>=1);
		$this->setCanReadContacts($radio_array[5]>=1);
		$this->setCanReadWeblinks($radio_array[6]>=1);
		$this->setCanReadFiles($radio_array[7]>=1);
		$this->setCanReadEvents($radio_array[8]>=1);
		
		$this->setCanWriteMessages($radio_array[0] == 2);
		$this->setCanWriteTasks($radio_array[1] == 2);
		$this->setCanWriteMilestones($radio_array[2] == 2);
		$this->setCanWriteMails($radio_array[3] == 2);
		$this->setCanWriteComments($radio_array[4] == 2);
		$this->setCanWriteContacts($radio_array[5] == 2);
		$this->setCanWriteWeblinks($radio_array[6] == 2);
		$this->setCanWriteFiles($radio_array[7] == 2);
		$this->setCanWriteEvents($radio_array[8] == 2);
	 }
	 
	 function setCheckboxPermissions($checkbox_array){
	 	$this->setCanAssignToOwners($checkbox_array[0] == 1);
		$this->setCanAssignToOther($checkbox_array[1] == 1);
	 }
	 
	 /**
	  * Returns false is the user has no permissions in the workspace
	  * Return true in the user has any permission, from the radio or checkbox array
	  *
	  * @param unknown_type $radio_array
	  * @param unknown_type $checkbox_array
	  * @return unknown
	  */
	 static function hasAnyPermissions($radio_array,$checkbox_array){
	 	if(is_array($radio_array)){
		 	foreach ($radio_array as $elem){
		 		if($elem != 0)
		 			return true;
		 	}
	 	}
	 	if(is_array($checkbox_array)){
		 	foreach ($checkbox_array as $elem){
		 		if($elem != 0)
		 			return true;
		 	}
	 	}
	 	return false;
	 }
} // ProjectUser

?>