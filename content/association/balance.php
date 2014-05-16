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

class BalancePage extends Page {

	public function writeHttpHeader() {
		if (!isset($_SESSION) || !isset($_SESSION['accountPermissions']) 
			|| ($_SESSION['accountPermissions']['view_documents'] != '1')) {
			header('HTTP/1.1 403 Forbidden');
		}
		return true;
	}

	public function writeHtmlHeader() {
		echo '<title>Balance'.STENDHAL_TITLE.'</title>'."\n";
		echo '<meta name="robots" content="noindex">'."\n";
	}

	function writeContent() {
		global $lang;
		if (!isset($_SESSION) || !isset($_SESSION['account'])) {
			startBox(t('Balance'));
			$currentPage = '/?id=content/association/balance';
			echo '<p>'.t('You need to').' <a href="'.STENDHAL_LOGIN_TARGET.'/?id=content/association/login&amp;url='.urlencode($currentPage).'">'.t('login').'</a> '.t('in order to view the balance.').'</p>';
			endBox();
			return;
		}
		if (!isset($_SESSION) || !isset($_SESSION['accountPermissions']) 
		|| ($_SESSION['accountPermissions']['view_documents'] != '1')) {
			startBox(t('Balance'));
			echo '<p>'.t('You are missing the required permission for this action.').'</p>';
			endBox();
			return;
		}

		startBox(t('Balance'));

		echo '<p>'.t('Automatic query of balance').'</p>';

		echo '<table class="prettytable"><tr><td>Sparkasse Lemgo: </td><td style="text-align:right">';
		passthru('/usr/local/bin/getbalance-sparkasse.sh');
		echo ' EUR </td></tr>';

		echo '<tr><td>Paypal: </td><td style="text-align:right">';
		passthru('/usr/local/bin/getbalance-paypal.sh');
		echo ' EUR </td></tr></table>';

		echo '<p>Monthly costs for the server of 51 EUR are debited from Sparkasse Lemgo at the beginning of each month.</p>';
		echo '<p>'.t('The invoices and journal are available in the manually updated document section.').'</p>';
		endBox();
	}
}
$page = new BalancePage();
?>