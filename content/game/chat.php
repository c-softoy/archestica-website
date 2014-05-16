<?php 

class ChatPage extends Page {
	public function writeHtmlHeader() {
		echo '<title>Chat'.STENDHAL_TITLE.'</title>';
		echo '<meta name="robots" content="noindex">'."\n";
		$this->includeJs();
	}
	
	function writeContent() {
startBox("Chat to other users and developers"); ?>
You can get an IRC client and connect to:

<p><b>irc.freenode.net</b> (see <a href="http://freenode.net">http://freenode.net</a> for more information) then</p>

<p>Alternatively, you can simply use freenode's webchat service, below. Feel free to change the nick to for example, your player name.</p>
<ul> 
<li><a href="https://webchat.freenode.net/?channels=arianne">#arianne</a> (for ideas, contributions and support)</li>
<li><a href="https://webchat.freenode.net/?channels=arianne-chat">#arianne-chat</a> (for off topic chat not related to Arianne/Stendhal)</li>
</ul>
If you are new to IRC it is well worth reading this <a href="http://www.irchelp.org/irchelp/new2irc.html">short guide</a> before you join. In particular the section on talking, and entering commands, and the section 'Some advice' may be helpful.
<?php endBox(); ?>
<?php
	startBox(MAIN_CHANNEL . ' IRC log');
	echo '<a name="log" id="log"></a>';
	$directory = MAIN_LOG_DIRECTORY;

	if (isset($_GET['date']) && preg_match("/^\d\d\d\d-\d\d-\d\d$/", $_GET['date'])) {
	$date = $_GET['date'];
?>
	<p><a href="<?php echo rewriteURL("/chat/");?>">Index of logs</a></p>

	<h2><?php echo(MAIN_CHANNEL); ?> IRC Log for <?php echo($date); ?></h2>
	<p>Timestamps are in server time. <span id="irclog-toggle-ircstatus-span" style="display:none"><input id="irclog-toggle-ircstatus" type="checkbox" value=""><label for="irclog-toggle-ircstatus">Show join/quit messages</label></span></p>

<p>

<?php
$filename = $directory.$date . ".log";
if (!file_exists($filename)) {
	$filename = $directory.substr($date, 0, 4).'/'.$date.'.log';
}
$lines = explode("\n", file_get_contents($filename));
echo '<table style="table-layout:fixed; word-wrap:break-word; width:100%">';
echo '<tr><th style="width: 3em"></th><th style="width: 5.5em"></th><th></th></tr>';
for ($i = 0; $i < count($lines); $i++) {
	$line = $lines[$i];
	
	## make it pretty, yes this code is ugly.
	$class = "irctext";
	if (substr($line, 5, 5) == ' -!- ') {
		$class = "ircstatus";
	} else {
		if (substr($line, 5, 16) == ' < postman-bot> ') {
			if (substr($line, 21, 22) == 'Administrator SHOUTS: ') {
				$class = "ircshout";
			} else if (strpos($line, 'rented a sign saying') > 10) {
				$class = "ircsign";
			}
		}
	}
	preg_match('/(..:..) *(<.([^>]*)>|\*|-!-) (.*)/', $line, $matches);
	if (count($matches) >= 4) {
		$time = $matches[1];
		$nick = $matches[2];
		if ($matches[3] != '') {
			$nick = $matches[3];
		}
		$line = $matches[4];
	
		$line = htmlspecialchars($line);
		$line = preg_replace('/@/', '&lt;(a)&gt;', $line);
		$line = preg_replace('!(http|https)://(faiumoni.de|stendhalgame.org|arianne.sf.net|arianne.sourceforge.net|sourceforge.net|sf.net|download.oracle.com|libregamewiki.org)(/[^ ]*)?!', '<a href="$1://$2$3">$1://$2$3</a>', $line);

		if ($line != '') {
			echo '<tr class="'.$class.'"><td>'
				.htmlspecialchars($time).'</td><td>'
				.htmlspecialchars($nick).'</td><td>'
				.$line.'</td></tr>'."\n";
		}
	}
}
echo '</table>';

?>
</p>
<?php
} else {

function renderYear($year, $startMonth, $startDay, $endMonth, $endDay) {
	echo '<h2>'.htmlspecialchars($year).'</h2>';
	echo '<table>';
	for ($month = $endMonth; $month >= $startMonth; $month--) {
		$time = mktime(0, 0, 0, $month, 1, $year);
		echo '<tr><td>'.date('F', $time).'</td><td>';
		$myMonth = $month;
		if ($month < 10) {
			$myMonth = '0'.$month;
		}
		$myStartDay = 1;
		if ($month == $startMonth) {
			$myStartDay = $startDay;
		}
		$myEndDay = date('t', $time);
		if ($month == $endMonth) {
			$myEndDay = $endDay;
		}
		for ($day = $myStartDay; $day <= $myEndDay; $day++) {
			$myDay = $day;
			if ($day < 10) {
				$myDay = '0'.$day;
			}
			echo '&nbsp;<a href="'.rewriteURL('/chat/'.$year.'-'.$myMonth.'-'.$myDay.'.html').'">'.$myDay.'</a>&nbsp;';
			if ($day == 15) {
				echo '<br>';
			}
		}
		echo '</td></tr>';
	}
	echo '</table>';
}


	$startYear = 2006;
	$endYear = date('Y');
	for ($year = $endYear; $year >= $startYear; $year--) {
		$startMonth = 1;
		$startDay = 1;
		if ($year == $startYear) {
			$startMonth = 9;
			$startDay = 1;
		}
		$endMonth = 12;
		$endDay = 31;
		if ($year == $endYear) {
			$endMonth = date('n');
			$endDay = date('j');
		}
		renderYear($year, $startMonth, $startDay, $endMonth, $endDay);
	}
}

?>

<p>
These logs of  <?php echo(MAIN_CHANNEL); ?> were automatically created on
<a href="irc://<?php echo(MAIN_SERVER . "/" . substr(MAIN_CHANNEL, 1)); ?>"><?php echo(MAIN_SERVER); ?></a>
</p>
<?php
		endBox();
	}
}
$page = new ChatPage();
?>