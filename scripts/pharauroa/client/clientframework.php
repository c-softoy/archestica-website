<?php
/**
 * pharauroa client framework
 */
class PharauroaClientFramework {
	private $server;
	private $port;
	private $credentials;
	
	/**
	 * creates a new PharauroaClientFramework
	 *
	 * @param $server name or ip-address of server
	 * @param $port port of server
	 * @param $credentials credentials for proxy message authentication
	 */
	public function __construct($server, $port, $credentials) {
		$this->server = $server;
		$this->port = $port;
		$this->credentials = $credentials;
	}

	/**
	 * creates a new account
	 */
	public function createAccount($username, $password, $email) {
		// Create message
		$message = new PharauroaMessageP2SCreateAccount();
		$message->init($this->credentials, $username, $password, $email);

		try {
			$answer = $this->sendMessage($message);
		} catch (PharauroaIOException $e) {
			error_log($e);
			return new PharauroaResult(PharauroaResult::FAILED_OFFLINE);
		}
	
		if ($answer instanceof PharauroaMessageS2CCreateAccountACK) {
			return new PharauroaResult(PharauroaResult::OK_CREATED);
		} else if ($answer instanceof PharauroaMessageS2CCreateAccountNACK) {
			return $answer->getResult();
		} else {
			return new PharauroaResult(PharauroaResult::FAILED_EXCEPTION);
		}
	}

	/**
	 * creates a new character
	 */
	public function createCharacter($username, $character, $template) {
		$message = new PharauroaMessageP2SCreateCharacter();
		$message->init($this->credentials, $username, $character, $template);

		try {
			$answer = $this->sendMessage($message);
		} catch (PharauroaIOException $e) {
			error_log($e);
			return new PharauroaResult(PharauroaResult::FAILED_OFFLINE);
		}

		if ($answer instanceof PharauroaMessageS2CCreateCharacterACK) {
			return new PharauroaResult(PharauroaResult::OK_CREATED);
		} else if ($answer instanceof PharauroaMessageS2CCreateCharacterNACK) {
			return $answer->getResult();
		} else {
			return new PharauroaResult(PharauroaResult::FAILED_EXCEPTION);
		}
	}

	/**
	 * sends a message to the server and returns the answer
	 */
	private function sendMessage($message) {
		// Serialize message
		$serializer = new PharauroaSerializer();
		$message->writeObject($serializer);
		$data = $serializer->getData();

		// connect and write message
		$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		$res = socket_connect($sock, $this->server, $this->port);
		if ($res === FALSE) {
			throw new PharauroaIOException(socket_strerror(socket_last_error()));
		}
		socket_write($sock, $data, strlen($data));

		// read annswer and close connection
		$factory = new PharauroaMessageFactory($sock);
		$answer = $factory->readMessage();
		socket_close($sock);

		return $answer;
	}
}
?>
