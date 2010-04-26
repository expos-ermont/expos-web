<?php

  /**
  * Format filesize
  *
  * @access public
  * @param integer $in_bytes Site in bytes
  * @return string
  */
  function format_filesize($in_bytes) {
    $units = array(
      'TB' => 1099511627776,
      'GB' => 1073741824,
      'MB' => 1048576,
      'kb' => 1024,
      //0 => 'bytes'
    ); // array
    
    // Loop units bigger than byte
    foreach($units as $current_unit => $unit_min_value) {
      if($in_bytes > $unit_min_value) {
        $formated_number = number_format($in_bytes / $unit_min_value, 2);
        
        while(str_ends_with($formated_number, '0')) $formated_number = substr($formated_number, 0, strlen($formated_number) - 1); // remove zeros from the end
        if(str_ends_with($formated_number, '.')) $formated_number = substr($formated_number, 0, strlen($formated_number) - 1); // remove dot from the end
        
        return $formated_number . $current_unit;
      } // if
    } // foreach
    
    // Bytes?
    return $in_bytes . 'bytes';
    
  } // format_filesize
  
  /**
  * Return formated datetime
  *
  * @access public
  * @param DateTimeValue $value If value is not instance of DateTime object new DateTime
  *   object will be created with $value as its constructor param
  * @param string $format If $format is NULL default datetime format will be used
  * @param float $timezone Timezone, if NULL it will be autodetected (by currently logged user if we have it)
  * @return string
  */
  function format_datetime($value = null, $format = null, $timezone = null) {
    if(is_null($timezone) && function_exists('logged_user') && (logged_user() instanceof User)) {
      $timezone = logged_user()->getTimezone();
    } // if
    $datetime = $value instanceof DateTimeValue ? $value : new DateTimeValue($value);
    if ($format){
    	$l = new Localization();
    	$l->setDateTimeFormat($format);
    }else
    	$l = Localization::instance();
    return $l->formatDateTime($datetime, $timezone);
  } // format_datetime
  
  /**
  * Return formated date
  *
  * @access public
  * @param DateTimeValue $value If value is not instance of DateTime object new DateTime
  *   object will be created with $value as its constructor param
  * @param string $format If $format is NULL default date format will be used
  * @param float $timezone Timezone, if NULL it will be autodetected (by currently logged user if we have it)
  * @return string
  */
  function format_date($value = null, $format = null, $timezone = null) {
    if(is_null($timezone) && function_exists('logged_user') && (logged_user() instanceof User)) {
      $timezone = logged_user()->getTimezone();
    } // if
    $datetime = $value instanceof DateTimeValue ? $value : new DateTimeValue($value);
    if ($format){
    	$l = new Localization();
    	$l->setDateFormat($format);
    }else
    	$l = Localization::instance();
    return $l->formatDate($datetime, $timezone);
  } // format_date
  
  /**
  * Return descriptive date
  *
  * @param DateTimeValue $value If value is not instance of DateTime object new DateTime
  *   object will be created with $value as its constructor param
  * @param float $timezone Timezone, if NULL it will be autodetected (by currently logged user if we have it)
  * @return string
  */
  function format_descriptive_date($value = null, $timezone = null) {
    if(is_null($timezone) && function_exists('logged_user') && (logged_user() instanceof User)) {
      $timezone = logged_user()->getTimezone();
    } // if
    $datetime = $value instanceof DateTimeValue ? $value : new DateTimeValue($value);
    return Localization::instance()->formatDescriptiveDate($datetime, $timezone);
  } // format_descriptive_date
  
  /**
  * Return formated time
  *
  * @access public
  * @param DateTime $value If value is not instance of DateTime object new DateTime
  *   object will be created with $value as its constructor param
  * @param string $format If $format is NULL default time format will be used
  * @param float $timezone Timezone, if NULL it will be autodetected (by currently logged user if we have it)
  * @return string
  */
  function format_time($value = null, $format = null, $timezone = null) {
    if(is_null($timezone) && function_exists('logged_user') && (logged_user() instanceof User)) {
      $timezone = logged_user()->getTimezone();
    } // if
    $datetime = $value instanceof DateTimeValue ? $value : new DateTimeValue($value);
    if ($format){
    	$l = new Localization();
    	$l->setTimeFormat($format);
    }else
    	$l = Localization::instance();
    return $l->formatTime($datetime, $timezone);
  } // format_time

  
  /**
 * truncate string and add ellipsis
 *
 * Type:     modifier<br>
 * Name:     mb_truncate<br>
 * Purpose:  Truncate a string to a certain length if necessary,
 *           optionally splitting in the middle of a word, and
 *           appending the $etc string or inserting $etc into the middle.
 *           This version also supports multibyte strings.
 * @link http://smarty.php.net/manual/en/language.modifier.truncate.php
 *          truncate (Smarty online manual)
 * @author   Guy Rutenberg <guyrutenberg@gmail.com> based on the original 
 *           truncate by Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param integer
 * @param string
 * @param string
 * @param boolean
 * @param boolean
 * @return string
 */
function truncate($string, $length, $etc = '...', $charset='UTF-8',
                                  $break_words = false, $middle = false)
{
    if ($length == 0)
        return '';
    if (function_exists('mb_strlen')) {
 		$len = mb_strlen($string);
 		$lenetc = mb_strlen($etc);
    } else {
    	$len = strlen($string);
    	$lenetc = strlen($etc);
    }
    if ($len > $length) {
        $length -= min($length, $lenetc);
        if (!$break_words && !$middle) {
        	if(function_exists('mb_substr')) {
            	$string = preg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $length+1, $charset));
        	} else {
        		$string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length+1));
        	}
        }
        if(!$middle) {
        	if(function_exists('mb_substr')) {
            	return mb_substr($string, 0, $length, $charset) . $etc;
        	} else {
        		return substr($string, 0, $length) . $etc;
        	}
        } else {
        	if(function_exists('mb_substr')) {
            	return mb_substr($string, 0, $length/2, $charset) . $etc . mb_substr($string, -$length/2, $charset);
        	} else {
        		return substr($string, 0, $length/2) . $etc . substr($string, -$length/2);
        	}
        }
    } else {
        return $string;
    }
}

function date_format_tip($format) {
	$traductions = array('d' => 'dd', 'D' => lang('sunday short'), 'j' => 'd', 'l' => lang('sunday'), 'N' => 'w', 'S' => 'st', 
					'w' => 'w', 'z' => 'dy', 'W' => 'W', 'F' => lang('month 1'), 'm' => 'mm', 'M' => substr(lang('month 1'),0,3),
					'n' => 'm', 't' => '', 'L' => '', 'o' => 'yyyy', 'Y' => 'yyyy', 'y' => 'yy',
					'a' => 'am', 'A' => 'AM', 'B' => '000', 'g' => 'h', 'G' => 'h', 'h' => 'hh', 'H' => 'hh', 'i' => 'mm', 
					's' => 'ss', 'u' => 'uuuuu', 'e' => 'GMT', 'I' => '', 'O' => '+hhmm', 'P' => '+hh:mm', 'T' => 'EST', 
					'Z' => 'ssss', 'c' => 'ISO date', 'r' => 'Thu, 21 Dec 2000 16:01:07 +0200', 'U' => 'ssss');
	
	$formatChars = array_keys($traductions);
	$result = '';
	$i = 0;
	while ($i < strlen($format)) {
		$char = $format[$i++];
		if (in_array($char, $formatChars)) $result .= $traductions[$char];
		else $result .= $char;
	}
	
	return $result;
}

?>