<?php

  /**
  * Companies, generated on Sat, 25 Feb 2006 17:37:12 +0100 by 
  * DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class Companies extends BaseCompanies {
    
    /**
    * Return all registered companies
    *
    * @param void
    * @return array
    */
    static function getAll() {
      return Companies::findAll(array(
        'order' => '`client_of_id`'
      )); // findAll
    } // getAll
    
    static function getVisibleCompanies(User $user){
    	if (can_manage_contacts($user)){
    		return self::getAll();
    	} else {
    		return self::getCompaniesByProjects($user->getActiveProjectIdsCSV());
    	}
    }
    
    /**
    * Return all companies that are on specific projects, determined by a CVS list of project ids.
    *
    * @access public
    * @param string $projects_csv CSV list of projects
    * @param string $additional_conditions Additional SQL conditions
    * @param bool $include_owner Include the owner company
    * @return array Array of Companies
    */
    static function getCompaniesByProjects($projects_csv, $additional_conditions = null, $include_owner = true) {
    	$companies = array();
    	$companies_table = self::instance()->getTableName(true);
    	$project_objects_table=  WorkspaceObjects::instance()->getTableName(true);

		// Restrict result only on owner company
    	$ownerCond = '';
    	if (!$include_owner){
    		$owner_id = owner_company()->getId();
    		$ownerCond = "$companies_table.`client_of_id` = '$owner_id' AND ";
    	}
    	
    	$sql = "SELECT DISTINCT $companies_table.* FROM $companies_table, $project_objects_table WHERE $ownerCond ($companies_table.`id` = $project_objects_table.`object_id` AND $project_objects_table.`object_manager` = 'Companies' AND $project_objects_table.`workspace_id` IN ( " . $projects_csv . '))';
    	if(trim($additional_conditions) <> '') $sql .= " AND ($additional_conditions) ORDER BY $companies_table.`name`";

    	$rows = DB::executeAll($sql);
    	if(is_array($rows)) {
    		foreach($rows as $row) {
    			$companies[] = Companies::instance()->loadFromRow($row);
    		} // foreach
    	} // if

    	return count($companies) ? $companies : null;
    } // getCompaniesByProjects
    
    /**
    * Return all companies that have system users
    *
    * @param void
    * @return array
    */
    static function getCompaniesWithUsers() {
      $user_table =  Users::instance()->getTableName();
      $companies_table =  Companies::instance()->getTableName();
      return Companies::findAll(array(
        'conditions' => array(" exists (select id from $user_table where $user_table.`company_id` = $companies_table.`id` )"),
        'order' => '`client_of_id`'
      )); // findAll
    } // getCompaniesWithUsers
  
    /**
    * Return owner company
    *
    * @access public
    * @param void
    * @return Company
    */
    static function getOwnerCompany() {
      return Companies::findOne(array(
        'conditions' => array('`client_of_id` = ?', 0)
      )); // findOne
    } // getOwnerCompany
    
    /**
    * Return company clients
    *
    * @param Company $company
    * @return array
    */
    static function getCompanyClients(Company $company) {
      return Companies::findAll(array(
        'conditions' => array('`client_of_id` = ?', $company->getId()),
        'order' => '`name`'
      )); // array
    } // getCompanyClients
    
    
    static function getCompanyFieldNames() {
		return array('company[name]' => lang('name'),
			'company[address]' => lang('address'),
			'company[address2]' => lang('address2'),
			'company[city]' => lang('city'),
			'company[state]' => lang('state'),
			'company[zipcode]' => lang('zipcode'),
			'company[country]' => lang('country'),
			'company[phone_number]' => lang('phone'),
			'company[fax_number]' => lang('fax'),
			'company[email]' => lang('email address'),
			'company[homepage]' => lang('homepage'),
		);
    }
  } // Companies

?>