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

class MessagesPage extends Page {

	public function __construct() {
		$this->setupFilter();
	}

	public function writeHtmlHeader() {
		echo '<meta name="robots" content="noindex">'."\n";
		echo '<title>Messages'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {
		if(!isset($_SESSION['account'])) {
			startBox("Messages");
			echo '<p>Please <a href="'.STENDHAL_LOGIN_TARGET.'/index.php?id=content/account/login&amp;url='.rewriteURL('/account/messages.html').'">login</a> to view your personal messages.</p>';
			endBox();
		} else {
			$this->writeTabs();
			$this->deleteMessagesIfRequested();
			$this->printStoredMessages();
			$this->closeTabs();
		}
	}
	
	function getFilter($filter) {
		if ($filter=="to-me") {
			$where="characters.charname = postman.target AND messagetype <>'N' AND deleted != 'R'";
		} else if ($filter=="to-me-npcs") {
			$where="characters.charname = postman.target AND messagetype ='N'";
		} else if ($filter=="from-me") {
			$where= "characters.charname = postman.source AND deleted != 'S'";
		}
		return $where;
	}

	function setupFilter() {
		$this->filter = 'to-me';
		if (isset($_REQUEST['filter'])) {
			$this->filter = urlencode($_REQUEST['filter']);
		}
		$this->filterWhere=$this->getFilter($this->filter);
		// TODO: 404 on invalid filter variable
	}
	
	function writeTabs() {
		$playerId = $_SESSION['account']->id;
		?>
		<br>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr>
		<td class="barTab" width="2%"> &nbsp;</td>
		<?php echo '<td class="'.$this->getTabClass('to-me').'" width="25%"><a class="'.$this->getTabClass('to-me').'A" href="'.htmlspecialchars(rewriteURL('/account/messages/to-me.html')).'">To Me ['.StoredMessage::getCountUndeliveredMessages($playerId, $this->getFilter("to-me")).']</a></td>';?>
		<td class="barTab" width="2%"> &nbsp;</td>
		<?php echo '<td class="'.$this->getTabClass('to-me-npcs').'" width="25%"><a class="'.$this->getTabClass('to-me-npcs').'A" href="'.htmlspecialchars(rewriteURL('/account/messages/to-me-npcs.html')).'">To Me (NPCs) ['.StoredMessage::getCountUndeliveredMessages($playerId, $this->getFilter("to-me-npcs")).']</a></td>';?>
		<td class="barTab" width="2%"> &nbsp;</td>
		<?php echo '<td class="'.$this->getTabClass('from-me').'" width="25%"><a class="'.$this->getTabClass('from-me').'A" href="'.htmlspecialchars(rewriteURL('/account/messages/from-me.html')).'">From Me</a></td>';?>
		<td class="barTab">&nbsp;</td>
		</tr>
		<tr><td colspan="7" class="tabPageContent">
		<br>
		<?php
	}


	function closeTabs() {
		?></td></tr></table><?php 
	}

	function getTabClass($tab) {
		if ($this->filter == $tab) {
			return 'activeTab';
		} else {
			return 'backgroundTab';
		}
	}

	function deleteMessagesIfRequested() {
		if (!isset($_POST['csrf'])) {
			return;
		}
		if (($_POST['csrf'] != $_SESSION['csrf'])) {
			startBox("Error");
			echo '<p class="error">Session information was lost.</p>';
			endBox();
			return;
		}

		// extract id list
		$ids = '';
		foreach ($_POST as $key => $value) {
			if (substr($key, 0, 3) == 'id_') {
				$ids .= "'".mysql_real_escape_string(substr($key, 3)). "', ";
			}
		}
		if (strlen($ids) > 4) {
			$ids = substr($ids, 0, -2);
		}

		// execute it
		if ($this->filter=="from-me") {
			StoredMessage::deleteSentMessages($_SESSION['account']->id, $ids);
		} else {
			StoredMessage::deleteReceivedMessages($_SESSION['account']->id, $ids);
		}
	}

	function printStoredMessages() {
		$playerId = $_SESSION['account']->id;
		$messages = StoredMessage::getStoredMessages($playerId, $this->filterWhere);

		startBox('Messages');
		if ($this->filter=="to-me") {
			$which =  ' to  ';
		} else if ($this->filter=="to-me-npcs") {
			$which = ' from NPCs to '; 
		}else if ($this->filter=="from-me") {
			$which = ' from '; 
		}
		echo '<p>This is a list of the recent messages'.$which.'your characters.';

		echo '<form name="messages" id="messages" method="POST" action="'.rewriteURL('/account/messages/'.surlencode($this->filter).'.html').'">';
		echo '<input type="hidden" name="csrf" value="'.htmlspecialchars($_SESSION['csrf']).'">';
		echo '<table class="prettytable"><tr><th>mark</th><th>from</th><th>to</th><th>server time</th><th>message</th></tr>'."\n";
		foreach ($messages as $entry) {
			if ($this->filter!="from-me" && $entry->delivered == 0) {
			echo '<tr style="font-weight:bold;">';
			} else {
				echo '<tr>';
			}
			echo '<td><input type="checkbox" name="id_'.htmlspecialchars($entry->id).'"></td>';
			if ($entry->messageType == 'P') {
				echo '<td><a href="'.rewriteURL('/character/'.surlencode($entry->source).'.html').'">'.htmlspecialchars($entry->source).'</a>';
			} else if ($entry->messageType == 'S') {
				echo '<td><span style="color:orange">Support (<a href="'.rewriteURL('/character/'.surlencode($entry->source).'.html').'">'.htmlspecialchars($entry->source).'</a>)</span>';
			} else if ($entry->messageType == 'N') {
				echo '<td><a href="'.rewriteURL('/npc/'.surlencode($entry->source).'.html').'">'.htmlspecialchars($entry->source).'</a>';
			} else {
				echo '<td>'.htmlspecialchars($entry->source);
			}
			echo '</td><td>'.htmlspecialchars($entry->target);
			if ($entry->timedate == '2010-07-20 00:00:00') {
				echo '</td><td>unknown';
			} else {
				echo '</td><td>'.htmlspecialchars($entry->timedate);
			}
			echo '</td><td>'.htmlspecialchars($entry->message)
					.'</td></tr>'."\n";

		}
		echo '</table>';
		echo '<input type="submit" value="Delete marked messages">';
		echo '</form>';
		endBox(); 

	}
	
}
$page = new MessagesPage();
?>
