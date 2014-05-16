<?php

class CacheInfoPage extends Page {
	function writeContent() {

		if(getAdminLevel() < 5000) {
			die("Ooops!");
		}

		if (isset($_REQUEST['clear'])) {
			global $cache;
			$cache->clear();
			startBox('Cache');
			echo 'Cache was cleared.';
			endBox();
		} else {
			$this->dumpDetails();
		}
	}

	function dumpDetails() {
		startBox('User');
		echo '<pre>';
		var_dump(apc_cache_info('user'));
		echo '</pre>';
		endBox();

		startBox('Filehits');
		echo '<pre>';
		var_dump(apc_cache_info('filehits'));
		echo '</pre>';
		endBox();

		startBox('default');
		echo '<pre>';
		var_dump(apc_cache_info());
		echo '</pre>';
		endBox();
	}
}

$page = new CacheInfoPage();
?>