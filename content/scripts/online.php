<?php

class OnlinePage extends Page {
	private $isOnline = true;

	public function __construct() {
		$this->isOnline = ServerStatistics::checkServerIsOnline();
	}

	public function writeHtmlHeader() {
		echo '<title>Online Players'.STENDHAL_TITLE.'</title>';
		if (!$this->isOnline) {
			echo '<meta name="robots" content="noindex">'."\n";
		}
	}

	/**
	 * writes the page content.
	 */
	function writeContent() {
		if ($this->isOnline) {
			$this->writeOnlinePlayers();
		} else {
			$this->writeOfflineHint();
		}
		$this->writeRecent();
	}

	/**
	 * writes an informative text about the server being offline.
	 */
	function writeOfflineHint() {
		startBox("Server is offline");
		echo '<p>We are sorry! The server is offline right now.</p>';
		echo '<p>This may be the desired behaviour, in case of an update, or it may be the result of some kind of problem.</p>';
		echo '<p>Please join our IRC channel <a href="https://webchat.freenode.net/?channels='.substr(MAIN_CHANNEL, 1).'">'.MAIN_CHANNEL.'</a> ';
		echo 'for updates on the situation.</p>';
		endBox();
	}

	/**
	 * writes the list of online players.
	 */
	function writeOnlinePlayers() {
		$players = getOnlinePlayers();
		startBox('Online Players');
		
		if(sizeof($players)==0) {
			echo 'There are no players logged in at the moment.';
		}
		echo '<div style="height: '.(ceil(count($players) / 7) * 80 + 10) .'px">';
		foreach($players as $p) {
			echo '<div class="onlinePlayer onlinePlayerHeight">';
			echo '  <a class = "onlineLink" href="'.rewriteURL('/character/'.urlencode($p->name).'.html').'">';
			echo '  <img src="'.rewriteURL('/images/outfit/'.surlencode($p->outfit).'.png').'" alt="" width="48" height="64">';
			echo '  <span class="block onlinename">'.htmlspecialchars($p->name).'</span></a>';
			echo '</div>';
		}
		echo '</div>';
		endBox();
	}

	function writeRecent() {
		$stats = ServerStatistics::readOnlineStats();
		startBox('Recently online');
		echo '<p>This table shows the number of different accounts and characters which have been online recently.</p>';
		echo '<table class="prettytable">';
		echo '<tr><th>&nbsp;</th><th>Accounts</th><th>Characters</th></tr>';
		echo '<tr><td>Week</td><td>'.htmlspecialchars($stats['accounts_7']).'</td><td>'.htmlspecialchars($stats['characters_7']).'</td></tr>';
		echo '<tr><td>Month</td><td>'.htmlspecialchars($stats['accounts_30']).'</td><td>'.htmlspecialchars($stats['characters_30']).'</td></tr>';
		echo '</table>';
		endBox();
	}
}

$page = new OnlinePage();