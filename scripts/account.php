<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008  Miguel Angel Blanch Lardin
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



/**
 * checkLogin - Checks if the user has already previously
 * logged in, and a session with the user has already been
 * established. Returns true if the user has logged in.
 */
function checkLogin(){
	return isset($_SESSION) && isset($_SESSION['account']);
}


function getAdminLevel() {
	if(!checkLogin()) {
		return -1;
	}

	$sql = "select max(admin) As adminlevel FROM character_stats, characters, account "
		. " WHERE character_stats.name=characters.charname AND characters.player_id=account.id "
		. " AND account.username='".mysql_real_escape_string($_SESSION['account']->username)."'";
	$result = mysql_query($sql, getGameDB());
	while($row=mysql_fetch_assoc($result)) {
		return (int)$row['adminlevel'];
	}
}


// Returns user id for username or false
function getUserID($username) {
	$q = "SELECT id FROM account WHERE username = '".
	mysql_real_escape_string($username)."'";

	$result = mysql_query($q, getGameDB());

	if (!$result || mysql_num_rows($result) !== 1) {
		/* Couldn't find the userid or DB failure */
		return false;
	}

	$row = mysql_fetch_assoc($result);
	return $row['id'];
}




/**
 * verifies that thae specified characer belongs to the specified account
 *
 * @param string $username name of account
 * @param string $charname name of character
 * @return boolean
 */
function verifyCharacterBelongsToUsername($username, $charname) {
	$sql = "SELECT player_id "
	. "FROM account, characters "
	. "WHERE account.username='".mysql_real_escape_string($username)
	. "' AND account.id=characters.player_id "
	. "AND characters.charname='".mysql_real_escape_string($charname)."'";
	$result = mysql_query($sql, getGameDB());
	$res = mysql_numrows($result) > 0;
	mysql_free_result($result);
	return $res;
}

/**
 * checks if a character exist
 *
 * @param string $username name of account
 * @return boolean
 */
function doesCharacterExist($charname) {
	$sql = "SELECT player_id "
	. "FROM characters "
	. "WHERE characters.charname='".mysql_real_escape_string($charname)."'";
	$result = mysql_query($sql, getGameDB());
	$res = mysql_numrows($result) > 0;
	mysql_free_result($result);
	return $res;
}

function storeSeed($username, $ip, $seed, $authenticated) {
	$query = 'INSERT INTO loginseed(player_id, address, seed, complete, used)'
	." SELECT id, '".mysql_real_escape_string($ip)."', '".mysql_real_escape_string($seed)."', '"
	.mysql_real_escape_string($authenticated)."', 0 FROM account WHERE username='".mysql_real_escape_string($username)."'";

	mysql_query($query, getGameDB());
}


/**
 * merges two accounts
 *
 * @param string $oldUsername the account to merge in
 * @param string $newUsername the account to keep
 */
function mergeAccount($oldUsername, $newUsername) {
	$oldAccountId = getUserID($oldUsername);
	$newAccountId = getUserID($newUsername);
	mysql_query("UPDATE account SET status='merged' WHERE id='".mysql_real_escape_string($oldAccountId)."'", getGameDB());
	$result = mysql_query("SELECT charname FROM characters WHERE player_id='".mysql_real_escape_string($oldAccountId)."'", getGameDB());
	$chars = array();
	while($row = mysql_fetch_assoc($result)) {
		$chars[] = $row['charname'];
		PlayerLoginEntry::logAccountMerge($row['charname'], $oldAccountId, $oldUsername, $newUsername);
	}
	sendUdpMessage('stendhal', $oldUsername . ' was merged into ' . $newUsername. ' (' . implode(', ', $chars) . ')');
	mysql_free_result($result);
	mysql_query("UPDATE characters SET player_id='".mysql_real_escape_string($newAccountId)."' WHERE player_id='".mysql_real_escape_string($oldAccountId)."'", getGameDB());
	mysql_query("UPDATE accountLink SET player_id='".mysql_real_escape_string($newAccountId)."' WHERE player_id='".mysql_real_escape_string($oldAccountId)."'", getGameDB());
}


/**
 * adds an account link (like an openid)
 *
 * @param string $username the name of the logged in user to add the link to
 * @param $identifier the openid identifier
 * @param $type "openid" or "connect"
 * @param $email email address provided by the identity provider
 * @param $nickname nick name
 */
