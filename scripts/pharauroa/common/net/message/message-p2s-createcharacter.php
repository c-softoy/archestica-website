<?php
class PharauroaMessageP2SCreateCharacter extends PharauroaMessage{
	/** proxy credentials */
	private $credentials;

	/** acting for this ip-address */
	private $forwardedFor;

	/** username */
	private $username;

	/** name of new character */
	private $character;

	/** RPObject template */
	private $template;

	/** Constructor for allowing creation of an empty message */
	public function __construct() {
		parent::__construct(PharauroaMessageType::P2S_CREATECHARACTER);
	}

	/**
	 * Constructor with username, password
	 * and email associated to the account to be created.
	 *
	 * @param username username
	 * @param character name of new character
	 * @param template template for the new object
	 */
	public function init($credentials, $username, $character, $template) {
		$this->credentials = $credentials;
		$this->forwardedFor = $_SERVER['REMOTE_ADDR'];
		$this->username = $username;
		$this->character = $character;
		$this->template = $template;
	}

	
	/**
	 * Returns account's username
	 *
	 * @return account's username
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * Returns desired character name
	 *
	 * @return desired character name
	 */
	public function getCharacter() {
		return $this->character;
	}

	/**
	 * Returns the template
	 * @return the template
	 */
	public function getTemplate() {
		return $this->template;
	}

	public function writeObject(&$out) {
		parent::writeObject($out);
		$out->writeString($this->credentials);
		$out->writeString($this->forwardedFor);
		$out->writeString($this->username);
		$out->writeString($this->character);
		$this->template->writeObject($out);
	}

	public function readObject(&$in) {
		parent::readObject(§in);
		$this->credentials = $in->readString();
		$this->forwardedFor = $in->readString();
		$this->username = $in->readString();
		$this->character = $in->readString();
		$this->template = new PharauroaRPObject();
		$this->template->readObject($in);

		if ($this->messageType != PharauroaMessageType::P2S_CREATECHARACTER) {
			throw new PharauroaIOException('Invalid message type in readObject');
		}
	}
}