<?php
/*
 Copyright (C) 2011 Faiumoni

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

require_once('scripts/account.php');
require_once('content/account/openid.php');

class LoginPage extends Page {
	private $error;
	private $openid;

	public function writeHttpHeader() {
		if ($this->handleRedirectIfAlreadyLoggedIn()) {
			return false;
		}

		// force SSL if supported
		if (strpos(STENDHAL_LOGIN_TARGET, 'https://') !== false) {
			if (!isset($_SERVER['HTTPS']) || ($_SERVER['HTTPS'] != "on")) {
				header('Location: '.STENDHAL_LOGIN_TARGET.rewriteURL('/association/login.html'));
				return false;
			}
		}

		$this->openid = new OpenID();
		if (!isset($_GET['openid_mode'])) {
			// redirect to openid provider?
			$this->openid->doOpenidRedirectIfRequired('https://stendhalgame.org');
			if ($this->openid->isAuth && !$this->openid->error) {
				return false;
			}

		} else {

			if ($this->verifyLoginByOpenid()) {
				return false;
			}

		}

		return true;
	}

	public function verifyLoginByOpenid() {
		if (!isset($_GET['openid_mode'])) {
			return false;
		}

		if($_GET['openid_mode'] == 'cancel') {
			$this->openid->error = 'OpenID-Authentication was canceled.';
			return false;
		}

		$username = $this->openid->getStendhalAccountName();
		if (!$username) {
			// TODO: niecer error message
			die('Login failed in Openid transaction');
		}

		$account = Account::readAccountByName($username);
		if (!isset($account) || !($account instanceof Account)) {
			// TODO: niecer error message
			die('Login failed - Account unknown');
		}

		$permissions = Account::readPermissions($account->id);
		if (!$permissions) {
			// TODO: niecer error message
			die('This account is unknown. Please see the member section on how to become a member.');
		}

		// Login
		$_SESSION['account'] = $account;
		$_SESSION['accountPermissions'] = $permissions;
		$_SESSION['csrf'] = createRandomString();
		$this->handleRedirectIfAlreadyLoggedIn();
		return true;
	}

	function handleRedirectIfAlreadyLoggedIn() {
		if (checkLogin()) {
			if (isset($_REQUEST['url'])) {
				header('Location: '.STENDHAL_LOGIN_TARGET.$this->getUrl());
			} else {
				header('Location: '.STENDHAL_LOGIN_TARGET);
			}
			return true;
		}
		return false;
	}

	function getUrl() {
		$url = $_REQUEST['url'];
		if (strpos($url, '/') !== 0) {
			$url = '/'.$url;
		}
		// prevent header splitting
		if (strpos($url, '\r') || strpos($url, '\n')) {
			$url = '/';
		}
		return $url;
	}

}
$page = new LoginPage();
?>