function addAccountLink($username, $type, $identifier, $email, $nickname) {
	$accountId = getUserID($username);
	$sql = "INSERT INTO accountLink (player_id, type, username, nickname, email) VALUES ('"
	. mysql_real_escape_string($accountId)."', '"
	. mysql_real_escape_string($type)."', '"
	. mysql_real_escape_string($identifier)."', '"
	. mysql_real_escape_string($email)."', '"
	. mysql_real_escape_string($nickname)."');";
	mysql_query($sql, getGameDB());
}


/**
 * A class that represent a player history entry
 */
class PlayerLoginEntry {
	/* date and time of event */
	public $timedate;
	/* name of ip-address */
	public $address;
	/* name of service */
	public $service;
	/* type of event */
	public $eventType;
	/* success */
	public $success;

	function __construct($timedate, $address, $service, $eventType, $success) {
		$this->timedate = $timedate;
		$this->address = $address;
		$this->service = $service;
		$this->eventType = $eventType;
		$this->success = $success;
	}


	/**
	 * gets a list of recent login events for that player
	 */
	public static function getLoginHistory($playerId) {
		$sql = "SELECT address, timedate, service, event, result FROM "
		. "(SELECT address, timedate, service, 'login' As event, result FROM loginEvent "
		. "WHERE player_id=".mysql_real_escape_string($playerId)." AND timedate > DATE_SUB(CURDATE(),INTERVAL 7 DAY) "
		. "UNION SELECT address, timedate, service, 'password change' As event, result FROM passwordChange "
		. "WHERE player_id=".mysql_real_escape_string($playerId)." AND timedate > DATE_SUB(CURDATE(),INTERVAL 7 DAY)) As data "
		. "ORDER BY timedate DESC LIMIT 1000;";

		$result = mysql_query($sql, getGameDB());
		$list=array();

		while($row = mysql_fetch_assoc($result)) {
			$list[] = new PlayerLoginEntry($row['timedate'],
			$row['address'], $row['service'], $row['event'],$row['result']);
		}

		mysql_free_result($result);
		return $list;
	}

	/**
	 * log password changes for user from ip
	 * returns boolean successfully logged
	 */
	public static function logUserPasswordChange($user, $ip, $oldpass, $result) {
		$userid = getUserID($user);

		if ( $userid === false) {
			return false;
		}

		$q = "INSERT INTO passwordChange (player_id, address, oldpassword, service, result)".
			" values (".$userid.", '".mysql_real_escape_string(trim($ip))."', '".mysql_real_escape_string($oldpass)."', 'website', ".intval($result).")";
		$result = mysql_query($q, getGameDB());
		return $result !== false;
	}

	/**
	 * log logins for user from ip
	 * returns boolean successfully logged
	 */
	public static function logUserLogin($user, $ip, $accountLink, $success) {
		$userid = getUserID($user);

		if ( $userid === false ) {
			return false;
		}

		$q = "INSERT INTO loginEvent (player_id, address, result, service";
		if ($accountLink) {
			$q = $q .', account_link_id';
		}
		$q = $q . ") values (".$userid.", '".mysql_real_escape_string(trim($ip))." ',".intval($success).", 'website'";
		if ($accountLink) {
			$q = $q . ", '".mysql_real_escape_string($accountLink)."'";
		}
		$q = $q . ")";

		$result = mysql_query($q, getGameDB());
		return $result !== false;
	}

	public static function logAccountMerge($character, $oldAccountId, $oldUsername, $newUsername) {
		$q = "INSERT INTO gameEvents (source, event, param1, param2) values ".
			"('".mysql_real_escape_string($character)."', 'accountmerge', '".mysql_real_escape_string($oldAccountId)."', '"
			.mysql_real_escape_string($oldUsername). "-->". mysql_real_escape_string($newUsername) ."')";
			$result = mysql_query($q, getGameDB());
			return $result !== false;
	}
}


/**
 * A class that represents a StoredMessage
 */
class StoredMessage {
	/* id */
	public $id;
	/* source of message (who sent it) */
	public $source;
	/* target of message (who it was sent to) */
	public $target;
	/* date and time of event */
	public $timedate;
	/* content of message */
	public $message;
	/* type of message: S (Support); P (player); N (NPC)  */
	public $messageType;
	/* whether it was delivered */
	public $delivered;

	function __construct($id, $source, $target, $timedate, $message, $messageType, $delivered) {
		$this->id = $id;
		$this->source = $source;
		$this->target = $target;
		$this->timedate = $timedate;
		$this->message = $message;
		$this->messageType = $messageType;
		$this->delivered = $delivered;
	}

