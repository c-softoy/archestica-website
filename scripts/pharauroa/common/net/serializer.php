<?php

class PharauroaSerializer {
	
	private $data;
	private $protocolVersion = PHARAUROA_NETWORK_PROTOCOL_VERSION;

	public function setProtocolVersion($protocolVersion) {
		$this->$protocolVersion = min($protocolVersion, PHARAUROA_NETWORK_PROTOCOL_VERSION);
	}

	public function getProtocolVersion() {
		return $this->protocolVersion;
	}
	
	public function writeByte($byte) {
		$this->data = $this->data . chr($byte);
	}
	
	public function writeShort($short) {
		$this->data = $this->data . pack("s", $short);
	}

	public function writeInt($int) {
		$this->data = $this->data . pack("I", $int);
	}

	public function writeString($string) {
		$this->writeInt(strlen($string));
		$this->data = $this->data . $string;
	}

	public function write256LongString($string) {
		if (strlen($string) > 256) {
			// TODO
		}
		$this->writeByte(strlen($string));
		$this->data = $this->data . $string;
	}

	public function getData() {
		return pack("I", strlen($this->data) + 4).$this->data;
	}
}