<?php

require_once('scripts/account.php');

function validateParameters() {
	$username = $_SESSION['account']->username;

	if(!checkLogin()){
		return "You need to login first";
	}

	/* Check that all fields were typed in */

	if (!$_POST['newpass'] || !$_POST['newpass_retype']){
		return 'You didn\'t fill in all fields.';
	}

	if ($_SESSION['account']->password) {
		if (!$_POST['pass']) {
			return 'You didn\'t enter your old password.';
		}
	}

	if($_SESSION['account']->username == $_POST['newpass']) {
		return 'You cannot use your username as password.';
	}

	if($_POST['newpass']!=$_POST['newpass_retype']) {
		return 'Password incorrectly typed.';
	}

	if(strlen($_POST['newpass']) < 6) {
		return 'The password needs to be at least 6 characters long.';
	}

	if (!$_SESSION['account']->password) {
		return '';
	}

	$result = Account::tryLogin("passwordchange", $username, $_POST['pass']);
	if (! ($result instanceof Account)) {
		return 'The old password was wrong.';
	}

	return "";
}

function changePassword() {
	$username = $_SESSION['account']->username;
	
	/* Verify that user is in database */
	$md5newpass = strtoupper(md5($_POST['newpass']));
	$q = "update account set password='".mysql_real_escape_string(Account::sha512crypt($md5newpass))."' where username = '".mysql_real_escape_string($username)."'";
	$result = mysql_query($q,getGameDB());
	
	if (mysql_affected_rows()!=1) {
		die('Problem updating database');
	}

	// reread the accoutn form the database, so that it is not flagged as passwordless account anymore.
	$_SESSION['account'] = Account::readAccountByName($_SESSION['account']->username);

	startBox("Password Change");
		echo '<h1>Your password has been changed successfully.</h1> <h4>Remember to update and re-save any login profile you may have stored.</h4> Moving to main page.';
	endBox();
}

function handleValidationError($error) {
	startBox("Password Change Failed");
		echo '<p>'.htmlspecialchars($error).'</p>';
	endBox();
}


class ChangePasswordPage extends Page {

	public function writeHtmlHeader() {
		echo '<title>Change Password'.STENDHAL_TITLE.'</title>';
		echo '<meta name="robots" content="noindex">'."\n";
	}

	function writeContent() {
		if (!isset($_SESSION['account'])) {
			startBox("Change Password");
			echo '<p>Please <a href="'.STENDHAL_LOGIN_TARGET.'/index.php?id=content/account/login&amp;url=/account/change-password.html">login</a> to change your password.</p>';
			endBox();
		} else {
			$this->process();
		}
	}
	
	function process() {

/**
 * Checks to see if the user has submitted his
 * username and password through the login form,
 * if so, checks authenticity in database and
 * creates session.
 */
if(isset($_POST['sublogin'])){

	$error = validateParameters();
	if ($error == '') {
		changePassword();
	} else {
		handleValidationError($error);
	}
	
} else {
startBox("Change password");
if (!$_SESSION['account']->password) {
	echo '<p>You can create a local account here. The local account can be used in the download client.</p>';
}
?>

<form id="changePasswordForm" action="" method="post">
<table>
  <tr><td>Account name:</td><td><?php echo htmlspecialchars($_SESSION['account']->username);?>
  <input id="sessionUsername" type="hidden" value="<?php echo htmlspecialchars($_SESSION['account']->username);?>">
  </td></tr>
  <?php if ($_SESSION['account']->password) {?>
  <tr><td>Old Password:</td><td><input type="password" id="pass" name="pass" maxlength="30"></td></tr>
  <?php }?>
  <tr><td>New Password:</td><td><input type="password" id="newpass" name="newpass" maxlength="30"></td></tr>
  <tr><td>Retype new Password:</td><td><input type="password" id="newpass_retype" name="newpass_retype" maxlength="30"></td></tr>
  <tr><td colspan="2" align="right"><input type="submit" name="sublogin" value="Change Password"></td></tr>
</table>
</form>
<?php
endBox();
	$this->includeJs();
}
	}
}
$page = new ChangePasswordPage();
?>
