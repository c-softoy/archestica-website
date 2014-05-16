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

class HistoryPage extends Page {
	private $history;
	public function __construct() {
		global $lang;
		$this->history = CMS::readHistory($lang, $_REQUEST['title']);
	}

	public function writeHttpHeader() {
		if (!isset($_SESSION) || !isset($_SESSION['accountPermissions']) 
			|| ($_SESSION['accountPermissions']['view_history'] != '1')) {
			header('HTTP/1.1 403 Forbidden');
		}
		if (count($this->history) == 0) {
			header('HTTP/1.1 404 Not found');
		}
		return true;
	}

	public function writeHtmlHeader() {
		echo '<title>History'.STENDHAL_TITLE.'</title>'."\n";
		echo '<meta name="robots" content="noindex">'."\n";
	}

	function writeContent() {
		global $lang;
		$title = '';
		if (isset($_REQUEST['title'])) {
			$title = $_REQUEST['title'];
		}
		if (!isset($_SESSION) || !isset($_SESSION['account'])) {
			startBox(t('History'));
			$currentPage = '/?id=content/association/history&lang='.surlencode($lang).'&title='.urlencode($title);
			echo '<p>'.t('You need to').' <a href="'.STENDHAL_LOGIN_TARGET.'/?id=content/association/login&amp;url='.urlencode($currentPage).'">'.t('login').'</a> '.t('in order to view the history.').'</p>';
			endBox();
			return;
		}
		if (!isset($_SESSION) || !isset($_SESSION['accountPermissions']) 
			|| ($_SESSION['accountPermissions']['view_history'] != '1')) {
			startBox(t('History'));
			echo '<p>'.t('You are missing the required permission for this action.').'</p>';
			endBox();
			return;
		}

		if (count($this->history) == 0) {
			startBox(t('History'));
			echo '<p>'.t('Sorry, the requested page does not exist.').'</p>';
			endBox();
			return;
		}

		echo '<form method="GET">';
		startBox(t('History').' '.htmlspecialchars(ucfirst($title)));
		echo '<ul class="changehistory">';
		foreach ($this->history as $entry) {
			echo '<li>(<a href="/?id=content/association/diff&amp;lang='.urlencode($lang).'&amp;to='.urlencode($entry->id)
			.'">diff</a>) <b>'.htmlspecialchars($entry->lang).'/'.htmlspecialchars($entry->title)
				.'</b>: '.htmlspecialchars($entry->username).' '.htmlspecialchars($entry->timedate).'<br>'
				.'<i>'.htmlspecialchars($entry->commitcomment).'</i></li>';
		}
		echo '</ul>';
		endBox();
	}
}
$page = new HistoryPage();
?>