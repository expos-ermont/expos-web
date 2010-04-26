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
	include_once("Connection.php");
	include_once("Sheet.class.php");

	/**
 	* Class Book.
 	* @author Pepe
 	*/
	class FontStyle {

		public $fontStyleId ;
		public $bookId;
		public $fontId;
		public $fontSize ;
		public $fontBold ;
		public $fontItalic;
		public $fontUnderline;
		public $fontColor;

		public function getId(){
			return $this->fontStyleId;
		}

		public function getBookId(){
			return $this->bookId;
		}

		public function getFontId(){
			return $this->fontId;
		}

		public function getFontname(){

			$sql= "SELECT * FROM ". table('fonts') ." WHERE FontId= $this->fontId";
			$result= mysql_query($sql);

			if ($row = mysql_fetch_object($result)) {
				return $row->FontName;
			}
			else{
				return "Calibri";
			}
		}


		public function getFontSize(){
			return $this->fontSize;
		}

		public function getFontBold(){
			return $this->fontBold;
		}

		public function getFontItalic(){
			return $this->fontItalic;
		}

		public function getFontUnderline(){
			return $this->fontUnderline;
		}

		public function getFontColor(){
			return $this->fontColor;
		}

		/*** TERMINAR ***/
		public function setId($id){
			$this->fontStyleId;
		}

		public function setBookId($bookid){
			$this->bookId = $bookid;
		}

		public function setFontId($fontId){
			$this->fontId=$fontId;
		}

		public function toJson() {
			return json_encode($this) ;
		}

		public function fromJson($json_obj){
			$this->bookId 		= $json_obj->bookId;
			$this->fontStyleId 	= $json_obj->fontStyleId;
			$this->fontId 		= $json_obj->fontId;
			$this->fontBold 	= $json_obj->fontBold;
			$this->fontItalic 	= $json_obj->fontItalic;
			$this->fontUnderline= $json_obj->fontUnderline;
			$this->fontSize 	= $json_obj->fontSize;
			$this->fontColor	= $json_obj->fontColor;
		}

		public function delete($recursive = false) {
			$sql = "DELETE  FROM ".table('fontStyles'). " where fontStyleId=$this->fontStyleId";
			return mysql_query($sql);
		}


		/**
		* Constructor.
	 	*/
		public function __construct($fontStyleId = null,$bookId=null, $fontId =null, $fontSize =null ,  $fontBold =null, $fontItalic=null, $fontUnderline = null,$fontColor = null ){
			$this->fontStyleId=$fontStyleId;
			$this->bookId=$bookId;
			$this->fontId=$fontId;
			$this->fontSize = $fontSize;
			$this->fontBold=$fontBold ;
			$this->fontItalic=$fontItalic;
			$this->fontUnderline=$fontUnderline;
			$this->fontColor=$fontColor;
		}

		/**
		 * Destructor.
		 */
		public function __destruct(){
		}

		public function save() {
			$sql = sprintf("INSERT INTO ".table('fontStyles'). " (fontStyleId ,bookId,fontId ,fontSize ,fontBold ,fontItalic,fontUnderline ,fontColor)
							VALUES (%d,%d,%d,%d,%d,%d,%d,'%s')",
					$this->fontStyleId,
					$this->bookId,
					$this->fontId,
					$this->fontSize,
					$this->fontBold ,
					$this->fontItalic,
					$this->fontUnderline,
					$this->fontColor
			);

			if(mysql_query($sql))
				return false;
			else{
				//echo mysql_error();
				return true;
			}

		}
	}
?>