	/**
	 * gets a list of recent messages for that player
	 */
	public static function getCountUndeliveredMessages($playerId, $where) {
		$sql = "SELECT count(*) as count "
		. " FROM postman , characters "
		. " WHERE " . $where
		. " AND characters.player_id=".mysql_real_escape_string($playerId)
		. " AND delivered = 0;";
		$result = mysql_query($sql, getGameDB());

		while($row = mysql_fetch_assoc($result)) {
			$count = $row['count'];
		}

		mysql_free_result($result);
		return $count;
	}

	/**
	 * gets a list of recent messages for that player
	 */
	public static function getStoredMessages($playerId, $where) {
		$sql = "SELECT postman.id, postman.source, postman.target, postman.timedate, postman.message, postman.messageType, postman.delivered "
		. " FROM postman , characters "
		. " WHERE " . $where
		. " AND  characters.player_id=".mysql_real_escape_string($playerId)
		. " AND postman.timedate > DATE_SUB(CURDATE(),INTERVAL 3 MONTH) "
		. " ORDER BY postman.timedate DESC LIMIT 100;";
		// echo $sql;
		$result = mysql_query($sql, getGameDB());
		$list=array();

		while($row = mysql_fetch_assoc($result)) {
			$list[] = new StoredMessage($row['id'], $row['source'], $row['target'], $row['timedate'],
			$row['message'], $row['messageType'], $row['delivered']);
		}

		mysql_free_result($result);
		return $list;
	}

	/**
	 * deleted messages sent by the player
	 *
	 * @param $playerId id of player
	 * @param $ids id of messages to delete (need to be sql escaped)
	 */
	public static function deleteSentMessages($playerId, $ids) {
		$sql = "DELETE FROM postman USING postman, characters WHERE characters.player_id='".mysql_real_escape_string($playerId)
			."' AND characters.charname=postman.source AND (postman.deleted='R') AND postman.id IN (".$ids.")";
		mysql_query($sql, getGameDB()) || die(mysql_error(getGameDB()));

		$sql = "UPDATE postman, characters SET postman.deleted='S' WHERE characters.player_id='".mysql_real_escape_string($playerId)
			."' AND characters.charname=postman.source AND postman.id IN (".$ids.")";
		mysql_query($sql, getGameDB()) || die(mysql_error(getGameDB()));
	}


	/**
	 * deleted messages received by the player
	 *
	 * @param $playerId id of player
	 * @param $ids id of messages to delete (need to be sql escaped)
	 */
		public static function deleteReceivedMessages($playerId, $ids) {
		$sql = "DELETE FROM postman USING postman, characters WHERE characters.player_id='".mysql_real_escape_string($playerId)
			."' AND characters.charname=postman.target AND (postman.deleted='S' OR postman.messagetype='N') AND postman.id IN (".$ids.")";
		mysql_query($sql, getGameDB()) || die(mysql_error(getGameDB()));

		$sql = "UPDATE postman, characters SET postman.deleted='R' WHERE characters.player_id='".mysql_real_escape_string($playerId)
			."' AND characters.charname=postman.target AND postman.id IN (".$ids.")";
		mysql_query($sql, getGameDB()) || die(mysql_error(getGameDB()));
	}
}



/**
 * Account
 *
 * @author hendrik
 */
class Account {
	public $id;
	public $username;
	public $password;
	public $email;
	public $emailTrusted;
	public $timedate;
	public $status;
	public $banMessage;
	public $banExpire;
	public $links;
	public $usedAccountLink;

	public function __construct($id, $username, $password, $email, $emailTrusted, $timedate, $status) {
		$this->id = $id;
		$this->username = $username;
		$this->password = $password;
		$this->email = $email;
		$this->emailTrusted = $emailTrusted;
		$this->timedate = $timedate;
		$this->status = $status;
	}

