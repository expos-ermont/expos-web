<?php
/**
 * Set of functions to deal with Flickr! sets
 * 
 * UNUSED FOR THE MOMENT
 * 
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

class ExposFlickr {
	// SHOULD NOT be modified
	private static $key = 'e3f062d260a088ada8505d49be09cb06';
	private static $perms = 'read';
	
	// SHOULD NOT be modified, will be initialized during class creation
	private static $connectionLink = 'http://flickr.com/services/auth/?api_key=e3f062d260a088ada8505d49be09cb06&perms=read&api_sig=2a3a0b594a7676545901f6255bed3ae1';
	
	// MUST be set after using the URL given by ExposFlickr::getConnectionLink() (frob will be a GET var)
	private static $frob = '72157622818273331-5d2cbd775e87a6a5-33372046';
	
	public static function getConnectionLink($secret) {
		$api_sig = md5($secret.'api_key'.self::$key.'perms'.self::$perms);
		return 'http://flickr.com/services/auth/?api_key='.self::$key.'&perms='.self::$perms.'&api_sig='.$api_sig;
	}
	
	public static function getToken() {
		try {
			$soapClient = new SoapClient(null , array(
				'location' => 'http://api.flickr.com/services/soap/',
				'uri' => 'http://www.flickr.com'
			));
			var_dump($soapClient->getFunctions());
		} catch(Exception $e) {
			echo $e->getMessage();
		}
	}
	
	public static function getPoolPhotos() {
		try {
			$soapClient = new SoapClient(null , array(
				'location' => 'http://api.flickr.com/services/soap/',
				'uri' => 'http://www.flickr.com'
			));
			var_dump($soapClient->getFunctions());
		} catch(Exception $e) {
			echo $e->getMessage();
		}
	}
}
?>