<?php 

class DownloadPage extends Page {
	public function writeHtmlHeader() {
		echo '<title>Downloads'.STENDHAL_TITLE.'</title>';
		echo '<meta name="robots" content="noindex">'."\n";
	}
	
	function writeContent() {
		startBox("Download");
		?>
		<p><b>These are the most recent development snapshots.</b> 
		They are good for testing.</p>

		<p>If you are interested in the last stable release, you can download it at
		<a href="http://sourceforge.net/projects/arianne/files/">http://sourceforge.net/projects/arianne/files/</a>.</p>

		<p>Please help us test the things mentioned on <a href="http://stendhalgame.org/wiki/Stendhal_Testing">Stendhal Testing</a>.</p>

		<div style="margin-left: 3em">
		<?php
		$dir = opendir('download');
		while (false !== ($file = readdir($dir))) {
			if (strpos($file, '.') !== 0) {
				echo '<li><a href="/download/'.htmlspecialchars($file).'">'.htmlspecialchars($file).'</a></li>';
			}
		}
		closedir($dir);
		?>
		</div>
		<?php
		endBox();
	}
}

$page = new DownloadPage();
?>