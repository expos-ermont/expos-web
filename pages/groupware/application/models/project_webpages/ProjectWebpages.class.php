<?php

  /**
  * ProjectWebpages, generated on Wed, 15 Mar 2006 22:57:46 +0100 by 
  * DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class ProjectWebpages extends BaseProjectWebpages {
  
    /**
    * Return all webpages that are involved in specific project
    *
    * @access public
    * @param Project $project
    * @param string $additional_conditions
    * @return array
    */
    function getWebpagesByProject(Project $project, $additional_conditions = null) {
      ProjectWebpages::findAll(array('conditions' => '`project_id` = ' . $project->getId()));
    }
    
    function getWebpages($project_ids, $tag = '', $page = 1, $webpages_per_page = 10, $orderBy = 'title', $orderDir = 'ASC') {
    	$orderDir = strtoupper($orderDir);
    	if ($orderDir != "ASC" && $orderDir != "DESC") $orderDir = "ASC";
		if($page < 0) $page = 1;

		//$conditions = logged_user()->isMemberOfOwnerCompany() ? '' : ' `is_private` = 0';
		if ($tag == '' || $tag == null) {
			$tagstr = " '1' = '1'"; // dummy condition
		} else {
			$tagstr = "(select count(*) from " . TABLE_PREFIX . "tags where " .
			TABLE_PREFIX . "project_webpages.id = " . TABLE_PREFIX . "tags.rel_object_id and " .
			TABLE_PREFIX . "tags.tag = '".$tag."' and " . TABLE_PREFIX . "tags.rel_object_manager ='ProjectWebpages' ) > 0 ";
		}
		
		$permission_str = ' AND (' . permissions_sql_for_listings(ProjectWebpages::instance(),
							ACCESS_LEVEL_READ, 
							logged_user()) . ')';

		
		$project_str = " AND `project_id` IN ($project_ids) ";
		
		return ProjectWebpages::paginate(
			array("conditions" => $tagstr . $permission_str . $project_str ,
	        		'order' => DB::escapeField($orderBy)." $orderDir"),
			config_option('files_per_page', 10),
			$page
		); // paginate
    }
    
    
  } // ProjectWebpages 

?>