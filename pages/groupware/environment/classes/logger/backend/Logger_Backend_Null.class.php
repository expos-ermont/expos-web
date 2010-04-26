<?php

/**
 * Logger backand that does nothing
 *
 * @package Logger
 * @subpackage backends
 * @author Ignacio de Soto <ignacio.desoto@opengoo.org>
 */
class Logger_Backend_Null implements Logger_Backend {

	// ---------------------------------------------------
	//  Backend interface implementation and utils
	// ---------------------------------------------------

	public function saveSessionSet($sessions) {
		return true;
	} // saveSessionSet

	public function saveSession(Logger_Session $session) {
		return true;
	} // saveSession

} // Logger_Backend_Null

?>