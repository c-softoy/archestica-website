<?php

class PharauroaResult {

	/** Account was created correctly. */
	const OK_CREATED = 0;

	/**
	 * Account was not created because one of the important parameters was
	 * missing.
	 */
	const FAILED_EMPTY_STRING = 1;

	/**
	 * Account was not created because an invalid characters (letter, sign,
	 * number) was used
	 */
	const FAILED_INVALID_CHARACTER_USED = 2;

	/**
	 * Account was not created because any of the parameters are either too long
	 * or too short.
	 */
	const FAILED_STRING_SIZE = 3;

	/** Account was not created because this account already exists. */
	const FAILED_PLAYER_EXISTS = 4;

	/** Account was not created because there was an unspecified exception. */
	const FAILED_EXCEPTION = 5;

	/** Character was not created because this character already exists. */
	const FAILED_CHARACTER_EXISTS = 6;

	/**
	 * The template passed to the create character method is not valid because
	 * it fails to pass the RP rules.
	 */
	const FAILED_INVALID_TEMPLATE = 7;

	/**
	 * String is too short
	 *
	 * @since 2.1
	 */
	const FAILED_STRING_TOO_SHORT = 8;

	/**
	 * String is too long
	 *
	 * @since 2.1
	 */
	const FAILED_STRING_TOO_LONG = 9;

	/**
	 * Name is reserved
	 *
	 * @since 2.1
	 */
	const FAILED_RESERVED_NAME = 10;

	/**
	 * Password is too close to the username
	 *
	 * @since 2.1
	 */
	const FAILED_PASSWORD_TOO_CLOSE_TO_USERNAME = 11;

	/**
	 * Password is too weak.
	 *
	 * @since 2.1
	 */
	const FAILED_PASSWORD_TO_WEAK = 12;

	/**
	 * Too many accounts were created from this ip-address recently.
	 *
	 * @since 3.5
	 */
	const FAILED_TOO_MANY = 13;

	/**
	 * Server is offline.
	 *
	 * @since 3.8.5
	 */
	const FAILED_OFFLINE = 14;
	
	static $reasons = array("Account was created correctly.",
		"Account was not created because one of the important parameters was missing.",
		"Account was not created because an invalid character (special letters, signs, numbers) was used.",
		"Account was not created because any of the parameters are either too long or too short.",
		"Account was not created because this account already exists.",
		"Account was not created because there was an unspecified exception.",
		"Character was not created because this Character already exists.",
		"The template passed to the create character method is not valid because it fails to pass the RP rules.",
		"Account was not created because at least one of the parameters was too short.",
		"Account was not created because at least one of the parameters was too long.",
		"Account was not created because the name is reserved (or contains a reserved name).",
		"Account was not created because the password is too close to the username.",
		"Account was not created because the password is too weak.",
		"Account was not created because the account creation limit for your network was reached.\nPlease try again later.",
		"Creation failed because the server is temporarily not available. Please try again later."
	);

	public function __construct($result) {
		$this->result = $result;
	}

	public function wasSuccessful() {
		return $this->result == PharauroaResult::OK_CREATED;
	}

	public function getMessage() {
		return PharauroaResult::$reasons[$this->result];
	}

	public function getResult() {
		return $this->result;
	}
}