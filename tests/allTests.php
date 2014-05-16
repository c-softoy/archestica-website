<?php
require_once 'PHPUnit/Framework.php';

require_once 'tests/content/game/sourcelogTest.php';
require_once 'tests/lib/openid/lightopenidTest.php';
require_once 'tests/mediawiki/rdfa-breadcrumbsTest.php';
require_once 'tests/scripts/pharauroa/common/net/deserializerTest.php';
require_once 'tests/scripts/accountTest.php';
require_once 'tests/scripts/netstatsTest.php';


class AllTests {
	public static function suite() {
		$suite = new PHPUnit_Framework_TestSuite('PHPUnit');

		$suite->addTestSuite('SourceLogPageTest');
		$suite->addTestSuite('LightOpenidTest');
		$suite->addTestSuite('PharauroaDeserializerTest');
		$suite->addTestSuite('RDFaBreadcrumbsTest');
		$suite->addTestSuite('AccountTest');
		$suite->addTestSuite('AccountLinkTest');
		$suite->addTestSuite('NetstatsTest');
		
		return $suite;
	}
}
?>