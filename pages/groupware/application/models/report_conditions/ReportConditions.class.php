<?php

/**
 *   ReportConditions class
 *
 * @author Pablo Kamil <pablokam@gmail.com>
 */

class ReportConditions extends BaseReportConditions {

	/**
	 * Return specific condition
	 *
	 * @param $id
	 * @return ReportCondition
	 */
	static function getCondition($id) {
		return self::findOne(array(
			'conditions' => array("`id` = ?", $id)
		)); // findOne
	} //  getCondition
	
	/**
	 * Return all report conditions
	 *
	 * @param report_id
	 * @return array
	 */
	static function getAllReportConditions($report_id) {
		return self::findAll(array(
			'conditions' => array("`report_id` = ?", $report_id)
		)); // findAll
	} //  getAllReportConditions
	
	/**
	 * Return all report conditions for fields
	 *
	 * @param report_id
	 * @return array
	 */
	static function getAllReportConditionsForFields($report_id) {
		return self::findAll(array(
			'conditions' => array("`report_id` = ? AND field_name != '' AND custom_property_id = 0", $report_id)
		)); // findAll
	} //  getAllReportConditionsForFields
	
	/**
	 * Return all report conditions for fields
	 *
	 * @param report_id
	 * @return array
	 */
	static function getAllReportConditionsForCustomProperties($report_id) {
		return self::findAll(array(
			'conditions' => array("`report_id` = ? AND custom_property_id > 0", $report_id)
		)); // findAll
	} //  getAllReportConditionsForFields
	
} // ReportConditions

?>