	public static function tryLogin($type, $username, $password) {
		if (!Account::checkIpBan()) {
			return "Your IP Address has been banned.";
		}
		
		// ask database
		if ($type == 'password' || $type == 'passwordchange') {
			$banMessage = Account::checkBlocked($username, $_SERVER['REMOTE_ADDR']);
			if ($banMessage != null) {
				$success = 4;
			} else {
				$account = Account::readAccountByName($username);
				if (isset($account)) {
					$success = $account->checkPassword($password);
					if ($success == 0) {
						$banMessage = "Invalid username or password";
					}
				} else {
					$success = 0;
					$banMessage = "Invalid username or password";
				}
			}
		} else {
			$account = Account::readAccountByLink($type, $username, $password);
			if (isset($account)) {
				$success = 1;
			}
		}

		if ($success == 1) {
			$account->readAccountBan();
			$banMessage = $account->getAccountStatusMessage();
			$success = $account->getAccountStatusCode();

			$username = $account->username;
			$passhash = $account->password;
			$usedAccountLink = $account->usedAccountLink;
		}
		
		// Log loginEvent or passwordChange
		if ($type != 'passwordchange') {
			PlayerLoginEntry::logUserLogin($username, $_SERVER['REMOTE_ADDR'], $usedAccountLink, $success);
		} else {
			PlayerLoginEntry::logUserPasswordChange($username, $_SERVER['REMOTE_ADDR'], $passhash, $success);
		}

		
		// if the account is banned
		if (isset($banMessage)) {
			return $banMessage;
		}
		return $account;
	}

	private static function checkIpBan() {
		// TODO: implement me
		return true;
	}
	
	/**
	 * reads an account object based on the account id.
	 *
	 * @param string $username username
	 */
	public static function readAccountById($id) {
		$sql = "SELECT account.id, username, password, email.email, account.timedate, account.status "
		. " FROM account LEFT JOIN email ON email.player_id=account.id "
		. " WHERE id=".((int) $id);
		$result = mysql_query($sql, getGameDB());
		$list=array();
	
		$row = mysql_fetch_assoc($result);
		if ($row) {
			$res = new Account($row['id'], $row['username'], $row['password'], $row['email'], false, $row['timedate'], $row['status']);
		}
	
		mysql_free_result($result);
		return $res;
	}

	/**
	 * reads an account object based on the username.
	 *
	 * @param string $username username
	 */
	public static function readAccountByName($username) {
		$sql = "SELECT account.id, username, password, email.email, account.timedate, account.status "
			. " FROM account LEFT JOIN email ON email.player_id=account.id "
			. " WHERE username='".mysql_real_escape_string($username)."'";
		$result = mysql_query($sql, getGameDB());
		$list=array();

		$row = mysql_fetch_assoc($result);
		if ($row) {
			$res = new Account($row['id'], $row['username'], $row['password'], $row['email'], false, $row['timedate'], $row['status']);
		}

		mysql_free_result($result);
		return $res;
	}

	/**
	 * reads an account object from the database based on an account link.
	 *
	 * @param string $type "openid", "facebook"
	 * @param string $username identifier (e. g. openid url)
	 * @param string $password an optional secret
	 */
	public static function readAccountByLink($type, $username, $password) {
		$sql = "SELECT account.id As id, account.username As username, "
			. " account.password As password, email.email As email, "
			. " account.timedate As timedate, account.status As status, "
			. " accountLink.id As usedAccountLink"
			. " FROM accountLink INNER JOIN account ON (account.id = accountLink.player_id) "  
			. " LEFT JOIN email ON (account.id = email.player_id) "
			. " WHERE accountLink.type='".mysql_real_escape_string($type)."'"
			. " AND accountLink.username='".mysql_real_escape_string($username)."'";
		if (isset($password)) {
			$sql = $sql . " AND accountLink.secret='".mysql_real_escape_string($password)."'";
		} else {
			$sql = $sql . " AND accountLink.secret IS NULL";
		}

		$result = mysql_query($sql, getGameDB());
		$list = array();

		$res = null;
		$row = mysql_fetch_assoc($result);
		if ($row) {
			$res = new Account($row['id'], $row['username'], $row['password'], $row['email'], false, $row['timedate'], $row['status']);
			$res->usedAccountLink = $row['usedAccountLink'];
		}

		mysql_free_result($result);
		return $res;
	}


	/**
	 * checks that the password is correct
	 *
	 * @param string password
	 */
	private function checkPassword($password) {

		if (strpos($this->password, '$') === 0) {
			$cryptpass = crypt(STENDHAL_PASSWORD_PEPPER . strtoupper(md5($password)), $this->password);
			if ($cryptpass == $this->password) {
				return 1;
			}

			// pre-Marauroa 2.0 passwords
			$cryptpass = crypt(STENDHAL_PASSWORD_PEPPER . strtoupper(md5(md5($password, true))), $this->password);
			if ($cryptpass == $this->$password) {
				return 1;
			}
			return 0;
		}

		$md5pass = strtoupper(md5($password));
		if ($md5pass == $this->password) {
			return 1;
		}
		
		// We need to check the pre-Marauroa 2.0 passwords
		$md5pass = strtoupper(md5(md5($password, true)));
		if ($md5pass == $this->password) {
			return 1;
		}
		return 0;
	}
	
