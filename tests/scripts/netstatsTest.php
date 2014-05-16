<?php
require_once('scripts/netstats.php');

/**
* Tests for netstats
*
* @author hendrik
*/
class NetstatsTest extends PHPUnit_Framework_TestCase {

	public function testIsErrorToken() {
		$netstats = new NetStats();
 		$this->assertEquals(true, $netstats->isErrorToken("*"));
 		$this->assertEquals(true, $netstats->isErrorToken("!H"));
	 	$this->assertEquals(false, $netstats->isErrorToken("123"));
	}

	public function testParseLine() {
		$netstats = new NetStats();

 		$this->assertEquals(
 			array(TracerouteLine::create(1, null, '192.168.25.208', null, 0, array(0.525, 0.508, 0.496))),
 			$netstats->parseLine(' 1  192.168.25.208  0.525 ms  0.508 ms  0.496 ms'));

		$this->assertEquals(
			array(TracerouteLine::create(2, 'dslc-082-083-192-001.pools.arcor-ip.net', '(82.83.192.1)', '[AS3209]', 0, array(14.647, 14.650, 18.621))),
			$netstats->parseLine(' 2  dslc-082-083-192-001.pools.arcor-ip.net (82.83.192.1) [AS3209]  14.647 ms  14.650 ms  18.621 ms'));

		$this->assertEquals(
			array(TracerouteLine::create(9, 'hos-tr4.ex3k11.rz14.hetzner.de', '(213.239.224.236)', '[AS24940]', 0, array(63.559, 67.520)),
				  TracerouteLine::create(9, 'hos-tr2.ex3k11.rz14.hetzner.de', '(213.239.224.172)', '[AS24940]', 2, array(71.528))
			),
			$netstats->parseLine(' 9  hos-tr4.ex3k11.rz14.hetzner.de (213.239.224.236) [AS24940]  63.559 ms  67.520 ms hos-tr2.ex3k11.rz14.hetzner.de (213.239.224.172) [AS24940]  71.528 ms'));

		$this->assertEquals(
			array(TracerouteLine::create(8, 'hos-bb1.juniper1.fs.hetzner.de',   '(213.239.240.242)', '[AS24940]', 0, array(59.577)),
				  TracerouteLine::create(8, 'hos-bb1.juniper2.rz14.hetzner.de', '(213.239.240.247)', '[AS24940]', 1, array(63.571)),
				  TracerouteLine::create(8, 'hos-bb1.juniper1.fs.hetzner.de',   '(213.239.240.242)', '[AS24940]', 2, array(63.568))
			),
			$netstats->parseLine('8  hos-bb1.juniper1.fs.hetzner.de (213.239.240.242) [AS24940]  59.577 ms hos-bb1.juniper2.rz14.hetzner.de (213.239.240.247) [AS24940]  63.571 ms hos-bb1.juniper1.fs.hetzner.de (213.239.240.242) [AS24940]  63.568 ms'));

		$this->assertEquals(
			array(TracerouteLine::create(10, null, '82.83.238.224', null, 0, array(25.492, '*', '*'))),
			$netstats->parseLine('10  82.83.238.224  25.492 ms * *'));

		$this->assertEquals(
			array(TracerouteLine::create(2, null, '213.239.224.225', null, 0, array('*', 0.143, 0.149))),
			$netstats->parseLine(' 2  * 213.239.224.225  0.143 ms  0.149 ms'));

		$this->assertEquals(
			array(TracerouteLine::create(2, null, '213.239.224.225', null, 0, array('*', '*', 0.419))),
			$netstats->parseLine(' 2  * * 213.239.224.225  0.419 ms'));

		$this->assertEquals(
			array(TracerouteLine::create(10, null, '82.83.238.224', null, 0, array(25.492, '!H', '*'))),
			$netstats->parseLine('10  82.83.238.224  25.492 ms !H *'));

		$this->assertEquals(
			array(TracerouteLine::create(10, null, '82.83.238.224', null, 0, array(25.492, '!H', '*', 23.120))),
			$netstats->parseLine('10  82.83.238.224  25.492 ms !H * 23.120 ms', 4));
		
		$this->assertEquals(
			array(TracerouteLine::create(7, null, '', null, 0, array('*', '*', '*'))),
			$netstats->parseLine(' 7  * * *', 3));

	}

}
