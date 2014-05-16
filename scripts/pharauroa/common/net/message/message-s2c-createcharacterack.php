<?php 

class PharauroaMessageS2CCreateCharacterACK extends PharauroaMessage{

	/** The approved character name */
	private $character;

	/** the approved avatar configuration */
	private $template;
	
	/** Constructor for allowing creation of an empty message */
	public function __construct() {
		parent::__construct(PharauroaMessageType::S2C_CREATECHARACTER_ACK);
	}

	/**
	 * This method returns the character name
	 *
	 * @return character name
	 */
	public function getCharacter() {
		return $this->character;
	}

	/**
	 * This method returns the approved template object
	 *
	 * @return template
	 */
	public function getTemplate() {
		return $this->template();
	}

	public function writeObject(&$out) {
		parent::writeObject($out);
		$out->writeString($this->username);
		$this->template->writeObject($out);
	}

	public function readObject(&$in) {
		parent::readObject($in);
		$this->username = $in->readString();
		$this->template = new PharauroaRPObject();
		$this->template->readObject($in);

		if ($this->messageType != PharauroaMessageType::S2C_CREATECHARACTER_ACK) {
			throw new PharauroaIOException('Invalid message type in readObject');
		}
	}

}
