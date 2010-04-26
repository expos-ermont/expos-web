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

	include_once("model/Book.class.php");
	include_once("Connection.php");
	include_once("controller/Controller.class.php");
	
	$conn = new Connection();
	$book = new Book();
	$cell = new Cell();
	$book->load(1);
	echo '<hr>';
	print '<pre>'.print_r($book,1).'</pre>';
	
	$json = Controller::createJsonFromBook($book);
	echo $json;
	echo '<hr>';
	
	$book2 = Controller::createBookFromJson($json);
	print '<pre>'.print_r($book2,1).'</pre>';
	
	
	
?>