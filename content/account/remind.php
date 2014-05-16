<?php


die ('Not implemented, yet');

class RemindPage extends Page {
	private $error;
	private $success;

	public function writeHtmlHeader() {
		echo '<title>Password Reset'.STENDHAL_TITLE.'</title>';
		echo '<meta name="robots" content="noindex">'."\n";
	}

	function writeContent() {

		/**
		 * It is composed of two steps:
		 * a) User click new password and we email a md5(rand()) to him IF the account is registered with stendhal.
		 * b) The user confirms the link and we effectively change the password.
		 */
		if(isset($_POST['account'])) {
			$this->process();
		} else {

			$this->showForm();
		}
	}

	function process() {
		$this->error = false;
		$this->success = false;

		$username = $_POST['account'];
		$account = Account::readAccountByName($username);
		
		if (!isset($account) || $account->status != 'active') {
			$this->error = true; // 'The account does not exis, was merged into another account, is not active or does not have a valid email-address.';
			return;
		}

		$email = $account->email;
		if (($email == '') || (strpos($email, '@') === false)) {
			$this->error = true;
			return;
		}

		$signature=strtoupper(md5(rand()));
	
		// Good, store it...
		$query='insert into remind_password values("'.mysql_real_escape_string($username).'","'.$signature.'",null)';
		if(!mysql_query($query, getWebsiteDB())) {
			echo '<span class="error">There has been a problem while sending your password.</span>';
			die();
		}
	
		// Remove the entry or anything 48 hours old.
		$q = "delete from remind_password where datediff(now(),requested)>2";
		$result = mysql_query($q,getWebsiteDB());
	
		// ...and email
		$server=$_SERVER["SERVER_NAME"];
		$location=str_replace("/index.php","",$_SERVER["PHP_SELF"]);
	
		$clientip=$_SERVER['REMOTE_ADDR'];
	
		$body=file_get_contents("login/remindpassword.email");
	
		// Fill variables
		$body = str_replace("[SERVER]", $server.$location, $body);
		$body = str_replace("[SIGNATURE]", $signature, $body);
		$body = str_replace("[CLIENTIP]", $clientip, $body);
		$body = str_replace("[ACCOUNT]", $username, $body);
		
		if ($body == false) {
			echo '<span class="error">There has been a problem while getting password email template.</span>';
			die();
		}
	
		$headers = 'From: noreply@stendhalgame.org';
		if(!mail($email,"Password reset request",$body,$headers)) {
			echo '<span class="error">There has been a problem while sending your password email.</span>';
			die();
		}
	
		startBox("Password reset link emailed");
		?>
		We have just sent you a link to reset your password.<br>
		Check you inbox and follow the email instructions.
		<p>
		Back to <a href="?">Main</a>
		<?php
		endBox();

	}

	function showForm() {
		startBox("Forgot your password?");
		?>
		In case you have forgotten your new password or your account information we can send you it to your email account that you used to create your stendhal account.<p>
		<form action="" method="post">
		<table>
			<tr><td>Account:</td><td><input type="text" name="account" maxlength="90"></td></tr>
			<tr><td colspan="2" align="right"><input type="submit" name="forgotpassword" value="Get new password"></td></tr>
		</table>
		</form>
		<?php
		endBox();
	}
}

$page = new RemindPage();

