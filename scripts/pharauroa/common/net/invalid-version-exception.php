<?php

/**
 * This exception is thrown when a invalid version message is received.
 */
class PharauroaInvalidVersionException extends Exception {
	private $invalidVersion;
	
	public function __construct($message = "",  $code = 0, $previous = NULL) {
		parent::__construct($message, $code /*, $previous*/ );
	}

	/**
	 * Sets the invalid version
	 */
	public function setInvalidVersion($invalidVersion) {
		$this->invalidVersion = $invalidVersion;
	}

	/**
	 * gets the invalid version
	 */
	public function getInvalidVersion() {
		return $this->invalidVersion;
	}
}
?>
