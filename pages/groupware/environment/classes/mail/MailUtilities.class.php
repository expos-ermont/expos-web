<?php
require_once 'Net/IMAP.php';
require_once "Net/POP3.php";

class MailUtilities {

	function getmails($accounts = null, &$err, &$succ, &$errAccounts, &$mailsReceived, $maxPerAccount = 0) {
		if (is_null($accounts)) {
			$accounts = MailAccounts::findAll();
		}
		
		$old_memory_limit = ini_get('memory_limit');
		ini_set('memory_limit', '96M');

		$err = 0;
		$succ = 0;
		$errAccounts = array();
		$mailsReceived = 0;

		if (isset($accounts)) {
			foreach($accounts as $account){
				try{
					$accId = $account->getId();
					$emails = array();
					if (!$account->getIsImap()) {
						$mailsReceived += self::getNewPOP3Mails($account, $maxPerAccount);
					} else {
						$mailsReceived += self::getNewImapMails($account, $maxPerAccount);
					}

					$succ++;
				} catch(Exception $e) {
					$errAccounts[$err]["accountName"] = $account->getEmail();
					$errAccounts[$err]["message"] = $e->getMessage();
					$err++;
				}
			}
		}
		
		ini_set('memory_limit', $old_memory_limit);
		
		tpl_assign('err',$err);
		tpl_assign('errAccounts',$errAccounts);
		tpl_assign('accounts',$accounts);
		tpl_assign('mailsReceived',$mailsReceived);
	}

	private function getAddresses($field) {
		$f = '';
		if ($field) {
			foreach($field as $add) {
				if (!empty($f))
				$f = $f . ', ';
				$address = trim(array_var($add, "address", ''));
				if (strpos($address, ' '))
				$address = substr($address,0,strpos($address, ' '));
				$f = $f . $address;
			}
		}
		return $f;
	}
	
	private function SaveContentToFilesystem($uid, &$content) {
		$tmp = ROOT . '/tmp/' . rand();
		$handle = fopen($tmp, "wb");
		fputs($handle, $content);
		fclose($handle);
		$date = DateTimeValueLib::now()->format("Y_m_d_H_i_s__");
		$repository_id = FileRepository::addFile($tmp, array('name' => $date.$uid, 'type' => 'text/plain', 'size' => strlen($content)));
		
		unlink($tmp);
		
		return $repository_id;
	}

