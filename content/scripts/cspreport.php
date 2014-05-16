<?php

class CSPReportPage extends Page {

	public function writeHttpHeader() {
		header('HTTP/1.1 204');
		$sql = "INSERT INTO content_security_policy_report (address, useragent, content) "
			." VALUES ('".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."', '"
			. mysql_real_escape_string($_SERVER['HTTP_USER_AGENT'])."', '"
			. mysql_real_escape_string(file_get_contents('php://input'))."')";
		mysql_query($sql, getWebsiteDB());
		return false;
	}
}
$page = new CSPReportPage();
?>