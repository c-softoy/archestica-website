<?php

class PharauroaMessageFactory {
	private $sock;

	function __construct($sock) {
		$this->sock = $sock;
	}

	public function readMessage() {
		$data = socket_read($this->sock, 4);
		$temp = unpack("I", $data);
		$size = $temp[1];
		$data = socket_read($this->sock, $size);

		// TODO: make sure that $data contains the complete message by calling it in a loop

		$deserializer = new PharauroaDeserializer($data);
		$type = ord($data[1]);
		if ($type == PharauroaMessageType::S2C_CONNECT_NACK) {
			$message = new PharauroaMessageS2ConnectNACK();
		} else if ($type == PharauroaMessageType::S2C_CREATEACCOUNT_ACK) {
			$message = new PharauroaMessageS2CCreateAccountACK();
		} else if ($type == PharauroaMessageType::S2C_CREATEACCOUNT_NACK) {
			$message = new PharauroaMessageS2CCreateAccountNACK();
		} else if ($type == PharauroaMessageType::S2C_CREATECHARACTER_ACK) {
			$message = new PharauroaMessageS2CCreateCharacterACK();
		} else if ($type == PharauroaMessageType::S2C_CREATECHARACTER_NACK) {
			$message = new PharauroaMessageS2CCreateCharacterNACK();
		} else if ($type == PharauroaMessageType::S2C_INVALIDMESSAGE) {
			$message = new PharauroaMessageS2CInvalidMessage();
		} else {
			throw new PharauroaIOException('Unknown message type: '.$type);
		}
		$message->readObject($deserializer);
		//var_dump($message);
		return $message;
	}

}

?>