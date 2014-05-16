<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008-2010 The Arianne Project

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class MyCharactersPage extends Page {

	/**
	 * this method can write additional http headers, for example for cache control.
	 *
	 * @return true, to continue the rendering, false to not render the normal content
	 */
	public function writeHttpHeader() {
		if (strpos(STENDHAL_LOGIN_TARGET, 'https://') !== false) {
			if (!isset($_SERVER['HTTPS']) || ($_SERVER['HTTPS'] != "on")) {
				header('Location: '.STENDHAL_LOGIN_TARGET.rewriteURL('/account/mycharacters.html'));
				return false;
			}
		}
		if (!isset($_SESSION['account'])) {
			header('Location: '.STENDHAL_LOGIN_TARGET.'/index.php?id=content/account/login&url='.rewriteURL('/account/mycharacters.html'));
			return false;
		}

		return true;
	}

	public function writeHtmlHeader() {
		echo '<meta name="robots" content="noindex">'."\n";
		echo '<title>My Characters'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {
		if (defined('STENDHAL_MYCHARACTERS_INFOBOX')) {
			startBox('Announcement');
			echo STENDHAL_MYCHARACTERS_INFOBOX;
			endBox();
		}
		$this->writeCharacterList(7, rewriteURL('/account/create-character.html'));
	}

	function writeCharacterList($charsPerRow, $createURL) {
		global $adminLevel;
		startBox("Character Selector");

		$players = getCharactersForUsername($_SESSION['account']->username);
		if(sizeof($players)==0) {
			echo '<div>Please <a target="_top" href="'.$createURL.'">create a new character</a>.</div>';
		} else {
			echo '<div>Click on a character below to play or <a href="'.$createURL.'">create a new character</a>.</div>';
			echo '<div style="height: '.(ceil(count($players) / $charsPerRow) * 140) .'px">';
			foreach($players as $p) {
				echo '<div class="onlinePlayer characterHeight">';
				echo '  <a class = "onlineLink" href="'.STENDHAL_FOLDER.'/index.php/archestica-starter.jnlp?id=content/account/starter&amp;character='.surlencode($p->name).'">';
				echo '  <img src="'.rewriteURL('/images/outfit/'.surlencode($p->outfit).'.png').'" alt="">';
				echo '  <span class="block">'.htmlspecialchars($p->name).'</span></a>';
				echo '  <span class="block">Level: '.$p->level.'</span>';
				echo '  <span class="block">Age: '.intval($p->age/60).' h</span>';
				echo '  <span class="block"><a class = "characterLink" target="_top" href="'.rewriteURL('/character/'.surlencode($p->name).'.html').'">Details</a></span>';
				if ($adminLevel > 0) {
					echo '  <span class="block"><a class = "characterLink" onclick=\'window.open("/client/stendhal.html#'.surlencode($p->name).'", "_blank", "directories=0,height=750,location=0,left=1,top=1,menubar=0,scrollbars=n,status=n,toolbar=n,width=1000", false);return false;\' href="/client/stendhal.html#'.surlencode($p->name).'">Alpha</a></span>';
				}
				echo '</div>';
			}
			echo '</div>';
		}
		endBox();

		if(sizeof($players) > 0) {
			startBox("Note");
			?>
			<p>On the very first start Archestica will need to download some additional files. Subsequent starts will be a lot faster.</p>
			<?php 
			endBox();

			startBox("Trouble Shooting");
			?>
			<p>You will be asked to open a file called archestica-starter.jnlp with Java Webstart or Java Network Launched Application.
			If the application is unknown, please download and install <a target="_blank" href="http://java.com">Java</a>.</p>

			<p>If you have trouble to join the game, please ask in the <a target="_top" href="/chat/">#arianne</a> chat or create a 
			<a target="_blank" href="https://sourceforge.net/tracker/?group_id=1111&amp;atid=201111">support ticket</a>.</p>
			<?php
			endBox();
		}
	}
}
$page = new MyCharactersPage();
