<?php
class PharauroaMessageP2SCreateAccount extends PharauroaMessage{
	/** proxy credentials */
	private $credentials;

	/** acting for this ip-address */
	private $forwardedFor;

	/** Desired username */
	private $username;

	/** Desired password */
	private $password;

	/** email address for whatever thing it may be needed. */
	private $email;

	/** Constructor for allowing creation of an empty message */
	public function __construct() {
		parent::__construct(PharauroaMessageType::P2S_CREATEACCOUNT);
	}

	/**
	 * Constructor with username, password
	 * and email associated to the account to be created.
	 *
	 * @param username desired username
	 * @param password desired password
	 * @param email email of the player
	 */
	public function init($credentials, $username, $password, $email) {
		$this->credentials = $credentials;
		$this->forwardedFor = $_SERVER['REMOTE_ADDR'];
		$this->username = $username;
		$this->password = $password;
		$this->email = $email;
	}

	
	/**
	 * Returns desired account's username
	 * @return desired account's username
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * Returns desired account's password
	 * @return desired account's password
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * Returns the account associated email.
	 * @return the account associated email.
	 */
	public function getEmail() {
		return $this->email;
	}

	public function writeObject(&$out) {
		parent::writeObject($out);
		$out->writeString($this->credentials);
		$out->writeString($this->forwardedFor);
		$out->writeString($this->username);
		$out->writeString($this->password);
		$out->writeString($this->email);
	}

	public function readObject(&$in) {
		parent::readObject(§in);
		$this->credentials = $in->readString();
		$this->forwardedFor = $in->readString();
		$this->username = $in->readString();
		$this->password = $in->readString();
		$this->email = $in->readString();

		if ($this->MessageType != PharauroaMessageType::P2S_CREATEACCOUNT) {
			throw new PharauroaIOException('Invalid message type in readObject');
		}
	}
}