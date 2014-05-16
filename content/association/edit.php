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

class EditPage extends Page {
	private $cmsPage;
	public function __construct() {
		global $lang, $internalTitle;
		$this->cmsPage = CMS::readNewestVersion($lang, $internalTitle);
	}

	public function writeHttpHeader() {
		if (!isset($_SESSION) || !isset($_SESSION['accountPermissions']) 
			|| ($_SESSION['accountPermissions']['edit_page'] != '1')) {
			header('HTTP/1.1 403 Forbidden');
		}
		header_remove('Content-Security-Policy');
		return true;
	}

	public function writeHtmlHeader() {
		echo '<title>Edit'.STENDHAL_TITLE.'</title>'."\n";
		echo '<meta name="robots" content="noindex">'."\n";
		echo '<script type="text/javascript" src="/lib/ckeditor/ckeditor.js"></script>';
		$this->includeJs();
	}

	function writeContent() {
		global $lang;
		$title = $_REQUEST['title'];
		if ($title == '') {
			$title = 'Faiumoni';
		}
		if (!isset($_SESSION) || !isset($_SESSION['account'])) {
			startBox(t('Editing').' '.htmlspecialchars(ucfirst($title)));
			$currentPage = '/?id=content/association/edit&lang='.urlencode($lang).'&title='.urlencode($_REQUEST['title']);
			echo '<p>'.t('You need to').' <a href="'.STENDHAL_LOGIN_TARGET.'/?id=content/association/login&amp;url='.urlencode($currentPage).'">'.t('login').'</a> '.t('in order to edit pages.').'</p>';
			endBox();
			return;
		}
		if (!isset($_SESSION) || !isset($_SESSION['accountPermissions']) 
		|| ($_SESSION['accountPermissions']['edit_page'] != '1')) {
			startBox(t('Edit'));
			echo '<p>'.t('You are missing the required permission for this action.').'</p>';
			endBox();
			return;
		}
		
		echo '<form method="POST">';
		startBox(t('Editing').' '.htmlspecialchars(ucfirst($title)));
		echo '<p>'.t('Please skip Heading 1 and start with Heading 2.').'</p>';
		echo '<p>'.t('Back to article').' <a href="'.rewriteURL('/'.$lang.'/'.surlencode($_REQUEST['title']).'.html').'">'.htmlspecialchars(ucfirst($title)).'</a></p>';
		$displaytitle = '';
		if (isset($_POST['displaytitle'])) {
			$displaytitle = $_POST['displaytitle'];
		} else if ($this->cmsPage != null) {
			$displaytitle = $this->cmsPage->displaytitle;
		} else {
			$displaytitle = $_REQUEST['title'];
		}
		echo '<label for="displaytitle">'.t('Display title').': </label><input name="displaytitle" value="'.htmlspecialchars($displaytitle).'" style="width:99%"><br>';
		echo '<label for="commitcomment">'.t('Commit comment').': </label><input name="commitcomment" style="width:99%">';
		endBox();

		if (isset($_POST['editor'])) {
			$html = $this->filterHtml($_POST['editor']);
			if ($_POST['csrf'] != $_SESSION['csrf']) {
				startBox(t('Error'));
				echo '<p class="error">'.t('Session information was lost. Please save again.').'</p>';
				endBox();
			} else {
				CMS::save($lang, $_REQUEST['title'], $html, $_REQUEST['commitcomment'], $_REQUEST['displaytitle'], $_SESSION['account']->id);
			}
		}


		echo '<input name="csrf" type="hidden" value="'.htmlspecialchars($_SESSION['csrf']).'">';
		echo '<textarea id="editor" name="editor" style="width:100%; height:30em">';
		if ($_POST['editor']) {
			echo htmlspecialchars($html);
		} else if ($this->cmsPage != null) {
			echo htmlspecialchars($this->cmsPage->content);
		}
		echo '</textarea>';
		echo '</form>';
	}

	function filterHtml($html) {
		require_once 'lib/htmlpurifier/library/HTMLPurifier.path.php';
		require_once 'HTMLPurifier.includes.php';
		$config = HTMLPurifier_Config::createDefault();
		$config->set('HTML.Doctype', 'HTML 4.01 Transitional');
		$config->set('Attr.EnableID', true);
		$purifier = new HTMLPurifier($config);
		return $purifier->purify($html);
	}
}
$page = new EditPage();
?>