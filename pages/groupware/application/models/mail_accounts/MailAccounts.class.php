<?php

  /**
  * MailAccounts
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
  class MailAccounts extends BaseMailAccounts {
  	
	/**
    * Return Mail accounts by user
    *
    * @param user
    * @return array
    */
  	function getMailAccountsByUser(User $user)
  	{
  		return MailAccounts::findAll(array(
        'conditions' => '`user_id` = ' . DB::escape($user->getId())
      )); // findAll
  	}
  } // MailAccounts 

?>