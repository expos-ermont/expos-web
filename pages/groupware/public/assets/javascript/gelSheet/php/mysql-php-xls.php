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
// Edit the $Host, $User $Password, $DBName and $TableName vars only! //
$Host = "localhost";
$User = "root";
$Password = "ch0r1c1t0";
$DBName = "opengoo";
$TableName = "books";

// DO NOT EDIT BELOW HERE //
$link = mysql_connect ($Host, $User, $Password) or die('Could not connect: ' . mysql_error());
mysql_select_db($DBName) or die('Could not select database');

$select = "SELECT * FROM `".$TableName."`";
$export = mysql_query($select);
$fields = mysql_num_fields($export);

for ($i = 0; $i < $fields; $i++) {
	$csv_output .= mysql_field_name($export, $i) . "\t";
}

while($row = mysql_fetch_row($export)) {
	$line = '';
	foreach($row as $value) {
		if ((!isset($value)) OR ($value == "")) {
			$value = "\t";
		} else {
			$value = str_replace('"', '""', $value);
			$value = '"' . $value . '"' . "\t";
		}
		$line .= $value;
	}
	$data .= trim($line)."\n";
}
$data = str_replace("\r","",$data);

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=database_dump.csv");
header("Pragma: no-cache");
header("Expires: 0");
print $csv_output."\n".$data;
exit;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Download MySQL Table Code</title>
</head>
<body>

</body>
</html>
