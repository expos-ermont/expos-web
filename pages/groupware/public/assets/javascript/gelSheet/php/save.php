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

	include_once("model/Sheet.class.php");
	include_once("model/Book.class.php");
	include_once("Connection.php");
	include_once("controller/Controller.class.php");
	
	$conn = new Connection();
	$jbook='';
	if (isset($_REQUEST['JsonBook'])) {
		
		$jbook = stripslashes($_REQUEST['JsonBook']); 
		//echo $jbook;
		// Unescapo la variable
		//echo var_dump($jbook);
		//$jbook = '{"bookId":"200","bookName":"Prueba","sheets":[{"sheetId":"1000","cells":[{"dataRow":"0","dataColumn":"0","cellFormula":"","fontStyleId":"0","layoutStyleId":"0"},{"dataRow":"1","dataColumn":"1","cellFormula":"Celda 1","fontStyleId":"0","layoutStyleId":"0"},{"dataRow":"1","dataColumn":"2","cellFormula":"Columna 2","fontStyleId":"0","layoutStyleId":"0"},{"dataRow":"2","dataColumn":"1","cellFormula":"=3","fontStyleId":"0","layoutStyleId":"0"}]}]}';
		//$jbook = "{'bookId':'200','bookName':'Prueba','sheets':[{'sheetId':'1000','cells':[{'dataRow':'0','dataColumn':'0','cellFormula':'','fontStyleId':'0','layoutStyleId':'0'},{'dataRow':'1','dataColumn':'1','cellFormula':'Celda 1','fontStyleId':'0','layoutStyleId':'0'},{'dataRow':'1','dataColumn':'2','cellFormula':'Columna 2','fontStyleId':'0','layoutStyleId':'0'},{'dataRow':'2','dataColumn':'1','cellFormula':'=3','fontStyleId':'0','layoutStyleId':'0'}]}]}";
		//$data = json_decode($jbook);
		//echo "NAME_ " .$data->bookName;
		//var_dump($data);
		
		
		//$jbook=' {"bookId":111,"bookName":"El libraco fantastico del paliglio ","userId":1,"sheets":[{"sheetId":197,"bookId":101,"sheetName":"prueba1","sheetIndex":"1","cells":[{"sheetId":197,"dataColumn":1,"dataRow":1,"cellFormula":11,"fontStyleId":1,"layoutStyleId":null},{"sheetId":197,"dataColumn":1,"dataRow":2,"cellFormula":11,"fontStyleId":1,"layoutStyleId":null}]},{"sheetId":198,"bookId":101,"sheetName":"prueba2","sheetIndex":"2","cells":[{"sheetId":198,"dataColumn":1,"dataRow":1,"cellFormula":11,"fontStyleId":1,"layoutStyleId":null},{"sheetId":198,"dataColumn":2,"dataRow":1,"cellFormula":11,"fontStyleId":1,"layoutStyleId":null},{"sheetId":198,"dataColumn":3,"dataRow":1,"cellFormula":3456,"fontStyleId":1,"layoutStyleId":null},{"sheetId":198,"dataColumn":1,"dataRow":3,"cellFormula":3456,"fontStyleId":1,"layoutStyleId":null}]}],"fontStyles":[]}';
		$book = Controller::createBookFromJson($jbook);
		print '<pre>'.print_r($book->fontStyles,1).'</pre>';
		
		$book->setUserId(1);
		$book->save();
	}

?>