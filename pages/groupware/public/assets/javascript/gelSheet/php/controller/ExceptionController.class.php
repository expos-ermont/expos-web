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


	class ExceptionController {
		
		public function __construct(){}
		
		public function __destruct(){}
		
		
		/*this function returns the exception message and code*/
		public function getException($id= null){
			
			$sql= "SELECT ExceptionId, ExceptionName FROM exceptions WHERE ExceptionId= $id ";
			
			$result= mysql_query($sql);
			
			if ($row= mysql_fetch_row($result)){
				
				$exception[] = array(
					'exceptionId'	=>	$row->exceptionId	,
					'exceptionName'	=> 	$row->ExceptionName	
				);
				
				
			}
			
			return $exception;
			
		}
		
		
		
		
		
	}





?>