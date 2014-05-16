<?php
require_once('lib/openid/lightopenid.php');

class LightOpenidTest extends PHPUnit_Framework_TestCase {

	public function testhostExists() {
		$openid = new LightOpenID();
		$this->assertTrue($openid->hostExists("www.google.com"));
		$this->assertTrue($openid->hostExists("localhost"));
		$this->assertTrue($openid->hostExists("ip6-loopback"));
		$this->assertTrue($openid->hostExists("127.0.0.1"));
		$this->assertFalse($openid->hostExists("not.exists"));

		$this->assertTrue($openid->hostExists("http://me.yahoo.com"));
		$this->assertTrue($openid->hostExists("http://me.yahoo.com/"));
		$this->assertTrue($openid->hostExists("http://me.yahoo.com:80"));
		$this->assertTrue($openid->hostExists("http://me.yahoo.com:80/"));

		$this->assertFalse($openid->hostExists("http://not.exists"));
		$this->assertFalse($openid->hostExists("http://not.exists/"));
		$this->assertFalse($openid->hostExists("http://not.exists:80/"));
	}
}