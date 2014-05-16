<?php

class Registration {
  public $realname;
  public $street;
  public $city;
  public $country;
  public $email;
  public $visiblename;
  public $visibleemail;

  function __construct($realname, $street, $city, $country, $email, $visiblename, $visibleemail) {
	$this->realname = $realname;
	$this->street = $street;
	$this->email = $email;
	$this->city = $city;
	$this->country = $country;
	$this->visiblename = $visiblename;
	$this->visibleemail = $visibleemail;
  }

}

function saveMemberdata($playerId) {
	if (isset($_POST["realname"])) {
		$query = "insert into members (realname, street, city, country, email, visiblename, visibleemail, player_id) values "
		 . "('" . mysql_real_escape_string($_POST["realname"]) . "', '" . mysql_real_escape_string($_POST["street"])
		 . "', '" . mysql_real_escape_string($_POST["city"]). "', '" . mysql_real_escape_string($_POST["country"])
		 . "', '" . mysql_real_escape_string($_POST["email"]) . "', '" . mysql_real_escape_string($_POST["visiblename"])
		 . "', '" . mysql_real_escape_string($_POST["visibleemail"]) . "', '" . $playerId . "');";
		return mysql_query($query, getWebsiteDB());
	}
	return false;
}

function getMemberdata($playerId) {
	$query = "select * from members where player_id='".mysql_real_escape_string($playerId)."' ORDER BY id DESC LIMIT 1;";
	$result = mysql_query($query, getWebsiteDB());

	while($row = mysql_fetch_assoc($result)) {

		$registration = new Registration(
			$row['realname'],
			$row['street'],
			$row['city'],
			$row['country'],
			$row['email'],
			$row['visiblename'],
			$row['visibleemail']
		);
	}

	mysql_free_result($result);
	
	return $registration;
}