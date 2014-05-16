<?php
class AdminItemlogPage extends Page {
	public function writeHtmlHeader() {
		?>
		<style type="text/css">
			#container {width:99%;}
			.important {font-weight: bold}
			.highlight {background-color: #FAA}
			.hide {display: none}
		</style>
		<?php
	}

	public function writeContent() {

		if(getAdminLevel() < 5000) {
			die("Ooops!");
		}
		$itemid=$_REQUEST["itemid"];
		$highlightTimestamp = $_REQUEST['timestamp'];
		startBox('Search itemlog');
?>
	<form method="get" action="" accept-charset="iso-8859-1">
		<input type="hidden" name="id" value="content/admin/itemlog">
		<label for="itemid">itemid: </label><input type="text" id="itemid" name="itemid" value="<?php if (isset($itemid)) {echo htmlspecialchars($itemid);}?>">
		<label for="timestamp">timestamp: </label><input type="text" id="timestamp" name="timestamp" value="<?php if (isset($highlightTimestamp)) {echo htmlspecialchars($highlightTimestamp);}?>">
		<input type="submit" name="sublogin" value="Search">
	</form>
<?php
		endBox(); 

		if (isset($itemid)) {
			startBox('Item History');

			echo '<p>History for item '.htmlspecialchars($itemid).'</a>.</p>';
			echo '<table class="prettytable"><tr><th>itemid</th><th>time</th><th>source</th><th>event</th><th colspan="4">parameters</th></tr>';

			$history = ItemLog::getLogEntriesForItem($itemid);
			foreach ($history as $entry) {
				$class = '';
				if (isset($highlightTimestamp) && $entry->timedate < $highlightTimestamp) {
					$class = 'hide ';
				}
				if ($entry->event == 'register') {
					$class = 'important ';
				}
				echo '<tr class="'.$class.'">';
				echo '<td>'.$this->itemLink($entry->itemid).'</td>';
				echo '<td>'.$this->timestampLink($entry->timedate).'</td>';
				echo '<td>'.htmlspecialchars($entry->source).'</td>';
				echo '<td>'.htmlspecialchars($entry->event).'</td>';

				if ($entry->param1 != '-1' && ($entry->event == 'merge in' || $entry->event == 'merged in' || $entry->event == 'split out' || $entry->event == 'splitted out')) {
					echo '<td>'.$this->itemLink($entry->param1).'</td>';
				} else {
					echo '<td>'.htmlspecialchars($entry->param1).'</td>';
				}
				echo '<td>'.htmlspecialchars($entry->param2).'</td>';
				echo '<td>'.htmlspecialchars($entry->param3).'</td>';
				echo '<td>'.htmlspecialchars($entry->param4).'</td></tr>';
			}
			echo '</table>';
			echo '<p><b>These logs are for administrators eyes ONLY and should not be copied or pasted to others.</b></p>';

			endBox(); 
		}
	}

	private function timestampLink($timestamp) {
		return '<a href="/?id=content/admin/logs&amp;date='.urlencode(substr($timestamp, 0, 10)).'">'.htmlspecialchars($timestamp).'</a>';
	}

	private function itemLink($itemid) {
		return '<a href="/?id=content/admin/itemlog&itemid='.urlencode($itemid).'">'.htmlspecialchars($itemid).'</a>';
	}
}
$page = new AdminItemlogPage();
?>