	public static function checkBlocked($username, $ip) {
		$sql = "SELECT count(*) as amount FROM loginEvent"
			. " WHERE address='" . mysql_real_escape_string($ip) . "'"
			. " AND result != 1 and timedate > date_sub(CURRENT_TIMESTAMP(), INTERVAL 10 MINUTE)";
		
		$count = queryFirstCell($sql, getGameDB());
		if ($count > 3) {
			return "There have been too many failed login attempts from your network. Please wait a couple of minutes or contact support.";
		}

		$sql = "SELECT count(*) as amount FROM loginEvent, account"
			. " WHERE loginEvent.player_id=account.id"
			. " AND username='" . mysql_real_escape_string($username) . "'"
			. " AND loginEvent.result != 1 and loginEvent.timedate > date_sub(CURRENT_TIMESTAMP(), INTERVAL 10 MINUTE)";
		
		$count = queryFirstCell($sql, getGameDB());
		if ($count > 10) {
			return "There have been too many failed login attempts for your account. Please wait a couple of minutes or contact support.";
		}
						
		return null;
	} 

	private function readAccountBan() {
		$sql = "SELECT reason, expire FROM accountban "
			." WHERE accountban.player_id='".mysql_real_escape_string($this->id)."'"
			." AND (accountban.expire > CURRENT_TIMESTAMP OR accountban.expire IS NULL) ORDER BY ifnull(expire,'9999-12-31') desc limit 1 ";
		$result = mysql_query($sql, getGameDB());
		$list=array();

		$row = mysql_fetch_assoc($result);
		if ($row) {
			$this->banMessage = $row['reason'];
			$this->banExpire = $row['expire'];
		}

		mysql_free_result($result);
	}

	/**
	 * get a message telling the player why the account is not active
	 * 
	 * @return message or <code>null</code> if the account is active
	 */
	public function getAccountStatusMessage() {
		if (isset($this->banMessage)) {
			if (isset($this->banExpire)) {
				$res = "Your account is temporarily banned until " . $this->banExpire . " server time.\n";
			} else {
				$res = "Your account is banned.\n";
			}
			$res = $res . "The reason given was: " . $this->banMessage;
		} else if ($this->status == "banned") {
			$res = "Your account has been banned. Please contact support.";
		} else if ($this->status == "inactive") {
			$res = "Your account has been flagged as inactive. Please contact support.";
		} else if ($this->status == "merged") {
			$res = "Your account has been merged into another account. Please login with that account or contact support.";
		}
		if (isset($res)) {
			return $res;
		}
		return null;
	}

	/**
	 * get a status telling the player why the account is not active
	 *
	 * @return message or <code>null</code> if the account is active
	 */
	public function getAccountStatusCode() {
		if (isset($this->banMessage)) {
			$res = 2;
		} else if ($this->status == "banned") {
			$res = 2;
		} else if ($this->status == "inactive") {
			$res = 3;
		} else if ($this->status == "merged") {
			$res = 5;
		}
		return 1;
	}
	
	/**
	 * tries to convert a proposed username into a valid one
	 *
	 * @param string $username proposed username
	 * @return valid username or <code>null</code>.
	 */
	public static function convertToValidUsername($username) {
		$temp = preg_replace('/[^a-z]/', '', strtolower($username));
		if (strlen($temp) > 0) {
			$org = $temp;
			while (strlen($temp) < 6) {
				$temp = $temp . $org;
			}
			return substr($temp, 0, 20);
		} else {
			return null;
		}
	}

	/**
	 * Creates a sha512crypt hash of the password hash, unless it is disabled in configuration
	 * 
	 * @param string $passwordHash password hash
	 * @return sha512crypt hash
	 */
	public static function sha512crypt($passwordHash) {
		if (STENDHAL_PASSWORD_HASH == 'md5') {
			return $passwordHash;
		}
		$alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
		$salt = '$6$';
		for($i = 0; $i < 16; $i++) {
			$salt .= $alphabet[rand(0, 63)];
		}
		return crypt(STENDHAL_PASSWORD_PEPPER . $passwordHash, $salt);
	}

