<?php

class Facebook {
	public $isAuth = false;
	
	public function doRedirect() {
		header('Location: https://www.facebook.com/dialog/oauth?client_id=' . FACEBOOK_APP_ID
			.'&redirect_uri=' . urlencode(Account::createReturnUrl()));
	}
	
	public function doRedirectWithCSRFToken($token) {
		header('Location: https://www.facebook.com/dialog/oauth?client_id=' . FACEBOOK_APP_ID
		.'&redirect_uri=' . urlencode(Account::createReturnUrl())
		.'&state=' . urlencode(token));
	}

	/**
	 * creates an AccountLink object based on the facebook identification
	 * 
	 * @return AccountLink or <code>FALSE</code> if  the validation failed
	 */
	public function createAccountLink() {
		$tokenUrl = "https://graph.facebook.com/oauth/access_token?"
			. "client_id=" . FACEBOOK_APP_ID 
			. "&redirect_uri=" . urlencode(Account::createReturnUrl())
			. "&client_secret=" . FACEBOOK_APP_SECRET 
			. "&code=" . $_REQUEST['code'];
		$response = file_get_contents($tokenUrl);
		$params = null;
		parse_str($response, $params);
		if (!isset($params) || !isset($params['access_token'])) {
			return null;
		}
		return $this->createAccountLinkFromAccessToken($params['access_token']);
	}

	/**
	 * creates an AccountLink based on the information in a
	 * singed request.
	 */
	public function createAccountLinkForSignedRequest() {
		if (!isset($_REQUEST['signed_request'])) {
			return null;
		}
	
		$params = $this->parseSignedRequest($_REQUEST['signed_request']);
		if (!isset($params) || !isset($params['oauth_token'])) {
			return null;
		}
	
		return $this->createAccountLinkFromAccessToken($params['oauth_token']);
	}


	private function createAccountLinkFromAccessToken($token) {
		$url = "https://graph.facebook.com/me?fields=id,username,name&access_token=".urlencode($token);
		$data = file_get_contents($url);
		$user = json_decode($data, true);
		if (!isset($user) || !isset($user['id'])) {
			return false;
		}
		if (isset($user['username'])) {
			$nickname = strtolower($user['username']);
		} else {
			$nickname = str_replace(" ", "", strtolower($user['name']));
		}
		
		// ["id"]=> "100001372455913" ["name"]=>"Petra Portal" ["first_name"]=>"Petra" ["last_name"]=>"Portal"
		
		$accountLink = new AccountLink(null, null, 'facebook', $user['id'], $nickname, null, null);
		return $accountLink;
	}
	
	/**
	 * handles an requested account merge
 	 *
	 * @param AccountLink $accountLink the account link created for the login
	 */
	public function merge($accountLink) {
		$oldAccount = $_SESSION['account'];
		$newAccount = Account::readAccountByLink('facebook', $accountLink->username, null);
	
		if (!$newAccount || is_string($newAccount)) {
			$accountLink->playerId = $oldAccount->id;
			$accountLink->insert();
		} else {
			if ($oldAccount->username != $newAccount->username) {
				mergeAccount($newAccount->username, $oldAccount->username);
			}
		}
	}


	public function getCanvasAuthUrl() {
		return 'https://www.facebook.com/dialog/oauth?client_id='
			.FACEBOOK_APP_ID.'&redirect_uri=https://stendhalgame.org/%3Fid%3Dcontent/account/gadget%26social%3Dfb';
	}

// BEGIN FACEBOOK PHP SDK http://www.apache.org/licenses/LICENSE-2.0

	/**
	* Parses a signed_request and validates the signature.
	*
	* @param string $signed_request A signed token
	* @return array The payload inside it or null if the sig is wrong
	*/
	private function parseSignedRequest($signed_request) {
		list($encoded_sig, $payload) = explode('.', $signed_request, 2);

		// decode the data
		$sig = self::base64UrlDecode($encoded_sig);
		$data = json_decode(self::base64UrlDecode($payload), true);

		if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
			return null;
		}

		// check sig
		$expected_sig = hash_hmac('sha256', $payload, $this->getAppSecret(), $raw = true);
		if ($sig !== $expected_sig) {
			return null;
		}
		return $data;
	}

	/**
	* Base64 encoding that doesn't need to be urlencode()ed.
	* Exactly the same as base64_encode except it uses
	* - instead of +
	* _ instead of /
	*
	* @param string $input base64UrlEncoded string
	* @return string
	*/
	private static function base64UrlDecode($input) {
		return base64_decode(strtr($input, '-_', '+/'));
	}


// END FACEBOOK SDK

}
