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

	class ExportController {
		/*this controller manages the export functions*/

		private $file;
		private $book;
		private $objPHPExcel;
		private $objPHPOds;

		/*constructs*/

		/*the construct gets the book id for the exportation*/
		public function __construct() {


		}

		public function __destruct() {}


		/* export functions */

		function generateBook($book, $format) {
			//echo "generate Book";
			$this->book= $book; //BookController::find($idBook);
			$filename= $this->book->getName();

			if ($filename == null){

				$filename= "default-".rand(1,9999);

			}

			/*SET SPREADSHEET PROPERTIES*/
			if ($format!= "ods"){

				$this->objPHPExcel = new PHPExcel();
				$this->objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
				$this->objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
				$this->objPHPExcel->getProperties()->setTitle("Test Document");
				$this->objPHPExcel->getProperties()->setSubject("Test Document");
				$this->objPHPExcel->getProperties()->setDescription("Test document generated using PHP classes.");
				$this->objPHPExcel->getProperties()->setKeywords("office php");
				$this->objPHPExcel->getProperties()->setCategory("Test result file");

			}
			else{

				$this->objPHPOds= new PHPOds(); //create a new ods file

			}

			/*GENERATE THE SHEETS*/
			$this->_generateSheets($format);

			//TODO
			/*CHECK FOR RELATIVES PATHS*/
			global $cnf;
			$currentDir= $cnf['path']['Temp']."/";  // Get the Storage Folder
			//echo $currentDir;

			switch($format){

				case "ods":
							saveOds($this->objPHPOds,"$filename.$format"); //save the object to a ods file
							break;

				case "pdf":
							$objWriter1 = new PHPExcel_Writer_PDF($this->objPHPExcel);
							$objWriter1->writeAllSheets();
							$objWriter1->setTempDir($currentDir);
							$objWriter1->save("$filename.$format");	//save the object to a pdf file
							break;

				case "xls":
							$objWriter2 = new PHPExcel_Writer_Excel5($this->objPHPExcel);
							$objWriter2->setTempDir($currentDir);
							$objWriter2->save("$filename.$format");	//save the object to a xls file
							break;

				case "xlsx":
							$objWriter3 = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
							$objWriter3->save($currentDir."$filename.$format"); //save the object to a xlsx file
							break;


			}

			if ($format != "ods")
				$this->_send("$filename.$format");

		}

		/**
		 * Generates the sheet's workbook...
		 *
		 * @param String format extension
		 */
		function _generateSheets($format){


			$sheets= array();
			$sheets= $this->book->getSheets();
			$i= 0;


			if ($format=="ods"){

				foreach($sheets as $sheet){

					$cells= array();
					$cells= $sheet->getCells();


					foreach($cells as $cellarray){

						foreach($cellarray as $cell){

						$col= $cell->getDataColumn();
						$row= $cell->getDataRow();
						$data= $cell->getFormula();

						if (substr($data, 0, 1)== '=')

							$this->objPHPOds->addCell($i,$row,$col,substr($data, 1),'float');

						//TODO
						else /*OJO CON ESTO DISCERNIR ENTRE LOS DIFERENTES TIPOS*/

							$this->objPHPOds->addCell($i,$row,$col,$data,'string');

						}

					}

					$i++;

				}



			}
			else{


				foreach($sheets as $sheet){

					if ($i>0)
						$this->objPHPExcel->createSheet();

					$this->objPHPExcel->setActiveSheetIndex($i);
					$this->objPHPExcel->getActiveSheet()->setTitle($sheet->getName());

					$cells= array();

					$cells= $sheet->getCells();

					foreach($cells as $cellarray){

						foreach ($cellarray as $cell){


						$col= $cell->getDataColumn();
						$row= $cell->getDataRow();
						$data= $cell->getFormula();

						$cellPos= $this->_givePositionPHPExcel($col, $row);

						$this->objPHPExcel->getActiveSheet()->getCell($cellPos)->setValueExplicit($data, PHPExcel_Cell_DataType::dataTypeForValue($data));

						$fontId= $cell->getFontStyleId();

						$fontStyle= new FontStyle();
				//		echo "<br>".print_r($fontStyle);
						$fontStyle= $this->book->getFontStyle($fontId);
				//		echo "<br>".print_r($fontStyle);

						$fontName= $fontStyle->getFontName();
						
						$this->objPHPExcel->getActiveSheet()->getStyle($cellPos)->getFont()->setName($fontName);
						$this->objPHPExcel->getActiveSheet()->getStyle($cellPos)->getFont()->setBold($fontStyle->getFontBold()== 1);
						$this->objPHPExcel->getActiveSheet()->getStyle($cellPos)->getFont()->setColor(PHPExcel_Style_Color::setRGB(substr($fontStyle->getFontColor(), 1)));
						$this->objPHPExcel->getActiveSheet()->getStyle($cellPos)->getFont()->setItalic($fontStyle->getFontItalic()==1);
						$this->objPHPExcel->getActiveSheet()->getStyle($cellPos)->getFont()->setSize($fontStyle->getFontSize());

						if ($fontStyle->getFontUnderline()!= 0)
							$this->objPHPExcel->getActiveSheet()->getStyle()->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);

						}

					}



						$i++;

					}


				}

		}


		/**
		 * Gives a PHPExcel readeable position
		 *
		 * @param Integer $row
		 * @param Integer $col
		 * @return String up to ZZ or more
		 */
		function _givePositionPHPExcel($row, $col){

			$row;
			$col++;

			$result= "";

			$aux= $row;

			while($aux >= 0){

				$modulo= $aux % 26;

				switch($modulo){


					case 0:
							$result.= 'A';
							break;

					case 1:
							$result.= 'B';
							break;

					case 2:
							$result.= 'C';
							break;

					case 3:
							$result.= 'D';
							break;

					case 4:
							$result.= 'E';
							break;

					case 5:
							$result.= 'F';
							break;

					case 6:
							$result.= 'G';
							break;

					case 7:
							$result.= 'H';
							break;

					case 8:
							$result.= 'I';
							break;

					case 9:
							$result.= 'J';
							break;

					case 10:
							$result.= 'K';
							break;

					case 11:
							$result.= 'L';
							break;

					case 12:
							$result.= 'M';
							break;

					case 13:
							$result.= 'N';
							break;

					case 14:
							$result.= 'O';
							break;

					case 15:
							$result.= 'P';
							break;

					case 16:
							$result.= 'Q';
							break;

					case 17:
							$result.= 'R';
							break;

					case 18:
							$result.= 'S';
							break;

					case 19:
							$result.= 'T';
							break;

					case 20:
							$result.= 'U';
							break;

					case 21:
							$result.= 'V';
							break;

					case 22:
							$result.= 'W';
							break;

					case 23:
							$result.= 'X';
							break;

					case 24:
							$result.= 'Y';
							break;

					case 25:
							$result.= 'Z';
							break;

				}

				$aux-= 26;

			}

			$result.= $col;

			return $result;

		}

		/**
		 * Sends HTTP Headers to Download Archive...
		 *
		 * @param String $filename
		 */
		function _send($filename){

			
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename= $filename");
			header("Content-Transfer-Encoding: binary ");

			global $cnf;

			readfile($cnf['path']['Temp'].$filename);
			unlink($cnf['path']['Temp'].$filename ) ;

		}



	}

?>