<?php

class GUIController extends ApplicationController {

	/**
	 * Construct the GUIController
	 *
	 * @access public
	 * @param void
	 * @return GUIController
	 */
	function __construct() {
		parent::__construct();
	} // __construct

	function save_state() {
		$this->setLayout("json");
		$this->setTemplate(get_template_path("json"));
		
		$data = $_POST['data'];
		if (!isset($data)) {
			$object = array("success" => true);
			tpl_assign("object", $object);
			return;
		}
		$array = json_decode($data);
		$query = "INSERT INTO `" . TABLE_PREFIX . "guistate` (`user_id`, `name`, `value`) VALUES ";
		$queryd = "DELETE FROM `" . TABLE_PREFIX . "guistate` WHERE `user_id` = " . logged_user()->getId() . " AND `name` IN (";
		$values = "";
		$names = "";
		$id = logged_user()->getId();
		foreach ($array as $a) {
			if ($values != "") {
				$values .= ",";
				$names .= ",";
			}
			$values .= "(" . $id . ",'" . mysql_real_escape_string($a->name) . "','" . mysql_real_escape_string($a->value) . "')";
			$names .= "'" . mysql_real_escape_string($a->name) . "'";
		}
		$query .= $values;
		$queryd .= $names . ")";
		try {
			DB::execute($queryd);
			DB::execute($query);
			$object = array("success" => true);
			tpl_assign("object", $object);
		} catch (Exception $e) {
			$object = array("success" => false);
			$object = array("message" => $e->getMessage());
			tpl_assign("object", $object);
		}
	}
	
	function read_state() {
		$this->setLayout("json");
		$this->setTemplate(get_template_path("json"));
		
		try {
			$data = self::getState();
			$object = array(
				"success" => true,
				"data" => json_encode($data)
			);
			tpl_assign("object", $object);
		} catch (Exception $e) {
			$object = array("success" => false);
			$object = array("message" => $e->getMessage());
			tpl_assign("object", $object);
		}
	}
	
	static function getState() {
		$query = "SELECT `name`, `value` FROM `" . TABLE_PREFIX . "guistate` WHERE `user_id` = " . mysql_real_escape_string(logged_user()->getId());
		$rows = DB::executeAll($query);
		$data = array();
		if ($rows) {
			foreach ($rows as $r) {
				$data[] = array(
					"name" => $r["name"],
					"value" => $r["value"]
				);
			}
		}
		return $data;
	}

} // GUIController

?>