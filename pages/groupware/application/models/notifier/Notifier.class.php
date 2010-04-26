<?php

/**
 * Notifier class has purpose of sending various notification to users. Primary
 * notification method is email
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class Notifier {

	/** Supported transports **/
	const MAIL_TRANSPORT_MAIL = 'mail()';
	const MAIL_TRANSPORT_SMTP = 'smtp';

	/** Secure connection values **/
	const SMTP_SECURE_CONNECTION_NO  = 'no';
	const SMTP_SECURE_CONNECTION_SSL = 'ssl';
	const SMTP_SECURE_CONNECTION_TLS = 'tls';

	/**
	 * Cached value of echange compatible config option
	 *
	 * @var boolean
	 */
	static public $exchange_compatible = null;

	function notifyAction($object, $action, $log_data) {
		if (!$object instanceof ProjectDataObject) {
			return;
		}
		$subscribers = $object->getSubscribers();
		if (!is_array($subscribers) || count($subscribers) == 0) return;
		if ($action == ApplicationLogs::ACTION_ADD) {
			if ($object instanceof Comment) {
				self::newObjectComment($object);
			} else {
				self::objectNotification($object, $subscribers, $object->getCreatedBy(), 'new');
			}
		} else if ($action == ApplicationLogs::ACTION_EDIT) {
			self::objectNotification($object, $subscribers, $object->getUpdatedBy(), 'modified');
		} else if ($action == ApplicationLogs::ACTION_TRASH) {
			self::objectNotification($object, $subscribers, Users::findById($object->getTrashedById()), 'deleted');
		} else if ($action == ApplicationLogs::ACTION_CLOSE) {
			self::objectNotification($object, $subscribers, $object->getCompletedBy(), 'closed');
		}
	}
	function shareObject(ProjectDataObject $object, $people) {
		self::objectNotification($object, $people, logged_user(), 'share');
	}
	
	static function objectNotification($object, $people, $sender, $notification, $description = null, $descArgs = null, $properties = array()) {
		if(!is_array($people) || !count($people) || !$sender instanceof User) {
			return; // nothing here...
		} // if
		
		$type = $object->getObjectTypeName();
		$typename = lang($object->getObjectTypeName());
		$uid = $object->getUniqueObjectId();
		$name = $object instanceof Comment ? $object->getObject()->getObjectName() : $object->getObjectName();
		if (!isset($description)) {
			$description = "$notification notification $type desc";
			$descArgs = array($object->getObjectName(), $sender->getDisplayName());
		}
		if (!isset($descArgs)) {
			$descArgs = array();
		}
		if ($object->columnExists('text')) {
			$text = "\r\n" . $object->getColumnValue('text');
			$text = str_replace("\r\n", "\n", $text);
			$text = str_replace("\r", "\n", $text);
			$text = str_replace("\n", "\r\n>", $text);
			$properties['text'] = $text;
		}
		$properties['view '.$type] = str_replace('&amp;', '&', $object->getViewUrl());
		$properties['unique id'] = $uid;
		if ($object->columnExists('description')) {
			$text = "\r\n" . $object->getColumnValue('description');
			$text = str_replace("\r\n", "\n", $text);
			$text = str_replace("\r", "\n", $text);
			$text = str_replace("\n", "\r\n>", $text);
			$properties['description'] = $text;
		}
				
		tpl_assign('object', $object);
		tpl_assign('properties', $properties);
		
		$emails = array();
		foreach($people as $user) {
			if ($user->getId() != $sender->getId()) {
				// send notification on user's locale and with user info
				$locale = $user->getLocale();
				Localization::instance()->loadSettings($locale, ROOT . '/language');
				$workspaces = implode(", ", $object->getUserWorkspacePaths($user));
				$properties['workspace'] = $workspaces;
				tpl_assign('properties', $properties);
				tpl_assign('description', langA($description, $descArgs));
				$from = self::prepareEmailAddress($sender->getEmail(), $sender->getDisplayName());
				$emails[] = array(
					"to" => array(self::prepareEmailAddress($user->getEmail(), $user->getDisplayName())),
					"from" => self::prepareEmailAddress($sender->getEmail(), $sender->getDisplayName()),
					"subject" => $subject = lang("$notification notification $type", $name, $uid, $typename, $workspaces),
					"body" => tpl_fetch(get_template_path('general', 'notifier'))
				);
			}
		} // foreach
		$locale = logged_user() instanceof User ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
		self::queueEmails($emails);
	}
		
	/**
	 * Send new comment notification to message subscribers
	 *
	 * @param Comment $comment
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	static function newObjectComment(Comment $comment, $all_subscribers) {
		$object = $comment->getObject();
		$subscribers = array();
		foreach($all_subscribers as $subscriber) {
			if ($comment->isPrivate()) {
				if ($subscriber->isMemberOfOwnerCompany()) {
					$subscribers[] = $subscriber;
				} // if
			} else {
				$subscribers[] = $subscriber;
			} // of
		} // foreach
		self::objectNotification($comment, $subscribers, $comment->getCreatedBy(), 'new', "new comment posted", array($object->getObjectName()), $properties);
	} // newObjectComment
	
	/**
	 * Reset password and send forgot password email to the user
	 *
	 * @param User $user
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	static function forgotPassword(User $user) {
		$administrator = owner_company()->getCreatedBy();

		$new_password = $user->resetPassword(true);
		tpl_assign('user', $user);
		tpl_assign('new_password', $new_password);
		
		if (! $administrator instanceof User) return;

		// send email in user's language
		$locale = $user->getLocale();
		Localization::instance()->loadSettings($locale, ROOT . '/language');
		
		self::queueEmail(
			self::prepareEmailAddress($user->getEmail(), $user->getDisplayName()),
			self::prepareEmailAddress($administrator->getEmail(), $administrator->getDisplayName()),
			lang('your password'),
			tpl_fetch(get_template_path('forgot_password', 'notifier'))
		); // send
		$locale = logged_user() instanceof User ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
	} // forgotPassword
	
	/**
	 * Send password expiration notification email to user 
	 *
	 * @param User $user
	 * @param string $expiration_days
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	static function passwordExpiration(User $user, $expiration_days) {
		tpl_assign('user', $user);
		tpl_assign('exp_days', $expiration_days);

		if (! $user instanceof User) return;
		
		$locale = $user->getLocale();
		Localization::instance()->loadSettings($locale, ROOT . '/language');
		
		self::queueEmail(
			self::prepareEmailAddress($user->getEmail(), $user->getDisplayName()),
			self::prepareEmailAddress("noreply@opengoo.org", "noreply@opengoo.org"),
			lang('password expiration reminder'),
			tpl_fetch(get_template_path('password_expiration_reminder', 'notifier'))
		); // send
		
		$locale = logged_user() instanceof User ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
	} // passwordExpiration

	/**
	 * Send new account notification email to the user whose accout has been created
	 * (welcome message)
	 *
	 * @param User $user
	 * @param string $raw_password
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	static function newUserAccount(User $user, $raw_password) {
		tpl_assign('new_account', $user);
		tpl_assign('raw_password', $raw_password);

		if (! $user->getCreatedBy() instanceof User) return;
		
		$locale = $user->getLocale();
		Localization::instance()->loadSettings($locale, ROOT . '/language');
		
		self::queueEmail(
			self::prepareEmailAddress($user->getEmail(), $user->getDisplayName()),
			self::prepareEmailAddress($user->getCreatedBy()->getEmail(), $user->getCreatedByDisplayName()),
			lang('your account created'),
			tpl_fetch(get_template_path('new_account', 'notifier'))
		); // send
		
		$locale = logged_user() instanceof User ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
	} // newUserAccount


	/**
	 * Send task due notification to the list of users ($people)
	 *
	 * @param ProjectTask $task Due task
	 * @param array $people
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	static function objectReminder(ObjectReminder $reminder) {
		$object = $reminder->getObject();
		$context = $reminder->getContext();
		$type = $object->getObjectTypeName();
		$date = $object->getColumnValue($context);
		if ($reminder->getUserId() == 0) {
			$people = $object->getSubscribers();
		} else {
			$people = array($reminder->getUser());
		}
		Env::useHelper("format");

		self::objectNotification($object, $people, $object->getCreatedBy(), "$context reminder", "$context $type reminder desc", array($object->getObjectName(), $date->format("Y/m/d H:i:s")));
	} // taskDue
	
	/**
	 * Send event notification to the list of users ($people)
	 *
	 * @param ProjectEvent $event Event
	 * @param array $people
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	static function notifEvent(ProjectEvent $object, $people, $notification, $sender) {
		if(!is_array($people) || !count($people) || !$sender instanceof User) {
			return; // nothing here...
		} // if

		$uid = $object->getUniqueObjectId();
		$name = $object->getObjectName();
		$type = $object->getObjectTypeName();
		$typename = lang($object->getObjectTypeName());
		$description = lang("$notification notification event desc", $object->getObjectName(), $sender->getDisplayName());
		
		$properties['unique id'] = $uid;
		$properties['view event'] = str_replace('&amp;', '&', $object->getViewUrl());
				
		tpl_assign('object', $object);
		tpl_assign('description', $description);
		tpl_assign('properties', $properties);
		
		$emails = array();
		foreach($people as $user) {
			if ($user->getId() != $sender->getId()) {
				// send notification on user's locale and with user info
				$locale = $user->getLocale();
				Localization::instance()->loadSettings($locale, ROOT . '/language');
				$workspaces = implode(", ", $object->getUserWorkspaceNames($user));
				$properties['workspace'] = $workspaces;
				$properties['date'] = Localization::instance()->formatDescriptiveDate($object->getStart(), $user->getTimezone());
				tpl_assign('properties', $properties);
				$from = self::prepareEmailAddress($sender->getEmail(), $sender->getDisplayName());
				$emails[] = array(
					"to" => array(self::prepareEmailAddress($user->getEmail(), $user->getDisplayName())),
					"from" => self::prepareEmailAddress($sender->getEmail(), $sender->getDisplayName()),
					"subject" => $subject = lang("$notification notification $type", $name, $uid, $typename, $workspaces),
					"body" => tpl_fetch(get_template_path('general', 'notifier'))
				);
			}
		} // foreach
		$locale = logged_user() instanceof User ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
		self::queueEmails($emails);
	} // notifEvent
	
	 /** Send event notification to the list of users ($people)
	 *
	 * @param ProjectEvent $event Event
	 * @param array $people
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	static function notifEventAssistance(ProjectEvent $event, EventInvitation $invitation, $from_user) {
		if ((!$event instanceof ProjectEvent) || (!$invitation instanceof EventInvitation) 
			|| (!$event->getCreatedBy() instanceof User) || (!$from_user instanceof User)) {
			return;
		}
		
		tpl_assign('event', $event);
		tpl_assign('invitation', $invitation);
		tpl_assign('from_user', $from_user);
		
		$people = array($event->getCreatedBy());
		$recepients = array();
		foreach($people as $user) {
			$locale = $user->getLocale();
			Localization::instance()->loadSettings($locale, ROOT . '/language');
			$date = Localization::instance()->formatDescriptiveDate($event->getStart(), $user->getTimezone());
			tpl_assign('date', $date);
			self::queueEmail(
				array(self::prepareEmailAddress($user->getEmail(), $user->getDisplayName())),
				self::prepareEmailAddress($from_user->getEmail(), $from_user->getDisplayName()),
				$event->getProject()->getName() . ' - ' . lang('event invitation response') . ': ' . $event->getSubject(),
				tpl_fetch(get_template_path('event_inv_response_notif', 'notifier'))
			); // send
		} // foreach
		
		$locale = logged_user() instanceof User ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
	} // notifEvent

	// ---------------------------------------------------
	//  Milestone
	// ---------------------------------------------------

	/**
	 * Milestone has been assigned to the user
	 *
	 * @param ProjectMilestone $milestone
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	function milestoneAssigned(ProjectMilestone $milestone) {
		if($milestone->isCompleted()) {
			return true; // milestone has been already completed...
		} // if
		if(!($milestone->getAssignedTo() instanceof User)) {
			return true; // not assigned to user
		} // if

		tpl_assign('milestone_assigned', $milestone);

		if (! $milestone->getCreatedBy() instanceof User) return;
		
		$locale = $milestone->getAssignedTo()->getLocale();
		Localization::instance()->loadSettings($locale, ROOT . '/language');
		if ($milestone->getDueDate() instanceof DateTimeValue) {
			$date = Localization::instance()->formatDescriptiveDate($milestone->getDueDate(), $milestone->getAssignedTo()->getTimezone());
			tpl_assign('date', $date);
		}
		
		return self::queueEmail(
			self::prepareEmailAddress($milestone->getAssignedTo()->getEmail(), $milestone->getAssignedTo()->getDisplayName()),
			self::prepareEmailAddress($milestone->getCreatedBy()->getEmail(), $milestone->getCreatedByDisplayName()),
			lang('milestone assigned to you'),
			tpl_fetch(get_template_path('milestone_assigned', 'notifier'))
		); // send
		
		$locale = logged_user() instanceof User ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
	} // milestoneAssigned

	/**
	 * Task has been assigned to the user
	 *
	 * @param ProjectTask $task
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	function taskAssigned(ProjectTask $task) {
		if($task->isCompleted()) {
			return true; // task has been already completed...
		} // if
		if(!($task->getAssignedTo() instanceof User)) {
			return true; // not assigned to user
		} // if

		tpl_assign('task_assigned', $task);

		$locale = $task->getAssignedTo()->getLocale();
		Localization::instance()->loadSettings($locale, ROOT . '/language');
		
		if ($task->getDueDate() instanceof DateTimeValue) {
			$date = Localization::instance()->formatDescriptiveDate($task->getDueDate(), $task->getAssignedTo()->getTimezone());
			tpl_assign('date', $date);
		}
				
		self::queueEmail(
			array(self::prepareEmailAddress($task->getAssignedTo()->getEmail(), $task->getAssignedTo()->getDisplayName())),
			self::prepareEmailAddress($task->getUpdatedBy()->getEmail(), $task->getUpdatedByDisplayName()),
			lang('task assigned to you'),
			tpl_fetch(get_template_path('task_assigned', 'notifier'))
		); // send
		
		$locale = logged_user() instanceof User ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
	} // taskAssigned



	// ---------------------------------------------------
	//  Util functions
	// ---------------------------------------------------

	/**
	 * This function will prepare email address. It will return $name <$email> if both
	 * params are presend and we are not in exchange compatibility mode. In other case
	 * it will just return email
	 *
	 * @param string $email
	 * @param string $name
	 * @return string
	 */
	static function prepareEmailAddress($email, $name = null) {
		if(trim($name) && !self::getExchangeCompatible()) {
			return trim($name) . ' <' . trim($email) . '>';
		} else {
			return trim($email);
		} // if
	} // prepareEmailAddress

	/**
	 * Returns true if exchange compatible config option is set to true
	 *
	 * @param void
	 * @return boolean
	 */
	static function getExchangeCompatible() {
		if(is_null(self::$exchange_compatible)) {
			self::$exchange_compatible = config_option('exchange_compatible', false);
		} // if
		return self::$exchange_compatible;
	} // getExchangeCompatible

	/**
	 * Send an email using Swift (send commands)
	 *
	 * @param string to_address
	 * @param string from_address
	 * @param string subject
	 * @param string body, optional
	 * @param string content-type,optional
	 * @param string content-transfer-encoding,optional
	 * @return bool successful
	 */
	static function sendEmail($to, $from, $subject, $body = false, $type = 'text/plain', $encoding = '8bit') {
		Env::useLibrary('swift');

		$mailer = self::getMailer();
		if(!($mailer instanceof Swift)) {
			throw new NotifierConnectionError();
		} // if

		if (config_option("mail_transport", self::MAIL_TRANSPORT_MAIL) == self::MAIL_TRANSPORT_SMTP &&
				config_option("smtp_authenticate", false)) {
			$from = self::prepareEmailAddress(config_option("smtp_username", $from), $from);
		}
		$result = $mailer->send($to, $from, $subject, $body, $type, $encoding);
		$mailer->close();

		return $result;
	} // sendEmail
	
	static function queueEmail($to, $from, $subject, $body = false, $type = 'text/plain', $encoding = '8bit') {
		$cron = CronEvents::getByName('send_notifications_through_cron');
		if ($cron instanceof CronEvent && $cron->getEnabled()) {
			$qm = new QueuedEmail();
			$qm->setTo(implode(";", $to));
			$qm->setFrom($from);
			$qm->setSubject($subject);
			$qm->setBody($body);
			$qm->save();
		} else {
			self::sendEmail($to, $from, $subject, $body, $type, $encoding);
		}
	}
	
	static function queueEmails($emails) {
		foreach ($emails as $email) {
			self::queueEmail(
				array_var($email, 'to'),
				array_var($email, 'from'),
				array_var($email, 'subject'),
				array_var($email, 'body'),
				array_var($email, 'type', 'text/plain'),
				array_var($email, 'encoding', '8bit')
			);
		}
	}
	
	static function sendQueuedEmails() {
		$now = DateTimeValueLib::now();
		$date = DateTimeValueLib::now();
		$date->add("d", -2);
		$emails = QueuedEmails::getQueuedEmails($date);
		if (count($emails) <= 0) return;
		
		Env::useLibrary('swift');
		$mailer = self::getMailer();
		if(!($mailer instanceof Swift)) {
			throw new NotifierConnectionError();
		} // if
		$fromSMTP = config_option("mail_transport", self::MAIL_TRANSPORT_MAIL) == self::MAIL_TRANSPORT_SMTP && config_option("smtp_authenticate", false);
		$count = 0;
		foreach ($emails as $email) {
			try {
				$result = $mailer->send(
					explode(";", $email->getTo()),
					$fromSMTP ? self::prepareEmailAddress(config_option("smtp_username"), $email->getFrom()) : $email->getFrom(),
					$email->getSubject(),
					$email->getBody(),
					'text/plain',
					'8bit'
				);
				$count++;
			} catch (Exception $e) {
			}
		}
		$mailer->close();
		return $count;
	}

	/**
	 * This function will return SMTP connection. It will try to load options from
	 * config and if it fails it will use settings from php.ini
	 *
	 * @param void
	 * @return Swift
	 */
	static function getMailer() {
		$mail_transport_config = config_option('mail_transport', self::MAIL_TRANSPORT_MAIL);

		// Emulate mail() - use NativeMail
		if($mail_transport_config == self::MAIL_TRANSPORT_MAIL) {
			$mailer = new Swift(new Swift_Connection_NativeMail());
			return $mailer->isConnected() ? $mailer : null;

			// Use SMTP server
		} elseif($mail_transport_config == self::MAIL_TRANSPORT_SMTP) {

			// Load SMTP config
			$smtp_server = config_option('smtp_server');
			$smtp_port = config_option('smtp_port', 25);
			$smtp_secure_connection = config_option('smtp_secure_connection', self::SMTP_SECURE_CONNECTION_NO);
			$smtp_authenticate = config_option('smtp_authenticate', false);
			if($smtp_authenticate) {
				$smtp_username = config_option('smtp_username');
				$smtp_password = config_option('smtp_password');
			} // if

			switch($smtp_secure_connection) {
				case self::SMTP_SECURE_CONNECTION_SSL:
					$transport = SWIFT_SSL;
					break;
				case self::SMTP_SECURE_CONNECTION_TLS:
					$transport = SWIFT_TLS;
					break;
				default:
					$transport = SWIFT_OPEN;
			} // switch

			$mailer = new Swift(new Swift_Connection_SMTP($smtp_server, $smtp_port, $transport));
			if(!$mailer->isConnected()) {
				return null;
			} // if

			$mailer->setCharset('UTF-8');

			if($smtp_authenticate) {
				if($mailer->authenticate($smtp_username, $smtp_password)) {
					return $mailer;
				} else {
					return null;
				} // if
			} else {
				return $mailer;
			} // if

			// Somethings wrong here...
		} else {
			return null;
		} // if
	} // getMailer

	function sendReminders() {
		include_once "application/cron_functions.php";
		send_reminders();
	}
	
} // Notifier

?>