	private function SaveMail(&$content, MailAccount $account, $uidl, $state = 0, $imap_folder_name = '') {
		if (strpos($content, '+OK ') > 0) $content = substr($content, strpos($content, '+OK '));
		self::parseMail($content, $decoded, $parsedMail, $warnings);
		$encoding = array_var($parsedMail,'Encoding', 'UTF-8');
		$from = self::getAddresses(array_var($parsedMail, "From"));
		if ($state == 0) {
			if ($from == $account->getEmailAddress()) {
				$state = 1;
			}
		}

		if (!isset($parsedMail['Subject'])) $parsedMail['Subject'] = '';
		$mail = new MailContent();
		$mail->setAccountId($account->getId());
		$mail->setState($state);
		$mail->setImapFolderName($imap_folder_name);
		$mail->setFrom($from);
		
		if (array_key_exists('Encoding', $parsedMail)){
			$mail->setFromName(iconv($encoding, 'UTF-8//IGNORE', array_var(array_var(array_var($parsedMail, 'From'), 0), 'name')));
			$mail->setSubject(iconv($encoding, 'UTF-8//IGNORE', $parsedMail['Subject']));
		} else {
			$mail->setFromName(array_var(array_var(array_var($parsedMail, 'From'), 0), 'name'));
			$mail->setSubject($parsedMail['Subject']);
		}
		$mail->setTo(self::getAddresses(array_var($parsedMail, "To")));
		if (array_key_exists("Date", $parsedMail)) {
			$mail->setSentDate(new DateTimeValue(strtotime($parsedMail["Date"])));
		}else{
			$mail->setSentDate(new DateTimeValue(DateTimeValueLib::now()));
		}
		$mail->setSize(strlen($content));
		$mail->setHasAttachments(!empty($parsedMail["Attachments"]));
		$mail->setCreatedOn(new DateTimeValue(time()));
		$mail->setCreatedById($account->getUserId());
		$mail->setAccountEmail($account->getEmail());
		
		$uid = trim($uidl);
		if ($uid[0]== '<') {
			$uid = mb_substr($uid, 1, mb_strlen($uid, $encoding) - 2, $encoding);
		}
		$mail->setUid($uid);

		$type = array_var($parsedMail, 'Type', 'text');
		switch($type) {
			case 'html': $mail->setBodyHtml(iconv($encoding, 'UTF-8//IGNORE', isset($parsedMail['Data']) ? $parsedMail['Data'] : '')); break;
			case 'text': $mail->setBodyPlain(iconv($encoding, 'UTF-8//IGNORE', isset($parsedMail['Data']) ? $parsedMail['Data'] : '')); break;
			case 'delivery-status': $mail->setBodyPlain(iconv($encoding, 'UTF-8//IGNORE', $parsedMail['Response'])); break;
		}

			
		if (isset($parsedMail['Alternative'])) {
			if ($parsedMail['Alternative'][0]['Type'] == 'html') {
				$mail->setBodyHtml(iconv(array_var($parsedMail['Alternative'][0],'Encoding','UTF-8'),'UTF-8//IGNORE', array_var($parsedMail['Alternative'][0], 'Data', '')));
			} else {
				$mail->setBodyPlain(iconv(array_var($parsedMail['Alternative'][0],'Encoding','UTF-8'),'UTF-8//IGNORE', array_var($parsedMail['Alternative'][0], 'Data', '')));
			}
		}
		
		// Remove everything beyond the end of the html
		if (($end_pos = strpos($mail->getBodyHtml(), "</html>")) > 0) {
			$mail->setBodyHtml(substr($mail->getBodyHtml(), 0, $end_pos + strlen("</html>")));
		}
		
		$repository_id = self::SaveContentToFilesystem($mail->getUid(), iconv($encoding, 'UTF-8//IGNORE', $content));
		$mail->setContentFileId($repository_id);
		
		try {
			DB::beginWork();
			$mail->save();
			$user = Users::findById($account->getUserId());
			if ($user instanceof User) {
				$mail->subscribeUser($user);
			}
			DB::commit();
		} catch(Exception $e) {
			DB::rollback();
			Logger::log($e->getMessage());
		}
		unset($parsedMail);
	}

	function parseMail(&$message, &$decoded, &$results, &$warnings) {
		$mime = new mime_parser_class;
		$mime->mbox = 0;
		$mime->decode_bodies = 1;
		$mime->ignore_syntax_errors = 1;

		$parameters=array('Data'=>$message);

		if($mime->Decode($parameters, $decoded)) {
			for($msg = 0; $msg < count($decoded); $msg++) {
				$mime->Analyze($decoded[$msg], $results);
			}
			for($warning = 0, Reset($mime->warnings); $warning < count($mime->warnings); Next($mime->warnings), $warning++) {
				$w = Key($mime->warnings);
				$warnings[$warning] = 'Warning: '. $mime->warnings[$w]. ' at position '. $w. "\n";
			}
		}
	}

	/**
	 * Gets all new mails from a given mail account
	 *
	 * @param MailAccount $account
	 * @return array
	 */
	private function getNewPOP3Mails(MailAccount $account, $max = 0) {
		$mime = new mime_parser_class();
		$pop3 = new Net_POP3();

		// Connect to mail server
		if ($account->getIncomingSsl()) {
			$pop3->connect("ssl://" . $account->getServer(), $account->getIncomingSslPort());
		} else {
			$pop3->connect($account->getServer());
		}
		if (PEAR::isError($ret=$pop3->login($account->getEmail(), self::ENCRYPT_DECRYPT($account->getPassword()), 'USER'))) {
			throw new Exception($ret->getMessage());
		}
		$emails = $pop3->getListing();
		$oldUids = $account->getUids();

		// get the index of the last received email
		$lastReceived = count($emails) - 1;
		while ($lastReceived >= 0 && !in_array($emails[$lastReceived]['uidl'], $oldUids)) {
			$lastReceived--;
		}
		$lastReceived++;
		if ($lastReceived >= count($emails)) {
			// there's no new emails
			$newEmails = array();
		} else {
			// get the first max (or available) unread emails
			if ($max == 0) {
				$newEmails = array_slice($emails, $lastReceived, count($emails) - $lastReceived);
			} else {
				$newEmails = array_slice($emails, $lastReceived, min(array($max, count($emails) - $lastReceived)));
			}
			if (!empty($newEmails)) {
				for ($i=0; $i < count($newEmails); $i++) {
					$content = $pop3->getMsg($newEmails[$i]['msg_id']);
					self::SaveMail($content, $account, $newEmails[$i]['uidl']);
					unset($content);
				}
			}
		}
		$pop3->disconnect();

		return count($newEmails);
	}

