<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008-2011 The Arianne Project

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
require_once('scripts/openid-provider.php');

class OpenIDProviderPage extends Page {

	public function writeHttpHeader() {
		$provider = new MySQLBasedOpenidProvider();
		$provider->serverLocation = STENDHAL_LOGIN_TARGET.'/?id=content/account/openid-provider';
		$provider->xrdsLocation = STENDHAL_LOGIN_TARGET.'/?id=content/account/openid-provider&xrds&select=false';
		if (!isset($_REQUEST['select'])) {
			$provider->select_id=true;
		}
		$provider->server();
		return false;
	}

	function writeContent() {
	}

}
$page = new OpenIDProviderPage();
?>
