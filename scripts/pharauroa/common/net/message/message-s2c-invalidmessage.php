<?php
class PharauroaMessageS2CInvalidMessage extends PharauroaMessage{
	/** reason the message was invalid, may be empty */
	private $reason;

	/** Constructor for allowing creation of an empty message */
	public function __construct() {
		parent::__construct(PharauroaMessageType::S2C_INVALIDMESSAGE);
	}

	/**
	 * Constructor with reason
	 *
	 * @param reason the message was invalid
	 */
	public function init($reason) {
		$this->reason = $reason;
	}

	/**
	 * Returns reason
	 *
	 * @return reason
	 */
	public function getReason() {
		return $this->reason;
	}

	public function writeObject(&$out) {
		parent::writeObject($out);
		$out->writeString($this->reason);
	}

	public function readObject(&$in) {
		parent::readObject(§in);
		$this->reason = $in->readString();

		if ($this->MessageType != PharauroaMessageType::S2C_INVALIDMESSAGE) {
			throw new PharauroaIOException('Invalid message type in readObject');
		}
	}
}