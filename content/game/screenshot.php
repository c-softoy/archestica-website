<?php 

class ScreenshotPage extends Page {
	private $screenshots;
	private $idx;
	private $filename;
	private $title;

	function __construct() {
		$sql = "SELECT page_title As filename, cl_sortkey_prefix As subtitle FROM categorylinks, page WHERE cl_to='Stendhal_Slideshow' AND cl_type='file' AND page_id=cl_from ORDER BY page_touched DESC, page_id DESC";
		$this->screenshots = fetchToArray($sql, getWikiDB());
		$this->idx = 0;
		if (isset($_REQUEST['file'])) {
			$temp = $_REQUEST['file'];
			for ($i = 0; $i < count($this->screenshots); $i++) {
				if ($this->screenshots[$i]['filename'] == $temp) {
					$this->idx = $i;
					break;
				}
			}
		}
		$this->filename = $this->screenshots[$this->idx]['filename'];
		$this->title = $this->screenshots[$this->idx]['subtitle'];
	}

	public function writeHttpHeader() {
		echo '<!DOCTYPE html><html><head>';
		echo '<title>Screenshot '.$this->filename.STENDHAL_TITLE.'</title>';
		echo '<meta name="robots" content="noindex">';
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
		echo '</head><body>';
		echo '<a href="'.rewriteURL('/images/screenshot/'.urlencode($this->screenshots[($this->idx + 1) % count($this->screenshots)]['filename'])).'">';
		echo '<img src="'.$this->getImageUrl($this->filename).'" alt="'.htmlspecialchars($this->title).'">';
		echo '</a>';
		echo '<p>'.htmlspecialchars($this->title).'</p>';
		echo '</body></html>';
		return false;
	}

	function getImageUrl($filename) {
		$hash = md5($filename);
		return '/wiki/images/' . $hash{0} . '/' . substr( $hash, 0, 2 ) . '/' . urlencode($filename);
	}
}
$page = new ScreenshotPage();
?>