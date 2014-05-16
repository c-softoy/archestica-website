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

require_once('lib/openid/lightopenid.php');

class OpenID {
	public $error;
	public $isAuth = false;

	public function doOpenidRedirectIfRequired($requestedIdentifier) {
		if (!isset($_GET['openid_mode'])) {
			if (isset($requestedIdentifier)) {
				$this->isAuth = true;
				$openid = new LightOpenID($_SERVER['HTTP_HOST']);
				// $openid->oauth[] = 'https://www.googleapis.com/auth/plus.me';
				$openid->identity = $requestedIdentifier;
				$openid->required = array('contact/email', 'namePerson/friendly');
				$openid->realm     = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
				$openid->returnUrl = Account::createReturnUrl();
				try {
					header('Location: ' . $openid->authUrl());
				} catch (ErrorException $e) {
					$this->error = $e->getMessage();
				}
			}
		}
	}

	/**
	 * creates an AccountLink object based on the openid identification
	 * 
	 * @return AccountLink or <code>FALSE</code> if  the validation failed
	 */
	public function createAccountLink() {
		$openid = new LightOpenID($_SERVER['HTTP_HOST']);
		$openid->realm     = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
		$openid->returnUrl = Account::createReturnUrl();
		try {
			if (!$openid->validate()) {
				$this->error = 'Open ID validation failed.';
				return false;
			}
		} catch (Exception $e) {
			$this->error = $e->getMessage();
		}
		$attributes = $openid->getAttributes();
		$accountLink = new AccountLink(null, null, 'openid', $openid->identity, 
			$attributes['namePerson/friendly'], $attributes['contact/email'], null);
		// echo 'token:' . $openid->getOAuthRequestToken();
		return $accountLink;
	}

	public function getStendhalAccountName() {
		$openid = new LightOpenID();
		$openid->realm     = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
		$openid->returnUrl = Account::createReturnUrl();
		if (!$openid->validate()) {
			$this->error = 'Open ID validation failed.';
			return false;
		}
		$identifier = $openid->identity;
		if (strpos($identifier, 'https://archestica.net/a/') !== 0) {
			$this->error = 'Only Stendhal Accounts accepted';
			return false;
		}
		return substr($identifier, 27);
	}

	/**
	 * handles a succesful openid authentication
	 * 
	 * @param AccountLink $accountLink the account link created for the login
	 */
	public function merge($accountLink) {
		$oldAccount = $_SESSION['account'];
		$newAccount = Account::readAccountByLink('openid', $accountLink->username, null);

		if (!$newAccount || is_string($newAccount)) {
			$accountLink->playerId = $oldAccount->id;
			$accountLink->insert();
		} else {
			if ($oldAccount->username != $newAccount->username) {
				mergeAccount($newAccount->username, $oldAccount->username);
			}
		}
	}

	public function succesfulOpenidAuthWhileNotLoggedIn($accountLink) {
		unset($_SESSION['account']);
		$account = Account::tryLogin('openid', $accountLink->username, null);

		if (!$account || is_string($account)) {
			$account = $accountLink->createAccount();
		}
		$_SESSION['account'] = $account;
		$_SESSION['csrf'] = createRandomString();
		$_SESSION['marauroa_authenticated_username'] = $account->username;
		fixSessionPermission();
	}
}

