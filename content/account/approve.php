<?php

function createRandomPassword() {
	$chars = "abcdefghijkmnopqrstuvwxyz023456789";
	$i = 0;
	$pass = '' ;
	$amount=strlen($chars);

	while ($i <= 7) {
		$num = rand() % $amount;
		$pass = $pass . $chars[$num];
		$i++;
	}

	return $pass;
}

class ApprovePage extends Page {

	public function writeHtmlHeader() {
		echo '<title>Approve Password Reset'.STENDHAL_TITLE.'</title>';
		echo '<meta name="robots" content="noindex">'."\n";
	}

	function writeContent() {

		if(!isset($_GET["sign"])) {
			die('You didn\'t fill in a required field.');
		}

		$signature=$_GET["sign"];
		$signature=mysql_real_escape_string($signature);

		// Get the user name from the username<->hash relation
		$query='select username from remind_password where confirmhash="'.$signature.'"';
		$result = mysql_query($query, getWebsiteDB());

		if(mysql_numrows($result)!=1) {
			mysql_free_result($result);

			startBox("No such username");
			?>
			We are unable to find a valid username associated to that email account.
			<p>Your password can not be reset.
			<p>Back to <a href="?">Main</a>
			<?php
			endBox();

		} else {

			$row=mysql_fetch_assoc($result);
			$username=$row["username"];
			mysql_free_result($result);

			// Remove the entry or anything 48 hours old.
			$q = "delete from remind_password where username = '".mysql_real_escape_string($username)."' or  datediff(now(),requested)>2";
			$result = mysql_query($q,getWebsiteDB());

			// Create a random password for it and set it.
			$newpassword=createRandomPassword();

			$md5newpass = strtoupper(md5($newpassword));
			$q = "update account set password='".mysql_real_escape_string($md5newpass)."' where username = '".mysql_real_escape_string($username)."'";
			$result = mysql_query($q,getGameDB());

			// Show user the new password.
			startBox("New password generated");
			?>
			Per your request we have reset the password of your account "<b><?php echo $username; ?></b>".<br>
			Your new password is "<b><?php echo $newpassword; ?></b>".
			<p>Store it on a secure place.
			<?php
			endBox();
		}
	}
}

$page = new ApprovePage();
