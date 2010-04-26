<?php

  /**
  * SearchableObjects, generated on Tue, 13 Jun 2006 12:15:44 +0200 by 
  * DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class SearchableObjects extends BaseSearchableObjects {
    
    /**
    * Search for specific search string ($search_for) in specific project
    *
    * @param string $search_for Search string
    * @param Project $project Search in this project
    * @param boolean $include_private
    * @return array
    */
    static function search($search_for, Project $project, $include_private = false) {
      return SearchableObjects::doSearch(SearchableObjects::getSearchConditions($search_for, $project->getId(), $include_private));
    } // search
    
    /**
    * Search paginated
    *
    * @param string $search_for Search string
    * @param Project $project Search in this project
    * @param boolean $include_private
    * @param integer $items_per_page
    * @param integer $current_page
    * @return array
    */
    static function searchPaginated($search_for, $project_csvs, $include_private = false, $items_per_page = 10, $current_page = 1) {
        $conditions = SearchableObjects::getSearchConditions($search_for, $project_csvs, $include_private);
	    $tagconditions = SearchableObjects::getTagSearchConditions($search_for, $project_csvs);
	    $pagination = new DataPagination(SearchableObjects::countUniqueObjects($conditions, $tagconditions), $items_per_page, $current_page);
	    $items = SearchableObjects::doSearch($conditions, $tagconditions, $pagination->getItemsPerPage(), $pagination->getLimitStart());
		 return array($items, $pagination);
    } // searchPaginated
    
    static function searchByType($search_for, $project_csvs, $object_type = '', $include_private = false, $items_per_page = 10, $current_page = 1, $columns_csv = null, $user_id = 0) {
        $remaining = 0;
        $safe_search_for = str_replace("'", '"', $search_for);
	    $conditions = SearchableObjects::getSearchConditions($safe_search_for, $project_csvs, true, $object_type, $columns_csv, $user_id);
	    $count = SearchableObjects::countUniqueObjects($conditions);
	    $pagination = new DataPagination($count, $items_per_page, $current_page);
	    if ($count > 0)
	    	$items = SearchableObjects::doSearch($conditions, $pagination->getItemsPerPage(), $pagination->getLimitStart());
	    else
	    	$items = array();
        return array($items, $pagination);
    } // searchPaginated
    
    /**
    * Prepare search conditions string based on input params
    *
    * @param string $search_for Search string
    * @param string $project_csvs Search in this project
    * @return array
    */
    function getSearchConditions($search_for, $project_csvs, $include_private = false, $object_type = '', $columns_csv = null, $user_id = 0) {
    	$otSearch = '';
    	$columnsSearch = '';
    	$wsSearch = '';
    	if (!is_null($columns_csv))
    		$columnsSearch = " AND `column_name` in (" . $columns_csv . ")";
    		
    	if ($object_type != '')
    		$otSearch = " AND `rel_object_manager` = '$object_type'";
    
    	if ($user_id > 0)
    		$wsSearch .= " AND (`user_id` = " . $user_id . " OR ";
    	else
    		$wsSearch .= " AND (";
    		
    	if ($object_type=="ProjectMessages" || $object_type == "ProjectFiles" || $object_type == "MailContents")
    		$wsSearch .= "`rel_object_id` IN (SELECT `object_id` FROM `".TABLE_PREFIX."workspace_objects` WHERE `object_manager` = '$object_type' && `workspace_id` IN ($project_csvs))";
    	else if ($object_type=="ProjectFileRevisions")
    		$wsSearch .=  "`rel_object_id` IN (SELECT o.id FROM " . TABLE_PREFIX ."project_file_revisions o where o.file_id IN (SELECT p.`object_id` FROM `".TABLE_PREFIX."workspace_objects` p WHERE p.`object_manager` = 'ProjectFiles' && p.`workspace_id` IN ($project_csvs)))";
    	else if ($object_type=="Contacts")
    		$wsSearch .=  " (`rel_object_id` IN (SELECT o.contact_id FROM " . TABLE_PREFIX ."project_contacts o where o.`project_id` IN ($project_csvs)) OR (SELECT COUNT(*) from " . TABLE_PREFIX ."project_contacts o where o.`project_id` IN ($project_csvs) AND o.`contact_id` = `rel_object_id`) = 0)";
    	else
    		$wsSearch .=  "`project_id` in ($project_csvs)";
    	$wsSearch .=  ')';
    	
    	
    	//Check for trashed and other permissions
    	$tableName = eval("return $object_type::instance()->getTableName();");
    	$trashed = '';
    	if ($object_type != 'Projects' && $object_type != 'Users'){
    		$trashed = " and EXISTS(SELECT * FROM $tableName co where `rel_object_id` = id and trashed_by_id = 0 ";
    		if ($object_type != 'ProjectFileRevisions' && $object_type != 'Contacts')
    			$trashed .= ' AND ( ' . permissions_sql_for_listings(eval("return $object_type::instance();"), ACCESS_LEVEL_READ, logged_user(), '`project_id`', '`co`') .')';
    		else if ($object_type == "Contacts"){
    			if (!can_manage_contacts(logged_user())){
					$pcTableName = "`" . TABLE_PREFIX . 'project_contacts`';
					$trashed .= " AND `co`.`id` IN ( SELECT `contact_id` FROM $pcTableName `pc` WHERE `pc`.`contact_id` = `co`.`id` AND (" . permissions_sql_for_listings(ProjectContacts::instance(), ACCESS_LEVEL_READ, logged_user(), '`project_id`', '`pc`') .'))';
    			}
    		} else if ($object_type == "ProjectFileRevisions"){
				$fileTableName = "`" . TABLE_PREFIX . 'project_files`';
    			$trashed = " and EXISTS(SELECT * FROM $tableName co where `co`.`id` IN ( SELECT `id` FROM $fileTableName `pf` WHERE `pf`.`id` = `co`.`file_id` AND (" . permissions_sql_for_listings(ProjectFiles::instance(), ACCESS_LEVEL_READ, logged_user(), '`project_id`', '`pf`') .'))';
    		}
    		$trashed .= ')';
    	}

    	// if search criteria is a mail address, remove its domain to avoid matching emails with same domain that are not from this address
    	$pos = strpos_utf($search_for, '@');
    	while ($pos !== FALSE) {
    		$esp = strpos_utf($search_for, ' ', $pos);
    		if ($esp !== FALSE) $search_for = substr_utf($search_for, 0, $pos) . ' ' . substr_utf($search_for, $esp+1);
    		else $search_for = substr_utf($search_for, 0, $pos);
    		$pos = strpos_utf($search_for, '@');
    	}
    	
    	if($include_private) {
    		if (substr(Localization::instance()->getLocale(),0,2) == 'zh')
    			return DB::prepareString('`content` LIKE \'%' . $search_for . '%\'' . $wsSearch . $trashed . $otSearch . $columnsSearch );
    		else
    			return DB::prepareString('MATCH (`content`) AGAINST (\'' . $search_for . '\' IN BOOLEAN MODE)'  . $wsSearch . $trashed . $otSearch . $columnsSearch );
    	} else {
    		if (substr(Localization::instance()->getLocale(),0,2) == 'zh')
    			return DB::prepareString('`content` LIKE \'%' . $search_for . '%\' AND `is_private` = 0' . $wsSearch . $trashed . $otSearch . $columnsSearch );
    		else
    			return DB::prepareString('MATCH (`content`) AGAINST (\'' . $search_for . '\' IN BOOLEAN MODE) AND `is_private` = 0' .$wsSearch . $trashed . $otSearch . $columnsSearch);
    	} // if
    } // getSearchConditions
    
    /** Prepare search conditions string based on input params
    *
    * @param string $search_for Search string
    * @param string $project_csvs Search in this project
    * @return array
   	*/
    function getTagSearchConditions($search_for, $project_csvs) {
      return DB::prepareString(" tag = '$search_for' ");
    } // getTagSearchConditions
    
    /**
    * Do the search
    *
    * @param string $conditions
    * @param integer $limit
    * @param integer $offset
    * @return array
    */
    function doSearch($conditions, $limit = null, $offset = null) {
      $table_name = SearchableObjects::instance()->getTableName(true);
      //$tags_table_name = Tags::instance()->getTableName();
      
      $limit_string = '';
      if((integer) $limit > 0) {
        $offset = (integer) $offset > 0 ? (integer) $offset : 0;
        $limit_string = " LIMIT $offset, $limit";
      } // if
      
      $where = '';
      if(trim($conditions) <> '') $where = "WHERE $conditions";
      
      $sql = "SELECT distinct `rel_object_manager`, `rel_object_id` FROM $table_name $where ORDER BY `rel_object_id` DESC $limit_string";
      $result = DB::executeAll($sql);
      if(!is_array($result)) return null;
      
      
      $new_where = "'1' = '2' ";
      foreach($result as $row) {
        $manager_class = array_var($row, 'rel_object_manager');
        $object_id = array_var($row, 'rel_object_id');
        $new_where .= " OR (rel_object_manager = '" . $manager_class ."' AND rel_object_id = '" . $object_id . "')";
      }
      $new_where = " AND (" . $new_where . ')';
      
      $sql = "SELECT `rel_object_manager`, `rel_object_id`, 'column_name' FROM $table_name $where $new_where ORDER BY `rel_object_id`";
      $result = DB::executeAll($sql);
      if(!is_array($result)) return null;
      
      $loaded = array();
      $objects = array();
      
      foreach($result as $row) {
        $manager_class = array_var($row, 'rel_object_manager');
        $object_id = array_var($row, 'rel_object_id');
        
        if(!isset($loaded[$manager_class . '-' . $object_id]) || !($loaded[$manager_class . '-' . $object_id])) {
          if(class_exists($manager_class)) {
            $object = get_object_by_manager_and_id($object_id, $manager_class);
            if($object instanceof ApplicationDataObject) {
              $loaded[$manager_class . '-' . $object_id] = true;
              $objects[] = array('object' => $object, 'column_name' => array(array_var($row, 'column_name')));
            } // if
          } // if
        } else {
        	for ($i = 0; $i < count($objects); $i++)
        		if ($objects[$i]['object'] instanceof ProjectDataObject && $objects[$i]['object']->getObjectId() == $object_id && get_class($objects[$i]['object']->getObjectManagerName()) == $manager_class)
        			$objects[$i]['column_name'][] = array_var($row, 'column_name');
        } // if
      } // foreach
      
      return count($objects) ? $objects : null;
    } // doSearch
    
    /**
    * Return number of unique objects
    *
    * @param string $conditions
    * @return integer
    */
    function countUniqueObjects($conditions) {
      $table_name = SearchableObjects::instance()->getTableName(true);
      //$tags_table_name = Tags::instance()->getTableName();
      $where = '';
      if(trim($conditions <> '')) $where = "WHERE $conditions";
      
      $sql = "SELECT distinct `rel_object_manager`, `rel_object_id` FROM $table_name $where";
      $result = DB::executeAll($sql);
      if(!is_array($result) || !count($result)) return 0;
      
      $counted = array();
      $counter = 0;
      foreach($result as $row) {
        if(!isset($counted[array_var($row, 'rel_object_manager') . array_var($row, 'rel_object_id')])) {
          $counted[array_var($row, 'rel_object_manager') . array_var($row, 'rel_object_id')] = true;
          $counter++;
        } // if
      } // foreach
      
      return $counter;
    } // countUniqueObjects
    
    /**
    * Drop all content from table related to $object
    *
    * @param ProjectDataObject $object
    * @return boolean
    */
    static function dropContentByObject(ApplicationDataObject $object) {
    	return SearchableObjects::delete(array('`rel_object_manager` = ? AND `rel_object_id` = ?', get_class($object->manager()), $object->getObjectId()));
    } // dropContentByObject
    
    /**
    * Drop column content from table related to $object
    *
    * @param ProjectDataObject $object
    * @return boolean
    */
    static function dropContentByObjectColumn(ApplicationDataObject $object, $column = '') {
    	return SearchableObjects::delete(array('`rel_object_manager` = ? AND `rel_object_id` = ? AND `column_name` = '. "'". $column . "'" , get_class($object->manager()), $object->getObjectId(), $column));
    } // dropContentByObject
    
    /**
    * Drop columns content from table related to $object
    *
    * @param ApplicationDataObject $object
    * @return boolean
    */
    static function dropContentByObjectColumns(ApplicationDataObject $object, $columns = array()) {
    	$columns_csv = "'" . implode("','",$columns) . "'";
    	
    	return SearchableObjects::delete(array('`rel_object_manager` = ? AND `rel_object_id` = ? AND `column_name` in ('. $columns_csv . ')' , get_class($object->manager()), $object->getObjectId()));
    } // dropContentByObject
    
  } // SearchableObjects 

?>