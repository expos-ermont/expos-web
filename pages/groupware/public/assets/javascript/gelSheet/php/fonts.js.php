function loadFonts() {
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
	var list = new Array();
	<?php
		//include_once("Connection.php");
		include_once("../php/config/settings.php");
		
		$conn = new Connection();
		$sql = "select * from ". $cnf['tableName']['fonts'] ;
		$res = mysql_query($sql);
		while ($row = mysql_fetch_object($res)){ 
	?>
			
	list[<?php echo $row->FontId ?>]= "<?php echo $row->FontName ?>";	

	<?php			
		}
	?>	
	return list;	
}
