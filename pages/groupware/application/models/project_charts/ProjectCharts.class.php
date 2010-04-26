<?php
  /* ProjectCharts
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
  class ProjectCharts extends BaseProjectCharts {
    
    /**
    * Return charts that belong to specific project
    *
    * @param Project $project
    * @return array
    */
    static function getProjectCharts(Project $project) {
      $conditions = array('`project_id` = ?', $project->getId());
      
      return self::findAll(array(
        'conditions' => $conditions,
        'order' => '`created_on` DESC',
      )); // findAll
    } // getProjectCharts
     
  } // ProjectCharts 
?>