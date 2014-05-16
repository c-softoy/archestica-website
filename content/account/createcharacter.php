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
class CreateCharacterPage extends Page {
	private $outfitArray;
	private $result;

	/**
	 * this method can write additional http headers, for example for cache control.
	 *
	 * @return true, to continue the rendering, false to not render the normal content
	 */
	public function writeHttpHeader() {
		global $protocol;
		if (!isset($_SESSION['account'])) {
			// Show link to login box
			return true;
		}

		$res = $this->process();
		if ($res) {
			// redirect to my characters page
			header('HTTP/1.0 301 Moved permanently.');
			header("Location: ".$protocol."://".$_SERVER['HTTP_HOST'].preg_replace("/&amp;/", "&", rewriteURL('/account/mycharacters.html')));
			return false;
		}
		return true;
	}


	public function writeHtmlHeader() {
		echo '<title>Create Character'.STENDHAL_TITLE.'</title>';
		echo '<meta name="robots" content="noindex">'."\n";
		echo '<style type="text/css">
				.outfitpanel {border: 1px solid black; width: 8.5em; height: 256px; padding: 0em; float: left; margin-right: 2em; margin-bottom: 2em}
				.prev, .next {float: left; margin-top: 2em}
				.outfitpart {float: left; display: block; width:48px; height: 64px; background-position: 0 128px;}
			</style>';
	}

	function writeContent() {
		if (!isset($_SESSION['account'])) {
			startBox("Create Character");
			echo '<p>Please <a href="'.STENDHAL_LOGIN_TARGET.'/index.php?id=content/account/login&amp;url=/account/create-character.html">login</a> to create a character.</p>';
			endBox();
		} else {
			$this->show(rewriteURL('/account/create-character.html'));
		}
	}

	function process() {
		global $protocol;
		if (! isset($_POST['name']) || !isset($_REQUEST['outfitcode'])) {
			return false;
		}

		if ($_POST['csrf'] != $_SESSION['csrf']) {
			return false;
		}

		$user = strtolower($_POST['name']);
		require_once('scripts/pharauroa/pharauroa.php');
		$clientFramework = new PharauroaClientFramework(STENDHAL_MARAUROA_SERVER, STENDHAL_MARAUROA_PORT, STENDHAL_MARAUROA_CREDENTIALS);
		$template = new PharauroaRPObject();
		$template->put('outfit', $_REQUEST['outfitcode']);

		$this->result = $clientFramework->createCharacter($_SESSION['account']->username, $user, $template);

		if ($this->result->wasSuccessful()) {
			return true;
		} else {
			return false;
		}
	}
	

	function show($createURL) {
		$this->initOutfitArray();

		if (isset($_POST['name']) && ($_POST['csrf'] != $_SESSION['csrf'])) {
			startBox("Error");
			echo '<p class="error">Session information was lost.</p>';
			endBox();
		}

		if (isset($this->result) && !$this->result->wasSuccessful()) {
			startBox("Error");
			echo '<span class="error">'.htmlspecialchars($this->result->getMessage()).'</span>';
			endBox();
		}

		startBox("Create Character");
?>

<form id="createCharacterForm" name="createCharacterForm" action="<?php echo $createURL;?>" method="POST" style="height:22em; padding: 1em"> <!-- onsubmit="return checkForm()"  -->
<input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'])?>">

<div class="outfitpanel">
<div style="clear: both">
<input class="prev" type="button" data-offset="0" value="&lt;">
<span id="outfit0" class="outfitpart"></span>
<input class="next" type="button" data-offset="0" value="&gt;">
</div>

<div style="clear: both">
<input class="prev" type="button" data-offset="1" value="&lt;">
<span id="outfit1" class="outfitpart"></span>
<input class="next" type="button" data-offset="1" value="&gt;">
</div>


<div style="clear: both">
<input class="prev" type="button" data-offset="2" value="&lt;">
<span id="outfit2" class="outfitpart"></span>
<input class="next" type="button" data-offset="2" value="&gt;">
</div>


<div style="clear: both">
<input class="prev" type="button" data-offset="3" value="&lt;">
<span id="outfit3" class="outfitpart"></span>
<input class="next" type="button" data-offset="3" value="&gt;">
</div>

</div>

<div style="float:left; width: 50%">
<span id="canvas" style="border: 1px solid #AAA; display: block; width: 48px; height: 64px"></span>
<input class="turn" type="button" data-offset="-1" value="l">
<input class="turn" type="button" data-offset="1" value="r">
</div>


<div style="float:left; width: 50%; padding-top: 2em">
<input id="outfitcode" name="outfitcode" type="hidden" value="01010101">
<label for="name" >Name: </label><input id="name" name="name" type="text" maxlength="20" 
<?php 
	if (isset($_REQUEST['name'])) {
		echo 'value="'.htmlspecialchars($_REQUEST['name']).'"';
	} else {
		$username = Account::convertToValidUsername($_SESSION['account']->username);
		// don't suggest charnames based on the long ugly openid identifiers of google and yahoo
		if (strlen($username) < 20 && !doesCharacterExist($username)) {
			echo 'value="'.htmlspecialchars($username).'"';
		}
	}
?>>
<div id="warn" class="warn">&nbsp;</div>
<input name="submit" style="margin-top: 2em" type="submit" value="Create Character">
<input id="currentOutfit" name="currentOutfit" type="hidden" value = "<?php echo $this->outfitArray[0].','.$this->outfitArray[1].','.$this->outfitArray[2].','.$this->outfitArray[3];?>">
<input id="sessionUsername" type="hidden" value="<?php echo htmlspecialchars($_SESSION['account']->username);?>">
<input id="serverpath" name="serverpath" type="hidden" value="<?php echo STENDHAL_FOLDER;?>">
</div>
</form>
<?php
		endBox();
		$this->includeJs();
	}

	
	function initOutfitArray() {
		if (isset($_REQUEST['outfitcode'])) {
			$code = intval($_REQUEST['outfitcode'], 10);
			$this->outfitArray[2] = $code % 100;
			$this->outfitArray[3] = $code / 100 % 100;
			$this->outfitArray[1] = $code / 10000 % 100;
			$this->outfitArray[0] = $code / 1000000 % 100;
		} else {
			$this->randomOutfit();
		}
	}

	function randomOutfit() {
		$maxRndOutfit = array(26, 15, 5, 15);
		for ($i = 0; $i < 4; $i++) {
			$this->outfitArray[$i] = rand(1, $maxRndOutfit[$i]);
		}
	}

}

$page = new CreateCharacterPage();

?>