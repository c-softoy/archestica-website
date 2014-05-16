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

class MyAccountPage extends Page {

	/**
	 * this method can write additional http headers, for example for cache control.
	 *
	 * @return true, to continue the rendering, false to not render the normal content
	 */
	public function writeHttpHeader() {
		if (strpos(STENDHAL_LOGIN_TARGET, 'https://') !== false) {
			if (!isset($_SERVER['HTTPS']) || ($_SERVER['HTTPS'] != "on")) {
				header('Location: '.STENDHAL_LOGIN_TARGET.rewriteURL('/account/myaccount.html'));
				return false;
			}
		}
		if (!isset($_SESSION['account'])) {
			header('Location: '.STENDHAL_LOGIN_TARGET.'/index.php?id=content/account/login&url='.rewriteURL('/account/myaccount.html'));
			return false;
		}

		return true;
	}
	
	public function writeHtmlHeader() {
		echo '<meta name="robots" content="noindex">'."\n";
		echo '<title>My Account'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {
		if (!isset($_SESSION['account'])) {
			startBox("Login Required");
			echo '<p>Please <a href="'.STENDHAL_LOGIN_TARGET.'/index.php?id=content/account/login">login</a>.</p>';
			endBox();
			return;
		}

startBox("My Account"); ?>
	<p>You are logged in as <b><?php echo htmlspecialchars($_SESSION['account']->username);?></b>.</p>
<ul id="dmenu" >
	<?php 
		echo '<li><a href="'.rewriteURL('/account/mycharacters.html').'"><img src="/images/buttons/players_button.png" alt=" "> My Characters</a></li>';
		echo '<li><a href="'.rewriteURL('/account/messages.html').'"><img src="/images/buttons/postman_button.png" alt=" "> Messages</a></li>';
		echo '<li><a href="'.rewriteURL('/account/history.html').'"><img src="/images/buttons/history_button.png" alt=" "> Login History</a></li>';
		echo '<li><a href="'.rewriteURL('/account/change-password.html').'"><img src="/images/buttons/password_button.png" alt=" "> New Password</a></li>';
		echo '<li><a href="'.rewriteURL('/account/merge.html').'"><img src="/images/buttons/merge_button.png" alt=" "> Merge Accounts</a></li>';
		echo '<li><a href="'.rewriteURL('/account/logout.html').'"><img src="/images/buttons/logout_button.png" alt=" "> Logout</a></li>';
	?>
</ul>
<?php endBox();
	}
}
$page = new MyAccountPage();