	public function displayMultipleAddresses($addresses, $clean = true) {
		$list = explode(',', $addresses);
		$result = "";
		foreach($list as $address){
			$address = trim($address);
			$link = self::getPersonLinkFromEmailAddress($address, $clean);
			if ($result != "")
			$result .= ', ';
			$result .= $link;
		}
		return $result;
	}

	public function ENCRYPT_DECRYPT($Str_Message) {
		//Function : encrypt/decrypt a string message v.1.0  without a known key
		//Author   : Aitor Solozabal Merino (spain)
		//Email    : aitor-3@euskalnet.net
		//Date     : 01-04-2005
		$Len_Str_Message=STRLEN($Str_Message);
		$Str_Encrypted_Message="";
		FOR ($Position = 0;$Position<$Len_Str_Message;$Position++){
			// long code of the function to explain the algoritm
			//this function can be tailored by the programmer modifyng the formula
			//to calculate the key to use for every character in the string.
			$Key_To_Use = (($Len_Str_Message+$Position)+1); // (+5 or *3 or ^2)
			//after that we need a module division because can't be greater than 255
			$Key_To_Use = (255+$Key_To_Use) % 255;
			$Byte_To_Be_Encrypted = SUBSTR($Str_Message, $Position, 1);
			$Ascii_Num_Byte_To_Encrypt = ORD($Byte_To_Be_Encrypted);
			$Xored_Byte = $Ascii_Num_Byte_To_Encrypt ^ $Key_To_Use;  //xor operation
			$Encrypted_Byte = CHR($Xored_Byte);
			$Str_Encrypted_Message .= $Encrypted_Byte;

			//short code of the function once explained
			//$str_encrypted_message .= chr((ord(substr($str_message, $position, 1))) ^ ((255+(($len_str_message+$position)+1)) % 255));
		}
		RETURN $Str_Encrypted_Message;
	} //end function

	private static function getPersonLinkFromEmailAddress($email, $clean = true) {
		$name = $email;
		$url = "";

		$user = Users::getByEmail($email);
		if ($user instanceof User && $user->canSeeUser(logged_user())){
			$name = $clean ? clean($user->getDisplayName()) : $user->getDisplayName();
			$url = $user->getCardUrl();
		} else {
			$contact = Contacts::getByEmail($email);
			if ($contact instanceof Contact && $contact->canView(logged_user()))
			{
				$name = $clean ? clean($contact->getDisplayName()) : $contact->getDisplayName();
				$url = $contact->getCardUrl();
			}
		}
		if ($url != ""){
			return '<a class="internalLink" href="'.$url.'" title="'.$email.'">'.$name."</a>";
		} else {
			return $email;
		}
	}

	
	//function sendMail($smtp_server,$to,$from,$subject,$body,$cc,$bcc,$smtp_port=25,$smtp_username = null, $smtp_password ='',$type='text/plain',$transport=0) {
	function sendMail($smtp_server,$to,$from,$subject,$body,$cc,$bcc,$attachments=null,$smtp_port=25,$smtp_username = null, $smtp_password ='',$type='text/plain',$transport=0) {
		//Load in the files we'll need
		Env::useLibrary('swift');
		// Load SMTP config
		$smtp_authenticate = $smtp_username != null;
		
		switch ($transport) {
			case 'ssl': $transport = SWIFT_SSL; break;
			case 'tls': $transport = SWIFT_TLS; break;
			default: $transport = 0; break;
		}
		
		//Start Swift
		$mailer = new Swift(new Swift_Connection_SMTP($smtp_server, $smtp_port, $transport));

		if(!$mailer->isConnected()) {
			return false;
		} // if
		$mailer->setCharset('UTF-8');
		if($smtp_authenticate) {
			if(!($mailer->authenticate($smtp_username, self::ENCRYPT_DECRYPT($smtp_password)))) {
				return false;
			} // if
		}
		if(! $mailer->isConnected() )  return false;
		// Send Swift mail
		$mailer->addCc(explode(",",$cc));
		$mailer->addBcc(explode(",",$bcc));
		// add attachments
 		if (is_array($attachments)) {
 			$mailer->addPart($body, $type); // real body
         		foreach ($attachments as $att)
 				$mailer->addAttachment($att["data"], $att["name"], $att["type"]);
 			$body = false; // multipart
 		}
		$ok = $mailer->send($to, $from, $subject,$body,$type);
		$mailer->close();
		return $ok;
	}

