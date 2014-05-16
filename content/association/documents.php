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


class DocumentPage extends Page {
	private $filename;
	private $justFilename;
	private $relativeFilename;
	
	public function __construct() {
		$file = '';
		if (isset($_REQUEST['file'])) {
			$file = $_REQUEST['file'];
		}
		if ((strpos($file, '..') !== false) || (strpos($file, ':') !== false)) {
			return;
		}
		$this->relativeFilename = $file;
		$pos = strrpos($file, '/');
		$this->justFilename = substr($file, $pos);
		$this->filename = STENDHAL_DOCUMENT_DIRECTORY.'/'.$file;
	}

	public function writeHttpHeader() {
		if (!isset($this->filename)) {
			header('HTTP/1.0 404 Not found.');
			return true;
		}

		if (!isset($_SESSION) || !isset($_SESSION['accountPermissions']) 
			|| ($_SESSION['accountPermissions']['view_documents'] != '1')) {
			header('HTTP/1.0 403 Forbidden.');
			return true;
		}
		if (!is_dir($this->filename)) {
			$this->streamFile();
			return false;
		}
		return true;
	}

	public function writeHtmlHeader() {
		echo '<title>Documents'.STENDHAL_TITLE.'</title>';
		echo '<meta name="robots" content="noindex">'."\n";
	}

	function writeContent() {
		startBox(t('Documents'));

		if (!isset($_SESSION) || !isset($_SESSION['account'])) {
			echo '<p>'.t('You need to').' <a href="'.STENDHAL_LOGIN_TARGET.'/?id=content/association/login">'.t('login').'</a></p>';
		} else if (!isset($_SESSION) || !isset($_SESSION['accountPermissions']) 
			|| ($_SESSION['accountPermissions']['view_documents'] != '1')) {
			echo '<p>'.t('Sorry, internal documents are only available to members.').'</p>';
		} else if (is_dir($this->filename)) {
			$this->renderDirectory();
		} else { // files are handled in writeHttpHeader
			echo '<p>'.t('Sorry, the requested document was not found.').'</p>';
		}
		endBox();
	}

	/**
	 * writes a directory listing
	 */
	function renderDirectory() {
		global $lang;
		$dir = opendir($this->filename);
		while (false !== ($file = readdir($dir))) {
			if (strpos($file, '.') !== 0) {
				$files[] = $file;
			}
		}
		closedir($dir);
		sort($files);

		echo '<p><b>'.t('These are the internal documents of the association.').'</b></p>';
		echo '<ul>';
		$temp = $this->relativeFilename;

		// if the relative path is not epmty and does not start with a "/", add a "/" at the beginning.
		if ((strlen($temp) > 0) && strpos($temp, '/') !== 0) {
			$temp = '/' . $temp;
		}
		foreach ($files as $file) {
			echo '<li><a href="'.rewriteURL('/'.$lang.'/documents'.htmlspecialchars($temp.'/'.$file)).'">'.htmlspecialchars($file).'</a></li>';
		}
		echo '</ul>';
	}

	/**
	 * streams a file
	 */
	function streamFile() {

		// content type
		$pos = strrpos($this->filename, '.');
		$ext = strtolower(substr($this->filename, $pos + 1));
		$mimeType = 'application/octet-stream';
		if ($ext == 'jpg' || $ext == 'jpeg') {
			$mimeType = 'image/jpeg';
		} else if ($ext == 'png') {
			$mimeType = 'image/png';
		} else if ($ext == 'txt') {
			$mimeType = 'text/plain';
		} else if ($ext == 'html') {
			$mimeType = 'text/html';
		} else if ($ext == 'pdf') {
			$mimeType = 'application/pdf';
		}

		header('Content-Type: '.$mimeType);
		header('Content-Disposition: atachment; filename='.$this->justFilename);
		header('Content-Length: '.filesize($this->filename));
		$file = fopen($this->filename, 'r');
		echo fread($file, filesize($this->filename));
		fclose($file);
	}
}

$page = new DocumentPage();
?>