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
if ($_REQUEST['Format'] == 'xls') {
	header("Content-type: application/octet-stream");

# replace excelfile.xls with whatever you want the filename to default to
	header("Content-Disposition: attachment; filename=excelfile.xls");
	header("Pragma: no-cache");
	header("Expires: 0");

}

	include_once("model/Sheet.class.php");
	include_once("model/Book.class.php");
	include_once("controller/FormatController.class.php");
	//include_once("Book.php");
	include_once("Connection.php");

	
	
	$conn = new Connection();
	//$book = new Book();
	
	/*sheet = new Sheet();
	$sheet->load(1057);*/
	
	$book = new Book();
	$book->load(1);
	$sheet = $book->sheets[0];
	echo FormatController::toHTML($sheet);
	

?>