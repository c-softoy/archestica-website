<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2010 the Arianne Project

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


class UploadPage extends Page {

	public function writeHttpHeader() {
		if (!isset($_FILES['file'])) {
			header('HTTP/1.0 404 Not Found');
		}
		return true;
	}

	public function writeHtmlHeader() {
		echo '<title>Upload'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {
		if (!isset($_FILES['file'])) {
			startBox('Upload');
			echo 'Upload failed. Did you select a file?';
			endBox();
		} else {
			startBox('Upload');
			move_uploaded_file($_FILES['file']['tmp_name'], "/srv/upload/".date('ymd_His').'_'.$_SERVER["REMOTE_ADDR"].'.txt');
			echo 'Thanks for you help.';
			endBox();
		}
	}
}
$page = new UploadPage();
?>