<?php
class PharauroaMessageS2CConnectNACK extends PharauroaMessage{

	/** Constructor for allowing creation of an empty message */
	public function __construct() {
		parent::__construct(PharauroaMessageType::S2C_CONNECT_NACK);
	}

	public function writeObject(&$out) {
		parent::writeObject($out);
	}

	public function readObject(&$in) {
		parent::readObject(§in);

		if ($this->MessageType != PharauroaMessageType::S2C_CONNECT_NACK) {
			throw new PharauroaIOException('Invalid message type in readObject');
		}
	}
}