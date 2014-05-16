<?php 

class PharauroaMessageS2CCreateAccountNACK extends PharauroaMessage{

	/** Desired username */
	private $reason;

	/** Constructor for allowing creation of an empty message */
	public function __construct() {
		parent::__construct(PharauroaMessageType::S2C_CREATEACCOUNT_NACK);
	}

	/**
	 * This method returns the resolution of the login event
	 *
	 * @return a byte representing the resolution given.
	 */
	public function getResolutionCode() {
		return $this->reason->getResult();
	}

	/**
	 * This method returns the PharauroaResult.
	 */
	public function getResult() {
		return $this->reason;
	}

	/**
	 * This method returns a String that represent the resolution given to the
	 * login event
	 *
	 * @return a string representing the resolution.
	 */
	public function getResolution() {
		return $this->reason->getMessage();
	}

	public function writeObject(&$out) {
		parent::writeObject($out);
		$out->writeByte(($this->reason));
	}

	public function readObject(&$in) {
		parent::readObject($in);
		$this->reason = new PharauroaResult($in->readByte());

		if ($this->messageType != PharauroaMessageType::S2C_CREATEACCOUNT_NACK) {
			throw new PharauroaIOException('Invalid message type in readObject');
		}
	}

}
