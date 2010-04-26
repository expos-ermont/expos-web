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
class SpreadsheetController {
	

		
	public function loadBook($bookId){
		$bookController = new BookController();
		$book = $bookController->find($bookId);
		
		if ($book != -1)
			$message = new Success(null,$book->toJson());
		else 
			throw new Error(301,"Book is not found.");
	}
	
	
	public function saveBook($book, $inputFormat, $outputFormat ) {
		$bookController = new BookController();
		return  $bookController->saveBook($book,$inputFormat,$outputFormat);
	}
	
	public function deleteBook($bookId) {
		$bookController = new BookController();
		return  $bookController->deleteBook($bookId);
	}
	
}

?>