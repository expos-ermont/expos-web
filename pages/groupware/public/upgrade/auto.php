<?php
set_time_limit(0);
chdir("../..");
$version = $_GET['version'];
$zipurl = $_GET['url'];
if (!isset($zipurl)) {
	die("Error: You need to give an url to the zip file.");
}
if (isset($version)) {
	$zipname = "opengoo_" . str_replace(" ", "_", $version) . ".zip";
} else {
	$zipname = rand() . ".zip";
}
try {
	$in = fopen($zipurl, "r");
	$zippath = "tmp/" . $zipname;
	$out = fopen($zippath, "w");
	fwrite($out, stream_get_contents($in));
	fclose($out);
	fclose($in);
	$zip = zip_open($zippath);
	if (!is_resource($zip)) die("Error: Unable to open zip file.");
	while ($zip_entry  = zip_read($zip)) {
		$completePath = dirname(zip_entry_name($zip_entry));
		$completeName = zip_entry_name($zip_entry);
		$completePath = substr($completePath, strpos($completePath, "opengoo") + strlen("opengoo") + 1);
		$completeName = substr($completeName, strpos($completeName, "opengoo") + strlen("opengoo") + 1);

		@mkdir($completePath, true);

		if (zip_entry_open($zip, $zip_entry, "r")) {
			if ($fd = @fopen($completeName, 'w')) {
				fwrite($fd, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
				fclose($fd);
			} else {
				// Empty directory
				@mkdir($completeName);
			}
			zip_entry_close($zip_entry);
		}
	}
	zip_close($zip);
} catch (Error $ex) {
	die($ex->getMessage());
}
header("Location: index.php?upgrade_to=" . urlencode($version));
?>