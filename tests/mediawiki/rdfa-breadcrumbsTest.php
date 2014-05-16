<?php
require_once('mediawiki/rdfa-breadcrumbs.php');

class RDFaBreadcrumbsTest extends PHPUnit_Framework_TestCase {

	public function testGetTextFromLink() {
		$this->assertEquals('Simple Link', getTextFromLink('Simple Link'), 'Simple Link with [[]]');
		$this->assertEquals('Simple Link', getTextFromLink('[[Simple Link]]'), 'Simple Link');
		$this->assertEquals('Link Text', getTextFromLink('[[url|Link Text]]'), 'Link with different text');
		$this->assertEquals('Link Text', getTextFromLink('[[ url | Link Text ]]'), 'Link with different text and spaces');
		$this->assertEquals('Link Text', getTextFromLink('[http://example.com Link Text]'), 'External Link');
	}

	public function testGetURLFromLink() {
		global $wgArticlePath;
		$wgArticlePath = '/wiki/$1';
		$this->assertEquals('/wiki/Simple_Link', getURLFromLink('Simple Link'), 'Simple Link with [[]]');
		$this->assertEquals('/wiki/Simple_Link', getURLFromLink('[[Simple Link]]'), 'Simple Link');
		$this->assertEquals('/wiki/url', getURLFromLink('[[url|Link Text]]'), 'Link with different text');
		$this->assertEquals('/wiki/url', getURLFromLink('[[ url | Link Text ]]'), 'Link with different text and spaces');
		$this->assertEquals('http://example.com', getURLFromLink('[http://example.com Link Text]'), 'External Link');
	}
}
