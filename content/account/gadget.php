<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008-2012 The Arianne Project

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

require_once 'mycharacters.php';
require_once 'fb.php';
require_once 'createcharacter.php';

class GadgetPage extends Page {
	private $api;

	function __construct() {
		$this->api = new Facebook();
	}

	public function writeHttpHeader() {
		// Privacy Policy for cookies and cookie-linked data according to http://www.w3.org/TR/P3P/
		// CUR   Completion and Support of Activity For Which Data Was Provided    Information may be used by the service provider to complete the activity for which it was provided, whether a one-time activity such as returning the results from a Web search, forwarding an email message, or placing an order; or a recurring activity such as providing a subscription service, or allowing access to an online address book or electronic wallet.
		// ADM   Web Site and System Administration                                Information may be used for the technical support of the Web site and its computer system. This would include processing computer account information, information used in the course of securing and maintaining the site, and verification of Web site activity by the site or its agents.
		//
		// OUR   Ourselves and/or entities acting as our agents or entities for whom we are acting as an agent      An agent in this instance is defined as a third party that processes data only on behalf of the service provider for the completion of the stated purposes. (e.g., the service provider and its printing bureau which prints address labels and does nothing further with the information.)
		//
		// ONL   Online Contact Information   Information that allows an individual to be contacted or located on the Internet -- such as email. Often, this information is independent of the specific computer used to access the network. (See the category "Computer Information")
		// UNI   unique Identifiers           Non-financial identifiers, excluding government-issued identifiers, issued for purposes of consistently identifying or recognizing the individual. These include identifiers issued by a Web site or service. 
		// COM   Computer Information         Information about the computer system that the individual is using to access the network -- such as the IP number, domain name, browser type or operating system.
		// ...i  opt - in (account creation in our case)
		header('P3P: CP="CUR ADM OUR ONLi UNIi COM"');
		header_remove('X-Frame-Options');
		$this->writePageStart();
		$this->writeContent();
		$this->writePageEnd();
		return false;
	}

	function writePageStart() {
		echo '<!DOCTYPE html>';
		echo '<html><head>';
		echo '<title>Stendhal</title>';
		?>
		<style type="text/css">
.gadgetcanvas {

	width: 570px; 
	border: 1px solid black
	margin: 0;
	padding: 0;
	font-family: Arial, Helvetica, "Liberation Sans", "Lucida Sans", Sans-serif;
	font-size: 12pt;
}

.box {
	margin: 4px;
	padding: 0;
	border: solid 2px black;
	background-color: white;
}

.boxTitle {
	margin: 0;
	padding: 3px;
	color: white;
	font-weight: bolder;
	background-image: url("/data/gui/panel_tile_aqua_001.png");
	background-repeat: repeat;
	border: outset 2px brown;
}

.boxContent {
	color: Black;
	padding: 4px;
}

.onlinePlayer {
	width: 80px;
	text-align: center;
	float: left;
	font-size: 12px;
	font-weight: bold;
}

.characterHeight {
	height: 140px;
}

.onlineLink {
	text-decoration: none;
	color: black;
}
span.block {
	display: block;
	overflow-x: hidden;
}

.outfitpanel {border: 1px solid black; width: 8.5em; height: 256px; padding: 0em; float: left; margin-right: 2em; margin-bottom: 2em}
.prev, .next {float: left; margin-top: 2em}
.outfitpart {float: left; display: block; width:48px; height: 64px; background-position: 0 128px;}

		</style>
		<?php
		echo '</head><body>';
	}

	function writeContent() {

		// authenticate
		if (!isset($_SESSION['account'])) {

			// is this a request with authentication information?
			if (isset($_REQUEST['signed_request'])) {
				$accountLink = $this->api->createAccountLinkForSignedRequest();
			} else if (isset($_REQUEST['code'])) {
				$accountLink = $this->api->createAccountLink();
			}
			if (isset($accountLink)) {
				Account::loginOrCreateByAccountLink($accountLink);
			}

			// redirect to app authorisation page
			if (!isset($_SESSION['account'])) {
				echo '<span id="gadget-redirect" href="'.$this->api->getCanvasAuthUrl().'"></span>';
				return;
			}
		}


		// we are now authenticated

		echo '<div class="gadgetcanvas">';

		$createURL = '/?id=content/account/gadget&tab=create&social='.urlencode($_REQUEST['social']);

		$tab = 'mycharacters';
		if (isset($_REQUEST['tab'])) {
			$tab = $_REQUEST['tab'];
		}

		// If there are no characters, go to the char creation page
		if ($tab == 'mycharacters') {
			$players = getCharactersForUsername($_SESSION['account']->username);
			if (sizeof($players) == 0) {
				$tab = 'create';
			}
		}

		// handle character creation
		if ($tab == 'create') {
			$page = new CreateCharacterPage();
			$res = $page->process();
			if (!$res) {
				$page->show($createURL);
			} else {
				$tab = 'mycharacters';
			}
		}

		// handle character list
		if ($tab == 'mycharacters') {
			startBox('Links');
			echo '<a target="_blank" href="https://stendhalgame.org">Game Server Website</a>&nbsp;&nbsp;&nbsp;';
			echo '<a target="_blank" href="https://stendhalgame.org/world/atlas.html"> Atlas</a>&nbsp;&nbsp;&nbsp;';
			echo '<a target="_blank" href="https://stendhalgame.org/wiki/Stendhal_Manual/Controls_and_Game_Settings">Manual</a>&nbsp;&nbsp;&nbsp;';
			echo '<a target="_blank" href="https://stendhalgame.org/wiki/BeginnersGuide">Beginner\'s Guide</a>&nbsp;&nbsp;&nbsp;';
			endBox();
			$page = new MyCharactersPage();
			$page->writeCharacterList(6, $createURL);
		}

		echo '</div>';
	}

	function writePageEnd() {
		global $frame;
		echo '</div>';
		$frame->includeJs();
		echo '</body></html>';
	}
}
$page = new GadgetPage();
