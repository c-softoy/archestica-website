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

class DiffPage extends Page {

	public function writeHttpHeader() {
		if (!isset($_SESSION) || !isset($_SESSION['accountPermissions']) 
			|| ($_SESSION['accountPermissions']['view_history'] != '1')) {
			header('HTTP/1.1 403 Forbidden');
		}
		return true;
	}

	public function writeHtmlHeader() {
		echo '<title>Diff'.STENDHAL_TITLE.'</title>'."\n";
		echo '<meta name="robots" content="noindex">'."\n";
		?>
<style type="text/css">
del {
	color: red;
	background-color: #FFDDDD;
	text-decoration: none;
}
ins {
	color: green;
	background-color: #DDFFDD;
	text-decoration: none;
}
</style>
		<?php
	}

	function writeContent() {
		global $lang;
		if (!isset($_SESSION) || !isset($_SESSION['account'])) {
			startBox(t('Diff'));
			$currentPage = '/?id=content/association/diff&lang='.urlencode($lang).'&from='.urlencode($_REQUEST['from']).'&to='.urlencode($_REQUEST['to']);
			echo '<p>'.t('You need to').' <a href="'.STENDHAL_LOGIN_TARGET.'/?id=content/association/login&amp;url='.urlencode($currentPage).'">'.t('login').'</a> '.t('in order to view the history.').'</p>';
			endBox();
			return;
		}
		if (!isset($_SESSION) || !isset($_SESSION['accountPermissions']) 
			|| ($_SESSION['accountPermissions']['view_history'] != '1')) {
			startBox(t('Diff'));
			echo '<p>'.t('You are missing the required permission for this action.').'</p>';
			endBox();
			return;
		}

		$from = -1;
		$to = -1;
		if (isset($_REQUEST['from'])) {
			$from = intval($_REQUEST['from'], 10);
		}
		if (isset($_REQUEST['to'])) {
			$to = intval($_REQUEST['to'], 10);
		}
		if ($from <= 0 && $to <= 0) {
			startBox(t('Diff'));
			echo '<p>'.t('Both from and to parameters are invalid.').'</p>';
			endBox();
			return;
		}

		if ($from < 0) {
			$from = CMS::getPreviousVersion($to);
		}
		if ($to < 0) {
			$to = CMS::getLatestVersion($from);
		}
		$old = CMS::readPageVersion($from);
		$new = CMS::readPageVersion($to);
		$oldText = '';
		if (isset($old)) {
			$oldText = $old->content;
		}
		startBox(t('Diff'));
		require_once 'lib/finediff/finediff.php';
		$opcodes = FineDiff::getDiffOpcodes($oldText, $new->content, FineDiff::wordDelimiters);
		$diff = FineDiff::renderDiffToHTMLFromOpcodes($old->content, $opcodes);
		echo str_replace('&lt;br&gt;', '<br>', str_replace('&lt;/p&gt;', '', str_replace('&lt;p&gt;', '<br>', $diff))); 
		endBox();
	}
}
$page = new DiffPage();
