<?php


/**
 * RPObject
 */
class PharauroaRPObject extends PharauroaAttributes {

	// TODO: Add support for RPClass


	public function writeObject(&$out) {
		parent::writeObject($out);
		
		$out->writeByte(0); // not "full detail mode"
		$out->writeInt(0); // TODO: add support for RPSlots
		$out->writeInt(0); // TODO: add support for RPLinks
		if ($out->getProtocolVersion() >= 33) {
			$out->writeInt(0); // TODO: add support for maps
		}
		$out->writeInt(0); // TODO: add support for RPEvents
	}
	

	public function readObject(&$in) {
		parent::readObject($im);
		// TODO: implement Attributes.readObject
	}
}