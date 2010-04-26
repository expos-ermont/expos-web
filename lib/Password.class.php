<?php
/**
 * Give a set of functions to deal with passwords
 * 
 * @filesource
 * @author Florent Captier <florent@captier.org>
 */

class Password {
	
	const MIN_LENGTH      = 8;
	const NON_ALPHA_CHAR  = '!#@*()';
	const TOO_SHORT       = 1;
	const CLASS_ALPHA_UP  = 2;
	const CLASS_ALPHA_LOW = 4;
	const CLASS_NUM       = 8;
	const CLASS_NON_ALPHA = 16;
	
	/**
	 * Check the strength of a password
	 * @param string $password Password to check
	 * @return int 0 if success, Bit combination of missing classes (constants of current class)
	 */
	public static function checkStrength($password) {
		$state = 0;
		
		if(strlen($password) < self::MIN_LENGTH) {$state += self::TOO_SHORT;}
		if(!preg_match('/[A-Z]/' , $password)) {$state += self::CLASS_ALPHA_UP;}
		if(!preg_match('/[a-z]/' , $password)) {$state += self::CLASS_ALPHA_LOW;}
		if(!preg_match('/[0-9]/' , $password)) {$state += self::CLASS_NUM;}
		if(!preg_match('/['.preg_quote(self::NON_ALPHA_CHAR).']/' , $password)) {$state += self::CLASS_NON_ALPHA;}
		
		return $state;
	}
	
	/**
	 * Generate a random password
	 *
	 * @var int $length Length of the generated password
	 * @return string Generated password
	 */
	public static function generateRandom($length = self::MIN_LENGTH) {
		if($length < self::MIN_LENGTH) {throw new Exception('Length too short, MUST be at least '.self::MIN_LENGTH.' characters');}
		
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFIJKLMOPQRSTUVWXYZ!#@*()0123456789";
	    srand((double)microtime()*1000000);
	    $pass = '' ;
	
	    while(self::checkStrength($pass) !== 0) {
	    	$pass = '';
		    for($i = 0; $i < $length; $i++) {
		        $num = rand() % (strlen($chars)-1);
		        $tmp = substr($chars, $num, 1);
		        $pass = $pass . $tmp;
		    }
	    }
	    
	    return $pass;
	}
	
	/**
	 * Calculate the hash of a given password
	 *
	 * @var string $password Clear password
	 * @return string Hash value
	 */
	public static function hash($password) {
		return md5($password);
	}
}
?>