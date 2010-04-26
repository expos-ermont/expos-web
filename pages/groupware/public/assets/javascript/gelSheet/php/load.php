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
/*
 * Created on 11/06/2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

include_once("model/Sheet.class.php");
include_once("model/Book.class.php");
include_once("controller/Controller.class.php");
//include_once("Book.php");
include_once("Connection.php");

$conn = new Connection();



if (isset($_REQUEST['BookId'])){
	$bookId = $_REQUEST['BookId'];
	$book = Controller::loadBook($bookId);
	echo Controller::createJsonFromBook($book);
}
else
	if (isset($_REQUEST['UserBooks'])) {
		$uid = $_REQUEST['UserBooks'] ;
		$books = Controller::getBooksFromUser($uid);
		echo json_encode($books);
		
	}
		
		



?>
