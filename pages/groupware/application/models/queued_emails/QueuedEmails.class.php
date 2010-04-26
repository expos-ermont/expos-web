<?php

/**
 * QueuedEmails class
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class QueuedEmails extends BaseQueuedEmails {

	/**
	 * Returns all queued emails younger than the given date
	 * and deletes all emails.
	 * @return array
	 */
	static function getQueuedEmails($date = null) {
		DB::beginWork();
		if ($date instanceof DateTimeValue) {
			$emails = self::findAll(array(
				'condition' => array(
					'`timestamp` < ?',
					$date
				)
			));
		} else {
			$emails = self::findAll();
		}
		self::delete();
		DB::commit();
		return $emails;
	}

} // QueuedEmails

?>