<?php

  /**
  *  ReadObjects, generated on Wed, 26 Jul 2006 11:18:14 +0200 by 
  * DataObject generation tool
  *
  * @author Nicolas Medeiros <nicolas@iugo.com.uy>
  */
  class  ReadObjects extends BaseReadObjects {
  
    /**
    * Return all unreaded objects ( ReadObjects) for specific object and user
    *
    * @param ProjectDataObject $object
    * @return array
    */
    static function getUnreadByObject(ApplicationDataObject $object, int $user_id) {
      return self::findAll(array(
        'conditions' => array('(`rel_object_manager` = ? and `rel_object_id` = ?) and `user_id` = ? and is_read = 0', 
        		get_class($object->manager()), $object->getObjectId(), $user_id),
        'order' => '`created_on`'
      )); // findAll
    } // getUnreadByObject
    
    /**
    * Return all readed objects ( ReadObjects) for specific object and user
    *
    * @param ProjectDataObject $object
    * @return array
    */
    static function getReadByObject(ApplicationDataObject $object, int $user_id) {
      return self::findAll(array(
        'conditions' => array('(`rel_object_manager` = ? and `rel_object_id` = ?) and `user_id` = ? and is_read = 1', 
        		get_class($object->manager()), $object->getObjectId(), $user_id),
        'order' => '`created_on`'
      )); // findAll
    } // getReadByObject
    
    
    /**
    * User has read object
    *
    * @param int $object_id
    * @param int $user_id
    * @return bool
    */
    static function userHasRead( $user_id, $object ) {
	  $perm = self::findOne(array(
        'conditions' => array('`user_id` = ? and `rel_object_id` = ? AND `rel_object_manager` = ?', $user_id, $object->getId(), get_class($object->manager()))
      )); // findAll
      return $perm!=null && $perm->getIsRead();
    } //  userCanRead    
    
  } // clearRelationsByObject

?>