<?php

/**
 * Tools for OpenGoo development
 *
 * @version 1.0
 * @author Ignacio de Soto <ignacio.desoto@gmail.com>
 */
class ToolController extends ApplicationController {

	/**
	 * Construct the ToolController
	 *
	 * @access public
	 * @param void
	 * @return ToolController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'html');
	} // __construct

	function minify() {
		$this->setTemplate(get_template_path("empty"));
		
		if (!logged_user()->isAdministrator()) {
			die("You must be an administrator to run this tool.");
		}
		
		// include libraries
		include_once LIBRARY_PATH . '/jsmin/JSMin.class.php';
		include_once LIBRARY_PATH . '/cssmin/CSSMin.class.php';
		
		// process arguments
		$minify = isset($_GET['minify']);
		
		// process javascripts
		echo "Concatenating javascripts ... \n";
		$files = include "application/layouts/javascripts.php";
		
		$jsmin = "";
		foreach ($files as $file) {
			$jsmin .= file_get_contents("public/assets/javascript/$file") . "\n";
		}
		echo "Done!<br>\n";
		
		if ($minify) {
			echo "Minifying javascript ... \n";
			$jsmin = JSMin::minify($jsmin);
			echo "Done!<br>\n";
		}
		
		echo "Writing to file 'ogmin.js' ... ";
		file_put_contents("public/assets/javascript/ogmin.js", $jsmin);
		echo "Done!<br>";
		
		echo "<br>";
		
		
		// process CSS
		
		function changeUrls($css, $base) {
			return preg_replace("/url\s*\(\s*['\"]?([^\)'\"]*)['\"]?\s*\)/i", "url(".$base."/$1)", $css);
		}
		
		function parseCSS($filename, $filebase, $imgbase) {
			$css = file_get_contents($filebase.$filename);
			$imports = explode("@import", $css);
			$cssmin = changeUrls($imports[0], $imgbase);
			for ($i=1; $i < count($imports); $i++) {
				$split = explode(";", $imports[$i], 2);
				$import = trim($split[0], " \t\n\r\0\x0B'\"");
				$cssmin .= parseCSS($import, $filebase, $imgbase."/".dirname($import));
				$cssmin .= changeUrls($split[1], $imgbase);
			}
			return $cssmin;	
		}
		
		echo "Concatenating CSS ... ";
		$cssmin = parseCSS("website.css", "public/assets/themes/default/stylesheets/", ".");
		echo "Done!<br>";
		
		if ($minify) {
			echo "Minifying CSS ... ";
			$cssmin = CSSMin::minify($cssmin);
			echo "Done!<br>";
		}
		
		echo "Writing to file 'ogmin.css' ... ";
		file_put_contents("public/assets/themes/default/stylesheets/ogmin.css", $cssmin);
		echo "Done!<br>";

	}
	
	function translate() {
		if (!logged_user()->isAdministrator()) {
			die("You must be an administrator to run this tool.");
		}
		
		$download = $_GET['download'];
		if (isset($download)) {
			// download zip file and die
			download_zip_lang($download);
			die();
		}
		
		// save submissions
		$lang = $_POST['lang'];
		$added = 0;
		if (isset($lang)) {
			$file = $_POST['file'];
			$locale = $_POST['locale'];
			$rootfile = LANG_DIR . "/" . $locale . ".php";
			$dirname = LANG_DIR . "/" . $locale;
			$filename = $dirname . "/" . $file;
			if (!is_file($rootfile)) {
				$f = fopen($rootfile, "w");
				fwrite($f, '<?php if(!isset($this) || !($this instanceof Localization)) {
					throw new InvalidInstanceError(\'$this\', $this, "Localization", "File \'" . __FILE__ . "\' can be included only from Localization class");
				} ?>');
				fclose($f);
			}
			if (!is_dir($dirname)) {
				mkdir($dirname);
			}
			if (!is_file($filename)) {
				// create the file
				$f = fopen($filename, "w");
				fclose($f);
			}
			$all = loadFileTranslations($locale, $file);
			if (!is_array($all)) $all = array();
			foreach ($lang as $k => $v) {
				if (trim($v) != "") {
					if (!isset($all[$k])) {
						$added++;
					}
					$all[$k] = $v;
				}
			}
			$f = fopen($filename, "w");
			// write the translations to the file
			if (substr($file, -4) == ".php") {
				fwrite($f, "<?php return array(\n");
				foreach ($all as $k => $v) {
					fwrite($f, "\t'$k' => '" . escape_lang("$v"). "',\n");
				}
				fwrite($f, "); ?>\n");
			} else if (substr($file, -3) == ".js") {
				$total = count($all);
				fwrite($f, "locale = '$locale';\n");
				fwrite($f, "addLangs({\n");
				$count = 0;
				foreach ($all as $k => $v) {
					$count++;
					fwrite($f, "\t'$k': '" . escape_lang($v). "'");
					if ($count == $total) {
						fwrite($f, "\n");
					} else {
						fwrite($f, ",\n");
					}
				}
				fwrite($f, "});\n");
			}
			fclose($f);
		}
		
		$from = array_var($_GET, 'from', 'en_us');
		$to = array_var($_GET, 'to', '');
		
		// load languages
		$languages = array();
		$handle = opendir(LANG_DIR);
		while (false !== ($f = readdir($handle))) {
			if ($f != "." && $f != ".." && $f != "CVS" && $f != $from && is_dir(LANG_DIR . "/" . $f)) {
				$languages[] = $f;
			}
		}
		sort($languages);
		
		if ($to != "") {
			// load from files
			$from_files = array();
			$handle = opendir(LANG_DIR . "/" . $from);
			while (false !== ($file = readdir($handle))) {
				if (is_file(LANG_DIR . "/" . $from . "/" . $file)) {
					$from_files[] = $file;
				}
			}
			sort($from_files);
			tpl_assign('from_files', $from_files);
		}
		
		tpl_assign('added', $added);
		tpl_assign('from', $from);
		tpl_assign('to', $to);
		tpl_assign('languages', $languages);
	}
	
} // TimeController

define('LANG_DIR', 'language');

function escape_lang($string) {
	// TODO: this function needs to be checked for special cases
	// replace multiple backslashes for one
	// (this doesn't allow more than one consecutive backslash but eases escaping the string)
	$string = preg_replace("/[\\\\]+/", "\\", $string);
	// the form sends quotes with a leading backlash, so first remove those extra backslashes and then escape the string
	return str_replace(array("\\'", "\\\"", "'", "\r\n", "\r", "\n"), array("'", "\"", "\\'", "\\n", "\\n", "\\n"), $string);
}

function unescape_lang($string) {
	$string = preg_replace("/[\\\\]+/", "\\", $string);
	return str_replace(array("\\'", "\\n", "\\\\"), array("'", "\n", "\\"), $string);
}

function loadFileTranslations($locale, $file) {
	if (substr($file, -4) == ".php") {
		return include LANG_DIR . "/" . $locale . "/" . $file;
	} else if (substr($file, -3) == ".js") {
		$contents = file_get_contents(LANG_DIR . "/" . $locale . "/" . $file);
		$contents = preg_replace("/.*addLangs\s*\(\s*\{\s*/s", "", $contents);
		$contents = preg_replace("/\s*\}\s*\)\s*;\s*$/", "", $contents);
		$matches = array();
		preg_match_all("/\s*['\"](.*)['\"]\s*:\s*['\"](.*[^\\\\])['\"]\s*,?/", $contents, $matches, PREG_SET_ORDER);
		$lang = array();
		foreach ($matches as $match) {
			$lang[$match[1]] = $match[2];
		}
		return $lang;
	} else {
		return array();
	}
}

function download_zip_lang($locale) {		
	$filename = "tmp/$locale.zip";
	if (is_file($filename)) unlink($filename);
	$zip = new ZipArchive();
	$zip->open($filename, ZIPARCHIVE::OVERWRITE);
	$zip->addFile(LANG_DIR . "/$locale.php", "$locale.php");
	$zip->addEmptyDir($locale);
	$dir = opendir(LANG_DIR . "/" . $locale);
	while (false !== ($file = readdir($dir))) {
		if ($file != "." && $file != ".." && $file != "CVS") {
			$zip->addFile(LANG_DIR . "/$locale/$file", "$locale/$file");
		}
	}
	closedir($dir);
	$zip->close();
	header("Cache-Control: public");
	header("Expires: -1");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Content-Type: application/zip");
	header("Content-Length: " . (string) filesize($filename));
	header("Content-Disposition: 'attachment'; filename=\"$locale.zip\"");
	header("Content-Transfer-Encoding: binary");
	readfile($filename);
	die();
}

?>