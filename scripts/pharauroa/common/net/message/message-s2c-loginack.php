<?php 

class PharauroaMessageS2CLoginACK extends PharauroaMessage{

	/** Desired username */
	private $previousLogins;

	/** Constructor for allowing creation of an empty message */
	public function __construct() {
		parent::__construct(PharauroaMessageType::S2C_CREATEACCOUNT_ACK);
	}

	/**
	 * Constructor with previousLogins
	 *
	 * @param previousLogins previousLogins
	 */
	public function init($previousLogins) {
		$this->previousLogins = $previousLogins;
	}

	/**
	 * Returns an array of past login events
	 *
	 * @return loginEvents
	 */
	public function getPreviousLogins() {
		return $this->previousLogins;
	}


	public function writeObject(&$out) {
		parent::writeObject($out);

		$out->writeByte(count($this->previousLogins));
		foreach ($this->previousLogins as $event) {
			$out->write255LongString($event);
		}
	}

	public function readObject(&$in) {
		parent::readObject(§in);
		$size = $in->readByte();
		$this->previousLogins = array();
		for ($i = 0; $i < $size; $i++) {
			$previousLogins[] = $in->read255LongString();
		}

		if ($this->messageType != PharauroaMessageType::S2C_LOGIN_ACK) {
			throw new PharauroaIOException('Invalid message type in readObject');
		}
	}

}
