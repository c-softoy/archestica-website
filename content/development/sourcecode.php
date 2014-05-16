<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008-2010 The Arianne Project

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

class SourceCodePage extends Page {

	public function __construct() {
	}

	public function writeHtmlHeader() {
		echo '<meta name="robots" content="noindex">'."\n";
		echo '<title>Messages'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {
		$this->writeInfo();
	}
	
	function writeInfo() {
		startBox('Source Code of Archestica');
		?>
<p>Archestica is based on the Stendhal MMORPG. As Stendhal is licensed under the GPLv2, we are required to make our source code available to others. </p><p>Please see the links below to get access to our source code.</p>
<br/><p><a href="https://github.com/sourceress-project/archestica/">Archestica - server & client</a></p>
<p><a href="https://github.com/sourceress-project/archestica-website/">Archestica website</a></p>
		<?php
		endBox('Source Code of Archestica');
	}


}
$page = new SourceCodePage();
?>
