<?php

/**
 * Extended substr function. If it finds mbstring extension it will use, else
 * it will use old substr() function
 *
 * @access public
 * @param string $string String that need to be fixed
 * @param integer $start Start extracting from
 * @param integer $length Extract number of characters
 * @return string
 */
function substr_utf($string, $start = 0, $length = null) {

	$start = (integer) $start >= 0 ? (integer) $start : 0;
	if(is_null($length)) $length = strlen_utf($string) - $start;

	if(function_exists('mb_substr')) {
		return mb_substr($string, $start, $length, 'UTF-8');
	} else {
		return substr($string, $start, $length);
	} // if

} // substr_utf

/**
 * Return UTF safe string lenght
 *
 * @access public
 * @param strign $string
 * @return integer
 */
function strlen_utf($string) {
	if(function_exists('mb_strlen')) {
		return mb_strlen($string);
	} else {
		return strlen($string);
	} // if
} // strlen_utf

if (!function_exists('iconv')) {
	function iconv($in_charset, $out_charset, $text) {
		return $text;
	}
}

function strpos_utf($haystack, $needle, $offset = 0) {
	if (function_exists('mb_strpos')) {
		return mb_strpos($haystack, $needle, $offset, 'UTF-8');
	} else {
		return strpos($haystack, $needle, $offset);
	} // if
}

function detect_encoding($string, $encoding_list = null, $strict = false) {
	if (function_exists('mb_detect_encoding')) {
		if ($encoding_list == null) $encoding_list = mb_detect_order();
		return mb_detect_encoding($string, $encoding_list, $strict);
	} else {
		return 'UTF-8';
	}
}

?>