	function parse_to($to) {
		if (!is_array($to)) return $to;
		$return = array();
		foreach ($to as $elem){
			$mail= preg_replace("/.*\<(.*)\>.*/", "$1", $elem, 1);
			$nam = explode('<', $elem);
			$return[]= array(trim($nam[0]),trim($mail));
		}
		return $return;
	}

	/****************************** IMAP ******************************/
	
	private function getNewImapMails(MailAccount $account, $max = 0) {
		$received = 0;

		if ($account->getIncomingSsl()) {
			$imap = new Net_IMAP("ssl://" . $account->getServer(), $account->getIncomingSslPort());	
		} else {
			$imap = new Net_IMAP("tcp://" . $account->getServer());
		}
		$ret = $imap->login($account->getEmail(), self::ENCRYPT_DECRYPT($account->getPassword()));
		$mailboxes = MailAccountImapFolders::getMailAccountImapFolders($account->getId());
		if (is_array($mailboxes)) {
			foreach ($mailboxes as $box) {
				if ($box->getCheckFolder()) {
					
					if ($imap->selectMailbox(utf8_decode($box->getFolderName()))) {
						$oldUids = $account->getUids($box->getFolderName());
						$numMessages = $imap->getNumberOfMessages(utf8_decode($box->getFolderName()));
						
						$mails_info = array();
						if (!is_array($oldUids) || count($oldUids) == 0) {
							$lastReceived = 0;
						} else {
							$lastReceived = $numMessages;
							$mails_info[$lastReceived] = $imap->getSummary($lastReceived);
							while (is_array($mails_info[$lastReceived]) && $lastReceived > 0 && !in_array($mails_info[$lastReceived][0]['UID'], $oldUids)) {
								$lastReceived--;
								$mails_info[$lastReceived] = $imap->getSummary($lastReceived);
							}
						}
						
						if ($max == 0) $toGet = $numMessages;
						else $toGet = min($lastReceived + $max, $numMessages);
						
						// get mails since last received (last received is not included)
						for ($i = $lastReceived; $i < $toGet; $i++) {
							$index = $i+1;
							$summary = array_var($mails_info, $index, $imap->getSummary($index));
							if (PEAR::isError($summary)) {
								Logger::log($summary->getMessage());
							} else {
								if ($imap->isDraft($index)) $state = 2;
								else $state = 0;
								
								$messages = $imap->getMessages($index);
								if (PEAR::isError($messages)) {
									Logger::log($messages->getMessage());
									continue;
								}
								self::SaveMail($messages[$index], $account, $summary[0]['UID'], $state, $box->getFolderName());
								$received++;
							}
						}

					}
				}
			}
		}
		$imap->disconnect();
		return $received;
	}

	function getImapFolders(MailAccount $account) {
		if ($account->getIncomingSsl()) {
			$imap = new Net_IMAP("ssl://" . $account->getServer(), $account->getIncomingSslPort());	
		} else {
			$imap = new Net_IMAP("tcp://" . $account->getServer());
		}
		$ret = $imap->login($account->getEmail(), self::ENCRYPT_DECRYPT($account->getPassword()));
		if (PEAR::isError($ret)) {
			Logger::log($ret->getMessage());
			throw new Exception($ret->getMessage());
		}
		$result = array();
		if ($ret === true) {
			$mailboxes = $imap->getMailboxes('',0,true);
			if (is_array($mailboxes)) {
				foreach ($mailboxes as $mbox) {
					$select = true;
					$attributes = array_var($mbox, 'ATTRIBUTES');
					if (is_array($attributes)) {
						foreach($attributes as $att) {
							if (strtolower($att) == "\\noselect") $select = false;
							if (!$select) break;
						}
					}
					$name = array_var($mbox, 'MAILBOX');
					if ($select && isset($name)) $result[] = utf8_encode($name);
				}
			}
		}		
		$imap->disconnect();
		return $result;
	}
	
