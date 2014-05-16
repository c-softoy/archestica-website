<?php
class NetstatsPage extends Page {

	public function writeHtmlHeader() {
		echo '<title>Netstats'.STENDHAL_TITLE.'</title>';
		$this->includeJs();
	}

	function writeContent() {
		startBox('Trace route');
		echo '<div id="traceresult1">Tracing route, please wait...';
		echo '<table class="progressTable"><tr><td id="progress1" class="progress">&nbsp;</td><td class="pendingprogress">&nbsp;</td></tr></table>';
		echo' </div>';
		endBox();

		echo '<div id="tracebox2" style="display:none">';
		startBox('Details');
		echo '<div id="traceresult2">Gathering details about every hop on the route, please wait...';
		echo '<table class="progressTable"><tr><td id="progress2" class="progress">&nbsp;</td><td class="pendingprogress">&nbsp;</td></tr></table>';
		echo' </div>';
		endBox();
		echo' </div>';

		$ip = '';
		if (isset($_REQUEST['ip'])) {
			$ip = '&ip='.urlencode($_REQUEST['ip']);
		}
		echo '<span id="traceip" style="display: none">'.htmlspecialchars($ip).'</span>';
	}
}
$page = new NetstatsPage();