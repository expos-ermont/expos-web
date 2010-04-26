<?php
/**
 * Create and send mails
 * @author Florent Captier <florent@captier.org>
 *@filesource
 */
require_once('Page.class.php');

class Mail {
	
	public $from = 'Expos Ermont<noreply@expos-ermont.com>';
	public $to = null;
	public $subject = null;
	public $content = null;
	private $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=utf8\r\n";
	private $template = 'mail.html';
	
	public function addHeader($newHeader) {
		$this->headers .= $newHeader."\r\n";
	}
	
	public function send() {
		if(is_null($this->to) || is_null($this->subject) || is_null($this->content)) {throw new Exception('Missing values, you MUST set the ‘to‘, ‘subject‘ and ‘content‘ vars');}
		$this->addHeader('From: '.$this->from);
		
		// Get skinned content
		$page = new Page();
		$page->template = $this->template;
		$page->add('content' , $this->content);
		$content = $page->getHTML();
		unset($page);
		
		if(!mail($this->to , $this->subject , $content , $this->headers)) {throw new Exception('Error while sending email' , 2);}
	}
	
	public static function quickSend($to , $subject , $content) {
		$mail = new Mail();
		$mail->to = $to;
		$mail->subject = $subject;
		$mail->content = $content;
		$mail->send();
		unset($mail);
	}
}
?>