	/**
	 * inserst a record in the account table.
	 */
	public function insert() {
		$sql = "INSERT INTO account(username, status";
		$sql2 = ") VALUES ('".mysql_real_escape_string($this->username)
			."', '".mysql_real_escape_string($this->status)."'";
		if ($this->password) {
			$sql .= ", password";
			$sql2 .= ", '".mysql_real_escape_string(Account::sha512crypt($this->password))."'";
		}
		$sql = $sql.$sql2.');';
		if (!mysql_query($sql, getGameDB())) {
			echo htmlspecialchars($sql.': '.mysql_error(getGameDB()));
		}
		$this->id = mysql_insert_id(getGameDB());
		$this->insertEMail($this->email, $this->emailTrusted);
	}

	/**
	 * gets the email history
	 */
	public static function getEmailHistory($playerId) {
		$sql = "SELECT email, token, address, timedate, confirmed "
		. " FROM email WHERE player_id=" . intval($playerId)
		. " ORDER BY id DESC";
	
		$result = mysql_query($sql, getGameDB());
		$list = array();
	
		while($row = mysql_fetch_assoc($result)) {
			$list[] = $row;
		}
	
		mysql_free_result($result);
		return $list;
	}
	
	/**
	 * changes the email-address
	 *
	 * @param email new email-address
	 */
	public function insertEMail($email, $trusted) {
		if ($trusted) {
			$sql = "insert into email(player_id, email, address, confirmed) values ('".mysql_real_escape_string($this->id)
			."', '".mysql_real_escape_string($email)."', '".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."', NOW())";
			mysql_query($sql, getGameDB());
		} else {
			$data = Account::getEmailHistory($this->id);
			if (count($data) > 0) {
				$row = $data[0];
				if ($row['email'] == $email) {
					if (isset($row['confirmed']) && ($row['confirmed'] != '')&& (strpos($row['confirmed'], '0000') === false)) {
						return;
					} else {
						$token = $row['token'];
					}
				}
			}

			if (!isset($token)) {
				$token = createRandomString();
				$sql = "insert into email(player_id, email, token) values ('".mysql_real_escape_string($this->id)
					."', '".mysql_real_escape_string($email)."', '".mysql_real_escape_string($token)."')";
				mysql_query($sql, getGameDB());
			}
			require_once('scripts/cmd/mail.php');
			sendRegistrationMail($this->id, $this->username, $token, $email);
		}
	}

	/**
	 * checks if a name is available for account/character creation
	 *
	 * @param $name name to check
	 * @param $ignoreAccount ignore this account on the character check (to allow someone to create a character with his own account name)
	 */
	public static function isNameAvailable($name, $ignoreAccount) {
		$sql = '';
		if (!$ignoreAccount || trim($name) != trim($ignoreAccount)) {
			$sql = "SELECT username FROM account WHERE username = '".mysql_real_escape_string($name)."' UNION ";
		}
		$sql .= "SELECT charname FROM characters WHERE charname = '".mysql_real_escape_string($name)."';";
		$result = mysql_query($sql, getGameDB());
		$res = mysql_numrows($result) == 0;
		mysql_free_result($result);
		return $res;
	}

	/**
	 * reads the permissions for an account
	 *
	 * @param string $accountId accountId
	 */
	public static function readPermissions($accountId) {
		$sql = "SELECT * FROM permission WHERE account_id=".intval($accountId, 10);
		$result = mysql_query($sql, getWebsiteDB());
		$list=array();

		$row = mysql_fetch_assoc($result);
		mysql_free_result($result);
		return $row;
	}

