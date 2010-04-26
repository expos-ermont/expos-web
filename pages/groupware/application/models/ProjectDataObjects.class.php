<?php

/**
 * Abstract class that implements methods that share all projectlists (find, paginate, trash, etc)
 *
 * Project objects is data manager with few extra functions
 *
 * @version 1.0
 * @author Ignacio de Soto <ignacio.desoto@gmail.com>
 */
abstract class ProjectDataObjects extends DataManager {
	private function check_include_trashed(& $arguments = null) {
		if (!array_var($arguments, 'include_trashed', false)) {
			$columns = $this->getColumns();
			if (array_search("trashed_by_id", $columns) != false) {
				$conditions = array_var($arguments, 'conditions', '');
				if (is_array($conditions)) {
					$conditions[0] = "`trashed_by_id` = 0 AND (".$conditions[0].")";
				} else if ($conditions != '') {
					$conditions = "`trashed_by_id` = 0 AND ($conditions)";
				} else {
					$conditions = "`trashed_by_id` = 0";
				}
				$arguments['conditions'] = $conditions;
			}
		}
	}
	
	function find($arguments = null) {
		$this->check_include_trashed($arguments);
		return parent::find($arguments);
	}
	
	function paginate($arguments = null, $items_per_page = 10, $current_page = 1) {
		$this->check_include_trashed($arguments);
		return parent::paginate($arguments, $items_per_page, $current_page);
	}
	
}

?>