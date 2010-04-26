<?php
/*  Gelsheet Project, version 0.0.1 (Pre-alpha)
 *  Copyright (c) 2008 - Ignacio Vazquez, Fernando Rodriguez, Juan Pedro del Campo
 *
 *  Ignacio "Pepe" Vazquez <elpepe22@users.sourceforge.net>
 *  Fernando "Palillo" Rodriguez <fernandor@users.sourceforge.net>
 *  Juan Pedro "Perico" del Campo <pericodc@users.sourceforge.net>
 *
 *  Gelsheet is free distributable under the terms of an GPL license.
 *  For details see: http://www.gnu.org/copyleft/gpl.html
 *
 */
	define ("GS_ROOT", dirname(__FILE__) );
	/**** Scripts that must be included: autoloader is only for objects ****/  
	include_once './config/settings.php'	;
	include_once './util/db_functions.php'	;
	include_once './util/lang/languages.php';
	/***********************************************************************/
	
	
	function validateCall($controller, $method, $parameters ) {
		if (trim($controller) ==  'SpreadsheetController') return TRUE ;
		if (trim($controller) ==  'UserController') return TRUE ;
		if (trim($controller) ==  'LanguageController') return TRUE ;
		return FALSE ;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param String $classname
	 */
	function __autoload($classname){
		global $cnf ;
		if(isset($cnf['path'][$classname])){
//			echo $cnf['site']['path']."/". $cnf['path'][$classname]."<br>";
			include_once ($cnf['site']['path']."/". $cnf['path'][$classname]);
		}
		else {
			echo $classname. ": Class don't exist in the config file";
		}
	}

	/**
	 * Callback Function that handles PHP Exceptions and 
	 * Filter user defined	 
	 * @param Exception $ex
	 * @return boolean
	 */
	function exceptionHandler($ex) {
		if (is_subclass_of ( $ex, "Message" )) {
			return false;
		}
	}
	
	//Set the Execption Handler to  
	set_exception_handler ( "exceptionHandler" );
	
	/**
	 * Takes param from REQUEST..
	 * and makes an array..
	 * Magic Prefix Params (Thanks pepe!)
	 *
	 */
	function splitParameters($param_prefix = "param") {
		$params = array();
		$more_params = true;
		$i=1;
		while($more_params){
			if (isset($_REQUEST[$param_prefix.$i]) ) {				
				$param = $_REQUEST[$param_prefix.$i] ;
				array_push($params,$param);
				$i++;
			}else {
				$more_params = false ;
			}
		}
		return $params;
	}

	$connection  = new Connection();

	if(!isset($_REQUEST['c'])){
		$err = new Error(201,"Bad Command Request");
		if($err->isDebugging())
			$err->addContentElement("Param Required","Controller is needed, c=ControllerName should be passed");
		throw $err;
	}
	
	$controller = $_REQUEST['c']."Controller";
	$method = $_REQUEST['m'];
	$params = splitParameters("param");
	
	
	if (! validateCall($controller, $method, $params) ){
		die ("invalid class/method/params") ;
	}
	
	if (class_exists($controller)) {
		if (method_exists($controller, $method)) {
			$cont = new $controller();
			$php_params = "'". implode("','",$params) . "'";
			eval('$cont->$method('.rawurldecode($php_params).');');
		}
	}

?>