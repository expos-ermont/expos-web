<?php

  /**
  * Projects, generated on Sun, 26 Feb 2006 23:10:34 +0100 by 
  * DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class Projects extends BaseProjects {
    
    /**
    * This constants are used for retriving project data, to see how to order results
    */
    const ORDER_BY_DATE_CREATED = 'created_on';
    const ORDER_BY_NAME = 'name';
    
    /**
    * Return all projects
    *
    * @param void
    * @return array
    */
    static function getAll($order_by = self::ORDER_BY_NAME) {
      return Projects::findAll(array(
        'order' => $order_by
      )); // findAll
    } // getAll
    
    /**
    * Return all active project from the database
    *
    * @param string $order_by
    * @return null
    */
    static function getActiveProjects($order_by = self::ORDER_BY_NAME) {
      return self::findAll(array(
        'conditions' => array('`completed_on` = ?', EMPTY_DATETIME),
        'order' => $order_by,
      )); // findAll
    } // getActiveProjects
    
    /**
     * Returns the workspaces that have no parent.
     * @return array
     *
     */
    static function getTopWorkspaces() {
    	return Projects::findAll(array('conditions' => array('parent_id = ?', 0))); 
    }
    
    /**
    * Return finished projects
    *
    * @param string $order_by
    * @return array
    */
    static function getFinishedProjects($order_by = self::ORDER_BY_NAME) {
      return self::findAll(array(
        'conditions' => array('`completed_on` > ?', EMPTY_DATETIME),
        'order' => $order_by,
      )); // findAll
    } // getFinishedProjects
    
    /** Return project by name.
	*
	* @param name
	* @return array
	*/
	static function getByName($name) {
		$conditions = array('`name` = ?', $filename);
		
		return self::findOne(array(
			'conditions' => $conditions
		));
	} // getByFilename
	
	/**
	 * Receives comma seperated ids and returns the workspaces with those ids
	 *
	 * @param string $csv
	 * @return array
	 */
	static function findByCSVIds($csv) {
		$ids = split(",", $csv);
		if (!is_array($ids)) return array();
		$ws = array();
		foreach ($ids as $id) {
			$w = Projects::findById($id);
			if ($w instanceof Project) {
				$ws[] = $w;
			}
		}
		return $ws;
	}
    
    /**
    * Return all projects as tree view
    *
    * @access public
    * @param User $user
    * @param 
    * @return array
    */
    function getProjectsByParent(User $user, $additional_conditions = null) {
      	$projects_table = Projects::instance()->getTableName(true);
    	$all = self::getActiveProjects(/*"$projects_table.`parent_id`, $projects_table.`name`"*/);
	    if(is_array($all)) {
	        foreach($all as $proj) {
	          	$projects[$proj->getParentId()] []= $proj;
	        } // foreach
	    } // if
      
      return count($projects) ? $projects : null;
    } // getProjectsByUser
  } // Projects 

?>