	function deleteMailsFromServer(MailAccount $account) {
		if ($account->getDelFromServer() > 0) {
			$max_date = DateTimeValueLib::now();
			$max_date->add('d', -1 * $account->getDelFromServer());
			if ($account->getIsImap()) {
				if ($account->getIncomingSsl()) {
					$imap = new Net_IMAP("ssl://" . $account->getServer(), $account->getIncomingSslPort());	
				} else {
					$imap = new Net_IMAP("tcp://" . $account->getServer());
				}
				$ret = $imap->login($account->getEmail(), self::ENCRYPT_DECRYPT($account->getPassword()));
				
				$result = array();
				if ($ret === true) {
					$mailboxes = MailAccountImapFolders::getMailAccountImapFolders($account->getId());
					if (is_array($mailboxes)) {
						foreach ($mailboxes as $box) {
							if ($box->getCheckFolder()) {
								$numMessages = $imap->getNumberOfMessages(utf8_decode($box->getFolderName()));
								for ($i = 1; $i < $numMessages; $i++) {
									$summary = $imap->getSummary($i);
									if (is_array($summary)) {
										$m_date = DateTimeValueLib::makeFromString($summary[0]['INTERNALDATE']);
										if ($max_date->getTimestamp() > $m_date->getTimestamp())
											$imap->deleteMessages($i);
										else break;
									} 
								}
								$imap->expunge();
							}
						}
					}
				}
				
			} else {
				//require_once "Net/POP3.php";
				$pop3 = new Net_POP3();
				// Connect to mail server
				$pop3->connect($account->getServer());
				if (PEAR::isError($ret=$pop3->login($account->getEmail(), self::ENCRYPT_DECRYPT($account->getPassword()), 'USER'))) {
					throw new Exception($ret->getMessage());
				}
				$emails = $pop3->getListing();
				foreach ($emails as $email) {
					$headers = $pop3->getParsedHeaders($email['msg_id']);
					$date = DateTimeValueLib::makeFromString($headers['Date']);
					if ($max_date->getTimestamp() > $date->getTimestamp()) {
						$pop3->deleteMsg($email['msg_id']);
					}
				}
				$pop3->disconnect();
										
			}
		}
	}
	
	function getContent($smtp_server, $smtp_port, $transport, $smtp_username, $smtp_password, $body, $attachments)
 	{
 		//Load in the files we'll need
		Env::useLibrary('swift');
 		
		switch ($transport) {
			case 'ssl': $transport = SWIFT_SSL; break;
			case 'tls': $transport = SWIFT_TLS; break;
			default: $transport = 0; break;
		}
 		
		//Start Swift
 		$mailer = new Swift(new Swift_Connection_SMTP($smtp_server, $smtp_port, $transport));
 		
 		if(!$mailer->isConnected()) {
 			return false;
 		} // if
 		$mailer->setCharset('UTF-8');
 
 		if($smtp_username != null) {
			if(!($mailer->authenticate($smtp_username, self::ENCRYPT_DECRYPT($smtp_password)))) {
				return false;
			}
		}
		if(! $mailer->isConnected() )  return false;
		
 		// add attachments
 		$mailer->addPart($body); // real body
 		if (is_array($attachments) && count($attachments) > 0) {
	 		foreach ($attachments as $att)
	 			$mailer->addAttachment($att["data"], $att["name"], $att["type"]);
 		}
 		
 		$content = $mailer->getFullContent(false);
 		$mailer->close();
 		return $content;
 	}
 
 	public function saveContent($content)
 	{
 		return $this->saveContentToFilesystem("UID".rand(), $content);
 	}
}
?>