<?php
require_once('content/page.php');
require_once('content/game/sourcelog.php');

/**
 * Tests for SourceLogPage
 *
 * @author hendrik
 */
class SourceLogPageTest extends PHPUnit_Framework_TestCase {
	
	public function testFormatLine() {
		$page = new SourceLogPage();
		
		$line = '18:59 < CIA-15> arianne_rpg: kymara * stendhal/src/games/stendhal/server/maps/quests/KillGnomes.java: fix typo';
		$exspected = '<span class="sourcetime">2011-02 18:59</span> <span class="sourceuser">kymara</span>   <span class="sourcemodule">stendhal</span> <span class="sourcefiles">src/games/stendhal/server/maps/quests/KillGnomes.java</span>:<br><span class="sourcecommit"> fix typo';
		$this->assertEquals($exspected, $page->formatLine('2011', '02', $line));

		$line = '16:15 < CIA-15> arianne_rpg: nhnb * r55da9e45f6ca marauroa/.gitignore: added build to .gitignore';
		$exspected = '<span class="sourcetime">2011-02 16:15</span> <span class="sourceuser">nhnb</span>  <a class="sourcerev" href="https://sourceforge.net/p/arianne/marauroa/ci/55da9e45f6ca">55da9e45f6ca</a> <span class="sourcemodule">marauroa</span> <span class="sourcefiles">.gitignore</span>:<br><span class="sourcecommit"> added build to .gitignore';
		$this->assertEquals($exspected, $page->formatLine('2011', '02', $line));

		$line = '23:12 < CIA-15> arianne_rpg: nhnb perception_delta_container * r7f9fdd2cc388 marauroa/src/marauroa/ (2 files in 2 dirs): converted ChangeContainer into an abstract singleton class and wrote a logging implementation';
		$exspected = '<span class="sourcetime">2011-02 23:12</span> <span class="sourceuser">nhnb</span> <span class="sourcedevbranch">&nbsp;perception_delta_container&nbsp;</span> <a class="sourcerev" href="https://sourceforge.net/p/arianne/marauroa/ci/7f9fdd2cc388">7f9fdd2cc388</a> <span class="sourcemodule">marauroa</span> <span class="sourcefiles">src/marauroa/ (2 files in 2 dirs)</span>:<br><span class="sourcecommit"> converted ChangeContainer into an abstract singleton class and wrote a logging implementation';
		$this->assertEquals($exspected, $page->formatLine('2011', '02', $line));

		$line = '07:36 arianne_rpg: nhnb * rf744d1ec2a3c exp/stendhal-client/stendhal-client-html/ (.gitignore, build.xml): build script which zips the html client, ignoring server files for now';
		$exspected = '<span class="sourcetime">2013-06 07:36</span> <span class="sourceuser">nhnb</span>  <a class="sourcerev" href="https://sourceforge.net/p/arianne/exp/stendhal-client/ci/f744d1ec2a3c">f744d1ec2a3c</a> <span class="sourcemodule">exp/stendhal-client</span> <span class="sourcefiles">stendhal-client-html/ (.gitignore, build.xml)</span>:<br><span class="sourcecommit"> build script which zips the html client, ignoring server files for now';
		$this->assertEquals($exspected, $page->formatLine('2013', '06', $line));
	}
}
