<?php

/**
 * This exception is thrown in case of an input/output error.
 */
class PharauroaIOException extends Exception {
	
	public function __construct($message = "",  $code = 0, $previous = NULL) {
		parent::__construct($message, $code /*, $previous*/);
	}
}
?>
