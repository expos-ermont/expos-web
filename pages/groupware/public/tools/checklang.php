<?php
$allowed = include 'access.php';
if (!in_array('checklang.php', $allowed)) die("This tool is disabled.");

header("Content-Type: text/html");
define('LANG_DIR', 'language');
define('TEST_LIST_PATH', 'all_langs.txt');
chdir("../..");
?>
<html>
<head>
<style>
body {
	padding: 5px 30px;
	font-family: Arial, sans-serif, serif;
	font-size: 12px;
}
.missing {
	color: red;
}
.ok {
	color: green;
}
.error {
	color: blue;
}
</style>
<script>
function addLangs(langs) {
	locales[locale][file] = {};
	for (var k in langs) {
		locales[locale][file][k] = langs[k];
	}
}
function escLang(text) {
	return text.replace(/'/g, "\\'").replace(/\n/g, "\\n").replace(/</g, "&lt;");
}
var locales = {}
</script>
</head>
<body>
<h1>Check lang</h1>
<p>This script allows you to compare translation files in some locale with translation files in english.
If you are translating to a locale this script can help you detect what translation keys you have missed.</p>
<p>You can use the <a href="translate.php">Translate OpenGoo</a> tool (if enabled) to add missing translations.</p>
<p>Select a locale from the list below. The following locales have been detected on this installation:</p> 
<?php
$handle = opendir(LANG_DIR);
echo "<ul>";
while (false !== ($file = readdir($handle))) {
	if ($file != "." && $file != ".." && $file != "CVS" && $file != "en_us" && is_dir(LANG_DIR . "/" . $file)) {
		echo "<li><a href=\"checklang.php?a=en_us&b=$file\">$file</a><br /></li>\n";
	}
}
echo "</ul>";
closedir($handle);
$a = isset($_GET["a"])?$_GET["a"]:null;
$b = isset($_GET["b"])?$_GET["b"]:null;
if (isset($a) && isset($b)) {
	echo "<h2>$b translation files</h2>";
	echo "<p>Next you can see the missing translation files in red, and the missing translation keys under each file, along with the english text</p>";
	$locales[$a] = array();
	$locales[$b] = array();
	echo "<script>
		locales['$a'] = {};
		locales['$b'] = {};
		base = '$a';
	</script>\n";
	foreach ($locales as $l => $v) {
		$handle = opendir(LANG_DIR . "/" . $l);
		while (false !== ($file = readdir($handle))) {
			if (!is_dir(LANG_DIR . "/" . $l . "/" . $file) && substr($file, -4) == ".php") {
				$locales[$l][$file] = include LANG_DIR . "/" . $l . "/" . $file;
			} else if (!is_dir(LANG_DIR . "/" . $l . "/" . $file) && substr($file, -3) == ".js") {
				echo "<script>";
				echo "locale = '$l';";
				echo "file = '$file';";
				echo "try {//";
				include LANG_DIR . "/" . $l . "/" . $file;
				echo "} catch (e) {
					document.write('<span class=\"error\">There was an error loading file $file: \"' + e.message + '\"</span>');
				}";
				echo "if (typeof _lang == 'object') {
					addLangs(_lang);
					_lang = false;
				}";
				echo "</script>";
			}
		}
		closedir($handle);
	}
	
	echo "<pre>";
	
	foreach ($locales as $l => $arr) {
		if ($l != $a) {
			echo "<h3>$l PHP files</h3>";
			foreach ($locales[$a] as $engf => $engfv) {
				$thisfv = $arr[$engf];
				if (!isset($thisfv)) {
					echo " - <span class=\"missing\">$engf missing</span>\n";
				} else {
					echo " - <span class=\"present\">$engf</span>\n";
					$count = 0;
					foreach ($engfv as $a => $b) {
						if (!isset($thisfv[$a])) {
							echo "    '$a' => '" . str_replace("'", "\\'", $b) . "',\n";
							$count++;
						}
					}
					if ($count == 0) {
						echo "      <span class=\"ok\">File complete</span>\n";
					}
				}
			}
		}
	}
	
	echo "<script>
	for (var l in locales) {
		if (l != base) {
			document.write('<h3>' + l + ' javascript files</h3>');
			for (var f in locales[base]) {
				var file = locales[l][f];
				if (!file) {
					document.write(' - <span class=\"missing\">' + f + ' missing</span>\\n');
				} else {
					document.write(' - <span class=\"present\">' + f + '</span>\\n');
					var count = 0;
					for (var k in locales[base][f]) {
						if (!file[k]) {
							document.write('    \'' + k + '\': \'' + escLang(locales[base][f][k]) + '\',\\n');
							count++;
						}
					}
					if (count == 0) {
						document.write('      <span class=\"ok\">File complete</span>\\n');
					}
				}
			}
		}
	}
	</script>";
	
	echo "</pre>";
	
}
elseif (isset($a)){
	
//	foreach ($locales as $l => $v) {
	$handle = opendir(LANG_DIR . "/" . $a);
	while (false !== ($file = readdir($handle))) {
		if (!is_dir(LANG_DIR . "/" . $a . "/" . $file) && substr($file, -4) == ".php") {
			$locales[$a][$file] = include LANG_DIR . "/" . $a . "/" . $file;
		} else if (!is_dir(LANG_DIR . "/" . $a . "/" . $file) && substr($file, -3) == ".js") {
			echo "<script>";
			echo "locale = '$a';";
			echo "file = '$file';";
			echo "try {";
			include LANG_DIR . "/" . $a . "/" . $file;
			echo "} catch (e) {
				document.write('<span class=\"error\">There was an error loading file $file: \"' + e.message + '\"</span>');
			}";
			echo "if (typeof _lang == 'object') {
				addLangs(_lang);
				_lang = false;
			}";
			echo "</script>";
		}
	}
	closedir($handle);
//	}
	echo "<pre>";
	$langlist = file(TEST_LIST_PATH,   FILE_IGNORE_NEW_LINES   |   FILE_SKIP_EMPTY_LINES   );
	foreach ($langlist as $line_num => $line) {
//    	echo "Line #<b>{$line_num}</b> : " . htmlspecialchars($line) . "<br />\n";
    	$found = false;
    	foreach ($locales[$a] as $engf => $engfv) {
    		//for each file
			if (!$found && isset($locales[$a] [$engf])) {
				if (isset($locales[$a][$engf][$line])){
					$found = true;
				}
			}
		}
		if(!$found)
			echo "<span class=\"missing\">$line missing</span>\n";
//		else echo "-$line_num-$line-OK-\n";
	}
}
	
?>

</body>
</html>