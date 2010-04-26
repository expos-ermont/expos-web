<?php

function myExceptionHandler($e) {
	trigger_error('Uncaught exception (Code : '.$e->getCode().') : '.$e->getMessage().' in '.$e->getFile().' at line '.$e->getLine() , E_USER_WARNING);
}

function normalizeToHTML($string) {
	return stripslashes($string);
}

function explode_query($query) {
	$return = array();
	if(empty($query)) {return $return;}
	$array = explode('&' , urldecode($query));
	foreach($array as $value) {
		$temp = explode('=' , $value);
		$return[$temp[0]] = $temp[1];
	}
	return $return;
}

function implode_query($array) {
	$return = array();
	foreach($array as $key => $value) {
		$return[] = urlencode($key).'='.urlencode($value);
	}
	$return = implode('&amp;' , $return);
	return $return;
}

/**
 * Resize and resample an image
 * 
 * TODO Remove the $ext param which is actually needed due to the fact that MIMEmagic is not installed at OVH
 * 
 * @param string $originalFile Path of the original image
 * @param int $longEdgeSize Length of the final long edge 
 * @param string $ext Force the extension of the original file
 * @param string $destination Path of the resampled image
 */
function resamplePicture($originalFile , $longEdgeSize = 100 , $destination = null , $ext = null) {
	// Determine the extension
	if(function_exists('mime_content_type')) {
		$ext = mime_content_type($originalFile);
		$ext = preg_replace('/^[^\/]*\//' , '' , $ext);
	} else {
		$ext = preg_replace('/^.*\./' , '' , (is_null($ext)) ? $originalFile : $ext);
	}
	
	// New sizes determination
	$final_max = $longEdgeSize; // Length of the bigest edge
	$original_size = getimagesize($originalFile);
	$original_width = $original_size[0];
	$original_height = $original_size[1];
	// Don't resize if original size is lower than longer edge final size
	if($original_width < $longEdgeSize && $original_height < $longEdgeSize) $final_max = min($original_height , $original_width);
	if ($original_width > $original_height) {
		$final_width = $final_max;
		$final_height = round(($final_max * $original_height) / $original_width);
	} else {
		$final_height = $final_max;
		$final_width = round(($final_max * $original_width) / $original_height);
	}
	
	// Image creation
	switch($ext) {
	  case 'jpg';
	  case 'jpeg':
	    $pict = imagecreatefromjpeg($originalFile);
	    break;
	  case 'gif':
	    $pict = imagecreatefromgif($originalFile);
	    break;
	  case 'png':
	    $pict = imagecreatefrompng($originalFile);
	    break;
	  default :
	    $pict = imagecreatefromjpeg($originalFile);
	    break;
	}
	$rpict = imagecreatetruecolor($final_width , $final_height);
	imagealphablending($rpict , false);
	imagecopyresampled($rpict , $pict , 0 , 0 , 0 , 0 , $final_width , $final_height , $original_width , $original_height);
	
	// Turn off alpha blending and set alpha flag
	imagealphablending($rpict, false);
	imagesavealpha($rpict, true);
	
	imageinterlace($rpict , true);
	
	// Output the image in the standard output
	imagepng($rpict , $destination , 0 , PNG_ALL_FILTERS);
	imagedestroy($pict);
	imagedestroy($rpict);
}

/**
 * Set GET variables
 * Exception code :
 *    1 : type mismatch
 *    2 : wrong number of element
 */
function setGetVar($key , $value , $base_uri = null) {
	global $_CONF;

	$uri = (is_null($base_uri)) ? $_SERVER['REQUEST_URI'] : $base_uri;
	
	if(is_array($key) || is_array($value)) {
		// Exception checks
		if(!is_array($key)) {throw new Exception('$key is not an array since $value is' , 1);}
		if(!is_array($value)) {throw new Exception('$value is not an array since $key is' , 1);}
		if(count($key) != count($value)) {throw new Exception('Number of elements differ between keys('.count($key).') and values('.count($value).')');}
		
		for($i = 0; $i < count($key); $i++) {
			$uri = setGetVar($key[$i] , $value[$i] , $uri);
		}
	} else {
		// Generate the new uri
		$t_uri = array('scheme' => '' , 'host' => '' , 'port' => '' , 'user' => '' , 'pass' => '' , 'path' => '' , 'query' => '' , 'fragment' => '');
		$t_uri = array_merge($t_uri , parse_url($uri));
		
		$t_query = explode_query($t_uri['query']);
		$t_query[$key] = $value;
		$t_uri['query'] = implode_query($t_query);
		
		$uri = '';
		if(!empty($t_uri['scheme'])) {$uri .= $t_uri['scheme'].'://';}
		if(!empty($t_uri['user'])) {
			$uri .= $t_uri['user'];
			if(!empty($t_uri['pass'])) {$uri .= ':'.$t_uri['pass'];}
			$uri .= '@';
		}
		if(!empty($t_uri['host'])) {$uri .= $t_uri['host'];}
		if(!empty($t_uri['port'])) {$uri .= ':'.$t_uri['port'];}
		if(!empty($t_uri['path'])) {$uri .= $t_uri['path'];}
		if(!empty($t_uri['query'])) {$uri .= '?'.$t_uri['query'];}
		if(!empty($t_uri['fragment'])) {$uri .= '#'.$t_uri['fragment'];}
	}
	
	return $uri;
}
?>