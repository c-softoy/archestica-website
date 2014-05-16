<?php
/**
 * An interface to handle systematic literature review data. Not really related to Stendhal.
 */
require_once('scripts/slr.php');
class SystematicLiteratureReviewPage extends Page {
	private static $READONLY_ATTRIBUTES = array('id', 'reviewer', 'timedate');
	private static $usernames = array('hendrikus', 'metzgermeister');
	private static $reviewers = array('hendrik', 'markus');
	private $edited;
	private $columns;

	public function writeHttpHeader() {
		if(getAdminLevel()<1000) {
			die("Ooops!");
		}
		
		if (isset($_REQUEST['format']) && ($_REQUEST['format'] == 'csv')) {
			header('Content-Type: text/csv');
			$this->csvExport();
			return false;
		} else {
			return true;
		}
	}

	public function writeHtmlHeader() {
		echo '<title>Systematic Literature Review'.STENDHAL_TITLE.'</title>';
	}
	function writeContent() {
		if(getAdminLevel()<1000) {
			die("Ooops!");
		}
		$this->renderWebsite();
	}

	function renderWebsite() {

$metadata = getSlrMetadata();

if(isset($_POST['action'])) {
  if($_REQUEST['action']=='submit') {
    startBox("Adding slr item");
    if (!isset($_REQUEST['paper_bibkey']) || trim($_REQUEST['paper_bibkey']) == '') {
    	die('Sorry you forgot the bibkey, all your data is lost.');
    }
  	$reviewer = str_replace(SystematicLiteratureReviewPage::$usernames, SystematicLiteratureReviewPage::$reviewers, $_SESSION['account']->username);
  	$_REQUEST['reviewer'] = $reviewer;
    $slrid = addSlr($metadata, $_REQUEST);
    if (isset($slrid) && $slrid > 0) {
    	echo 'Insert was successful.';
    }
    endBox();
  }
}

if ((isset($_REQUEST['action'])) && ($_REQUEST['action']=='edit' || $_REQUEST['action']=='submit')) {  
  $id = mysql_real_escape_string($_REQUEST['edit']);
  if (isset($slrid)) {
  	$id = $slrid;
  }
  $slrtoEdit=getSlr($id);
  if(sizeof($slrtoEdit)==0) {
    startBox("Edit slr item");
      echo '<div class="error">No such slr item</div';
    endBox();
  } else {
    $this->edited=$slrtoEdit[0];
  } 
}

	$this->columns = 2;
	if (isset($_REQUEST['columns'])) {
		$this->columns = intval($_REQUEST['columns']);
	}


  /*
   * Show all the previous slr items, just header
   */ 
  $reviewer = str_replace(SystematicLiteratureReviewPage::$usernames, SystematicLiteratureReviewPage::$reviewers, $_SESSION['account']->username);
  $slr=getAllSlr();
  startBox("Manage existing slr entries");
  echo '<ul>';
  foreach($slr as $item) {
    ?>
    <li>
    <?php if ($item['paper_exclude'] && trim($item['paper_exclude']) != '') { echo '<del>';}?>
    <span class="date"><a href="<?php echo STENDHAL_FOLDER;?>/?id=content/admin/slr&amp;action=edit&amp;edit=<?php echo $item['id']; ?>&amp;columns=<?php echo $this->columns?>#editform"><?php echo $item['paper_bibkey']; ?></a></span>
    <span><?php echo $item['paper_title']; ?></span>
    <span><i><?php echo $item['reviewer']; ?></i></span>
    <span><i><?php echo date(date('Y.m.d', $item['timdedate'])); ?></i></span>
    <?php if ($item['paper_exclude'] && trim($item['paper_exclude']) != '') { echo '</del>: '.htmlspecialchars($item['paper_exclude']);}?>
    </li>
    <?php
    }
  ?>
  </ul> 
  <?php
  endBox();  
?>

<a name="editform"></a>
<?php
startBox((isset($this->edited)?'Edit':'Submit').' slr item');

?>
<form class="slr" method="post" action="<?php echo STENDHAL_FOLDER;?>/?id=content/admin/slr#editform" name="submitslr">
	<input type="hidden" name="action" value="submit"/>
	<input type="hidden" name="columns" value="<?php echo htmlspecialchars($this->columns);?>"/>

	<table width="100%">
	<tbody style="vertical-align: top">
		<tr><td><input type="submit" value="Submit"></td></tr>
		<?php
		for ($i = 0; $i < count($metadata); $i++) {
			echo '<tr>';
			for ($j = 0; $j < $this->columns; $j++) {
				if ($i + $j >= count($metadata)) {
					break;
				}
				$this->writeInputHeader($metadata[$i+$j]);
			}
			echo '</tr><tr>';
			for ($j = 0; $j < $this->columns; $j++) {
				if ($i + $j >= count($metadata)) {
					break;
				}
				$this->writeInputBody($metadata[$i+$j]);
			}
			echo '</tr>';
			$i = $i + $j - 1;
		}
		?>


		<tr><td><input type="submit" value="Submit"></td></tr>
	</tbody>
	</table>
	<br>
</form>
<?php
		endBox();
		startBox("Caption");
		echo 'Y: details           A: alludes without details        N: not mentioned        (X: want discuss)';
		endBox();
	}

	function writeInputHeader($meta) {
		echo '<td><b>'.htmlspecialchars(str_replace('_', ' ', $meta['column_name'])).'</b> ';
		if (isset($meta['column_comment']) && trim($meta['column_comment']) != '') {
			echo '('.htmlspecialchars($meta['column_comment']).')';
		}
		echo '</td>';
	}

	function writeInputBody($meta) {
		echo '<td>';
		if (in_array($meta['column_name'], SystematicLiteratureReviewPage::$READONLY_ATTRIBUTES)) {
			echo htmlspecialchars($this->edited[$meta['column_name']]);
		} else if ($meta['column_type'] == "text") {
			echo '<textarea rows="10" style="width:99%" name="'.htmlspecialchars($meta['column_name']).'">';
			if (isset($this->edited[$meta['column_name']])) {
				echo htmlspecialchars($this->edited[$meta['column_name']]);
			}
			echo '</textarea>';
		} else {
			echo '<input name="'.htmlspecialchars($meta['column_name']).'" style="width:99%" ';
			if (isset($this->edited[$meta['column_name']])) {
				echo 'value="'.htmlspecialchars($this->edited[$meta['column_name']]).'"';
			}
			echo '>';
		}
		echo '</td>';
	}

	function csvExport() {
		$slr = getAllSlr(null);
		$metadata = getSlrMetadata();

		$first = true;
		foreach ($metadata as $meta) {
			if ($first) {
				$first = false;
			} else {
				echo '; ';
			}
			echo $this->escapeCsv(str_replace('_', ' ', $meta['column_name']));
		}
		echo "\n";

		$first = true;
		foreach ($metadata as $meta) {
			if ($first) {
				$first = false;
			} else {
				echo '; ';
			}
			echo $this->escapeCsv($meta['column_comment']);
		}
		echo "\n";
		echo "\n";

		foreach ($slr as $row) {
			$first = true;
			foreach ($row as $cell) {
				if ($first) {
					$first = false;
				} else {
					echo '; ';
				}
				echo $this->escapeCsv($cell);
			}
			echo "\n";
		}
	}

	function escapeCsv($cell) {
		return '"'.str_replace(array('"', '\\', ';'), array('\'', '\\\\', ',.'), $cell).'"';
	}
}
$page = new SystematicLiteratureReviewPage();
?>