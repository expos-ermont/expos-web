<?php

class LocalizationConfigHandler extends ConfigHandler {

	/**
	 * Array of available locales (subfolders of languages folder)
	 *
	 * @var array
	 */
	private $available_locales = array();

	/**
	 * Constructor
	 *
	 * @param void
	 * @return LocalizationConfigHandler
	 */
	function __construct() {
		$language_dir = with_slash(ROOT . "/language");

		if (is_dir($language_dir)) {
			$d = dir($language_dir);
			while (($entry = $d->read()) !== false) {
				if (str_starts_with($entry, '.') || $entry == "CVS") {
					continue;
				} // if

				if (is_dir($language_dir . $entry)) {
					$this->available_locales[] = $entry;
				} // if
			} // while
			$d->close();
		} // if
	} // __construct

	/**
	 * Render form control
	 *
	 * @param string $control_name
	 * @return string
	 */
	function render($control_name) {
		$options = array();

		foreach($this->available_locales as $locale) {
			$option_attributes = $this->getValue() == $locale ? array('selected' => true) : null;
			$options[] = option_tag($locale, $locale, $option_attributes);
		} // foreach

		return select_box($control_name, $options);
	} // render

} // LocalizationConfigHandler

?>