	/**
	 * creates the return url
	 */
	public static function createReturnUrl() {
		if (isset($_SERVER['SCRIPT_URI'])) {
			$res = $_SERVER['SCRIPT_URI'];
		} else {
			// SCRIPT_URI seems to be set by mod_redirect only
			if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == "on")) {
				$res = 'https';
			} else {
				$res = 'http';
			}
			$res = $res.'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
		}
		
		$res = $res.'?id='.urlencode($_REQUEST['id']);
		if (isset($_REQUEST['url'])) {
			$res .= '&url='.urlencode($_REQUEST['url']);
		}
		if (isset($_REQUEST['social'])) {
			$res .= '&social='.urlencode($_REQUEST['social']);
		}
		if (isset($_REQUEST['tab'])) {
			$res .= '&tab='.urlencode($_REQUEST['tab']);
		}
		return $res;
	}

	/**
	 * logs the user in using the providing account link, automatically
	 * creates the account, if it does not exist, yet.
	 *
	 * @param AccountLink $accountLink
	 */
	public static function loginOrCreateByAccountLink($accountLink) {
		unset($_SESSION['account']);
		$account = Account::tryLogin($accountLink->type, $accountLink->username, null);
		
		if (!$account || is_string($account)) {
			$account = $accountLink->createAccount();
		}
		$_SESSION['account'] = $account;
		$_SESSION['csrf'] = createRandomString();
		$_SESSION['marauroa_authenticated_username'] = $account->username;
		fixSessionPermission();
	}

	/**
	 * verifies an email
	 * @param string $token
	 * @return boolean
	 */
	function verifyEMail($token) {
		$sql = "SELECT count(*) FROM email WHERE token='".mysql_real_escape_string($token)."'";
		$temp = queryFirstCell($sql, getGameDB());
		if ($temp == 0) {
			return false;
		}

		$sql = "UPDATE email SET address='".mysql_real_escape_string($_SERVER['REMOTE_ADDR']) 
			. "', confirmed=NOW() WHERE token='".mysql_real_escape_string($token)
			. "' AND (confirmed IS NULL OR confirmed='0000-00-00 00:00:00')";
		mysql_query($sql, getGameDB());
		return true;
	}


	public static $SELFBAN_RESULT_OK = 0; 
	public static $SELFBAN_RESULT_NOT_CONFIGURED = 1; 
	public static $SELFBAN_INVALID_TOKEN = 2; 
	public static $SELFBAN_ACCOUNT_ALREADY_BANNED = 3; 
	public static $SELFBAN_ACCOUNT_NOT_ACTIVE = 4;

	/**
	 * self bans an account
	 * @param int $accountid
	 * @param string $timestamp
	 * @param string $signature
	 * @return enum value
	 */
	public function selfban($accountid, $timestamp, $signature) {
		if (!defined('STENDHAL_SECRET')) {
			return Account::SELFBAN_RESULT_NOT_CONFIGURED;
		}

		$s = hash_hmac("sha512", $accountid.'/'.$timestamp, STENDHAL_SECRET);
		if ($signature != $s) {
			return Account::SELFBAN_INVALID_TOKEN;
		}

		$account = Account::readAccountById($id);
		$account->readAccountBan();

		if (isset($account->banMessage) || $account->status == 'banned') {
			return Account::SELFBAN_ACCOUNT_ALREADY_BANNED;
		}

		if ($account->status != 'active') {
			return Account::SELFBAN_ACCOUNT_NOT_ACTIVE;
		}

		//TODO
		
		
		return Account::SELFBAN_RESULT_OK;
	}
}


/**
 * Account Link
 *
 * @author hendrik
 */
class AccountLink {
	public $id;
	public $playerId;
	public $type;
	public $username;
	public $nickname;
	public $email;
	public $secret;

	/**
	 * creates a new AccountLink
	 */
	public function __construct($id, $playerId, $type, $username, $nickname, $email, $secret) {
		$this->id = $id;
		$this->playerId = $playerId;
		$this->type = $type;
		$this->username = $username;
		$this->nickname = $nickname;
		$this->email = $email;
		$this->secret = $secret;
	}

	public static function getAccountLinks($playerId) {
		$links = array();
		$sql = "SELECT id, player_id, type, username, nickname, email, secret "
		. "FROM accountLink "
		. "WHERE player_id ='".mysql_real_escape_string($playerId)."'";
		$result = mysql_query($sql, getGameDB());
		while($row = mysql_fetch_assoc($result)) {
			$links[] = new AccountLink($row['id'], $row['player_id'],
			$row['type'], $row['username'], $row['nickname'],
			$row['email'], $row['secret']);
		}
		mysql_free_result($result);
		return $links;
	}

	public static function findAccountLink($type, $username) {
		$sql = "SELECT id, player_id, type, username, nickname, email, secret "
		. "FROM accountLink "
		. "WHERE username ='".mysql_real_escape_string($username)."'"
		. " AND type = '".mysql_real_escape_string($username)."'";
		$result = mysql_query($sql, getGameDB());
		while($row = mysql_fetch_assoc($result)) {
			$links[] = new AccountLink($row['id'], $row['player_id'],
			$row['type'], $row['username'], $row['nickname'],
			$row['email'], $row['secret']);
		}
		mysql_free_result($result);
		return $links;
	}

