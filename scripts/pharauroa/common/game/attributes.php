<?php

/**
 * Attributes
 */
class PharauroaAttributes {
	// TODO: Add support for RPClass
	private $content = array();

	public function put($key, $value) {
		$this->content[$key] = $value;
	}

	public function writeObject(&$out) {
		$out->writeString(""); // rpClass.getName()
		$out->writeInt(count($this->content));

		foreach ($this->content As $key => $value) {	
			$out->writeShort(-1);
			$out->writeString($key);
			$out->writeString($value);
		}
	}

	public function readObject(&$in) {
		// TODO: implement Attributes.readObject
	}
}