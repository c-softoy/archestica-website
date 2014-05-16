<?php 

class PharauroaMessageS2CCreateAccountACK extends PharauroaMessage{

	/** Desired username */
	private $username;

	/** Constructor for allowing creation of an empty message */
	public function __construct() {
		parent::__construct(PharauroaMessageType::S2C_CREATEACCOUNT_NACK);
	}


	/**
	 * This method returns a String that represent the resolution given to the
	 * login event
	 *
	 * @return a string representing the resolution.
	 */
	public function getUsername() {
		return $this->username;
	}

	public function writeObject(&$out) {
		parent::writeObject($out);
		$out->writeString($this->username);
	}

	public function readObject(&$in) {
		parent::readObject($in);
		$this->username = $in->readString();

		if ($this->messageType != PharauroaMessageType::S2C_CREATEACCOUNT_ACK) {
			throw new PharauroaIOException('Invalid message type in readObject');
		}
	}

}