	public function proposeUsernames() {
		$res = array();
		$res[] = Account::convertToValidUsername($this->nickname);
		if (isset($this->email)) {
			$pos = strpos($this->email, '@');
			if ($pos !== false) {
				$res[] = Account::convertToValidUsername(substr($this->email, 0, $pos));
			}
		}
		if ($this->type == 'facebook') {
			for ($i = 97; $i <= 122; $i++) {
				$res[] = Account::convertToValidUsername(substr($this->nickname, 0, 19).chr($i));
			}
		}
		if (strpos($this->username, 'http') === 0) {
			// apply openid url magic
			$lastSlash = strrpos($this->username, '/');
			if ($lastSlash < strlen($this->username) - 1) {
				$res[] = Account::convertToValidUsername(substr($this->username, $lastSlash + 1));
			} else {
				$lastSlash = strpos($this->username, '://') + 2;
				$dot = strpos($this->username, '.');
				$res[] = Account::convertToValidUsername(substr($this->username, $lastSlash + 1, $dot - $lastSlash - 1));
			}
		}
		$res[] = Account::convertToValidUsername($this->username);
		$res[] = $this->username;
		return $res;
	}

	public function createAccount() {
		// suggest usernames
		$proposedUsernames = $this->proposeUsernames();
		
		// create sql statement to check which suggestions exist
		mysql_query("BEGIN WORK", getGameDB());
		$first = true;
		$in = '';
		foreach($proposedUsernames As $name ) {
			if (!isset($name) || ($name == '')) {
				continue;
			}
			if ($first) {
				$first = false;
			} else {
				$in .= ', ';
			}
			$in .= "'".mysql_real_escape_string($name)."'";
		}

		$sql = "SELECT username FROM account WHERE username in (".$in.") FOR UPDATE "
			. "UNION SELECT charname FROM characters WHERE charname in (".$in.") FOR UPDATE;";

		// check database
		$existingUsernames = array();
		$result = mysql_query($sql, getGameDB());
		if (!$result) {
			echo htmlspecialchars($sql.': '.mysql_error(getGameDB()));
		}

		while($row = mysql_fetch_assoc($result)) {
			$existingUsernames[] = $row['username'];
		}
		mysql_free_result($result);

		// pick username
		foreach($proposedUsernames As $name ) {
			if ($name && trim($name) != '') {
				if (!in_array($name, $existingUsernames)) {
					$username = $name;
					break;
				}
			}
		}

		// trust google email addresses
		$trusted = (strpos($this->username, 'https://www.google.com/') === 0) && (strpos($this->email, '@') !== false);
		// insert
		$account = new Account(-1, $username, null, $this->email, $trusted, date("Y-m-d").' '.date("H:i:s"), 'active');
		$account->insert();
		$this->playerId = $account->id;
		$this->insert();
		
		mysql_query("COMMIT", getGameDB());

		return $account;
	}

	/**
	 * inserst a record in the accountLink table.
	 */
	public function insert() {
		$sql = "INSERT INTO accountLink(player_id, type, username";
		$sql2 = ") VALUES ('".mysql_real_escape_string($this->playerId)
			."', '".mysql_real_escape_string($this->type)
			."', '".mysql_real_escape_string($this->username)."'";
		if ($this->nickname) {
			$sql .= ", nickname";
			$sql2 .= ", '".mysql_real_escape_string($this->nickname)."'";
		}
		if ($this->email) {
			$sql .= ", email";
			$sql2 .= ", '".mysql_real_escape_string($this->email)."'";
		}
		if ($this->secret) {
			$sql .= ", secret";
			$sql2 .= ", '".mysql_real_escape_string($this->secret)."'";
		}
		$sql = $sql.$sql2.');';
		if (!mysql_query($sql, getGameDB())) {
			echo htmlspecialchars($sql.': '.mysql_error(getGameDB()));
		}
		$this->id = mysql_insert_id(getGameDB());
	}
}


function fixSessionPermission() {
	$id = session_id();
	if (preg_match('/^[a-zA-Z0-9]+$/', $id)) {
		chmod(session_save_path().'/sess_'.$id, 0640);
	}
}

/**
 * sends a udp message
 *
 * @param string $prefix  a prefix to tell message sources apart
 * @param string $message message
 */
function sendUdpMessage($prefix, $message) {
	$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
	$msg = $prefix.'/'.$message;
	socket_sendto($socket, $msg, strlen($msg), 0, '127.0.0.1', 7839);
	socket_close($socket);
}