<?php

/**
 * ProjectMessages, generated on Sat, 04 Mar 2006 12:21:44 +0100 by
 * DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ProjectMessages extends BaseProjectMessages {

	function __construct() {
		parent::__construct();
	}

	private static function getWorkspaceString(){
		return '`id` IN (SELECT `object_id` FROM `'.TABLE_PREFIX.'workspace_objects` WHERE `object_manager` = \'ProjectMessages\' AND `workspace_id` = ?)';
	}
	 
	/**
	 * Return messages that belong to specific project
	 *
	 * @param Project $project
	 * @param boolean $include_private Include private messages in the result
	 * @return array
	 */
	static function getProjectMessages(Project $project, $include_private = false) {
		$condstr = self::getWorkspaceString();
		if (is_null($condstr) || $condstr == '')
			$condstr = '`id` IN (SELECT `object_id` FROM `'.TABLE_PREFIX.'workspace_objects` WHERE `object_manager` = \'ProjectMessages\' AND `workspace_id` = ?)';
		
		if ($include_private) {
			$conditions = array($condstr, $project->getId());
		} else {
			$conditions = array($condstr . ' AND `is_private` = ?', $project->getId(), false);
		} // if

		return self::findAll(array(
			'conditions' => $conditions,
			'order' => '`created_on` DESC',
		)); // findAll
	} // getProjectMessages

	/**
	 * Return project messages that are marked as important for specific project
	 *
	 * @param Project $project
	 * @param boolean $include_private Include private messages
	 * @return array
	 */
	static function getImportantProjectMessages(Project $project, $include_private = false) {
		$condstr = self::getWorkspaceString();
		if($include_private) {
			$conditions = array($condstr . ' AND `is_important` = ?', $project->getId(), true);
		} else {
			$conditions = array($condstr . ' AND `is_important` = ? AND `is_private` = ?', $project->getId(), true, false);
		} // if

		return self::findAll(array(
	        'conditions' => $conditions,
	        'order' => '`created_on` DESC',
		)); // findAll
	} // getImportantProjectMessages

} // ProjectMessages

?>