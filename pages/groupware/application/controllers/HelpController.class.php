<?php

/**
 * Help controller
 *
 * @version 1.0
 * @author Carlos Palma <chonwil@gmail.com>
 */
class HelpController extends ApplicationController {
 	
	/* Construct the HelpController
	 *
	 * @access public
	 * @param void
	 * @return HelpController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct
	
	function view_message(){
		
	}
	
	function get_help_content(){
		if(!array_var($_GET, 'template')) return;
		$template = array_var($_GET, 'template');
		ajx_current("empty");
		ajx_extra_data(array("content" => load_help($template), "is_help_data" => 1));
	}
} // HelpController

?>