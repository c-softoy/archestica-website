<?php
class AtlasPage extends Page {
	public function writeHtmlHeader() {
		echo '<title>Atlas'.STENDHAL_TITLE.'</title>';
		$this->includeJs();
	}

	function writeContent() {
		echo '<div id="map_canvas" data-tile-url-base="'.STENDHAL_MAP_TILE_URL_BASE.'" style="width: 570px; height: 380px;"></div><p>&nbsp;</p>';
		startBox('Extended information');
		echo '<p>You can use your mouse or the map controls to zoom and pan.</p>';
		echo '<p>There is lots more information about each of the <a href="https://stendhalgame.org/wiki/Semos">regions</a> and <a href="https://stendhalgame.org/wiki/Semos_Dungeons">dungeons</a> on the Stendhal Wiki.</p>';
		echo '<p>You can add <a href="https://archestica.net?id=content/world/atlas&poi=dungeon">dungeon entrances</a> to this map or open a map with <a href="http://archestica.net/world_labelled.png">zone names</a>.</p>';
		endBox();

		$zoom = 3;
		$focusX = 500000;
		$focusY = 500000;

		$zones = Zone::getZones();

		// if there is exactly one poi, focus on that
		$poiOpen = false;
		if (isset($_REQUEST['poi']) && strpos($_REQUEST['poi'], '.') === false) {
			$pois = PointofInterest::getPOIs();
			if (isset($pois[$_REQUEST['poi']])) {
				$poi = $pois[$_REQUEST['poi']];
				$zoom = 5;
				$focusX = $poi->gx;
				$focusY = $poi->gy;
				$poiOpen = true;
			}
		}

		// focus on position of current player and display a marker
		if (isset($_REQUEST['me'])) {
			$coordinates = explode('.', $_REQUEST['me']);
			$zone = $zones[$coordinates[0]];
			if (isset($zone) && isset($zone->x)) {
				$meZone = $coordinates[0];
				$meX = $zone->x + intval($coordinates[1]);
				$meY = $zone->y + intval($coordinates[2]);
				if ($zone->z === 0) {
					$zoom = 5;
				} else {
					$zoom = 4;
				}
				$focusX = $meX;
				$focusY = $meY;
			}
		}

		// if there is a focus parameter, use it
		if (isset($_REQUEST['focus'])) {
			$zoom = 5;
			$coordinates = explode('.', $_REQUEST['focus']);
			if (count($coordinates) === 1) {
				$pois = PointofInterest::getPOIs();
				$poi = $pois[$coordinates[0]];
				if (isset($poi)) {
					$focusX = $poi->gx;
					$focusY = $poi->gy;
				}
			} else if (count($coordinates) === 2) {
				$focusX = $coordinates[0];
				$focusY = $coordinates[0];
			} else if (count($coordinates) === 3) {
				$zone = $zones[$coordinates[0]];
				if (isset($zone) && isset($zone->x)) {
					$focusX = $zone->x + intval($coordinates[1]);
					$focusY = $zone->y + intval($coordinates[2]);
				}
			}
		}

		if (isset($_REQUEST['zoom'])) {
			$zoom = intval($_REQUEST['zoom']);
		}

		echo "\n".'<div class="data">';
		echo '<span id="data-center" data-x="'.htmlspecialchars($focusX).'" data-y="'.htmlspecialchars($focusY).'" data-zoom="'.htmlspecialchars($zoom).'" data-open="'.$poiOpen.'"></span>';
		if (isset($meX)) {
			echo '<span id="data-me" data-x="'.htmlspecialchars($meX).'" data-y="'.htmlspecialchars($meY).'" ';
			echo 'data-zone="'.htmlspecialchars($meZone).'" ';
			echo 'data-local-x="'.htmlspecialchars($coordinates[1]).'" data-local-y="'.htmlspecialchars($coordinates[2]).'"></span>';
		}
		echo '<span id="data-pois" data-pois="'.htmlspecialchars(json_encode(PointofInterest::getPOIs())).'"></span>';
		echo '</div>';
	}
	
	public function writeAfterJS() {
		echo '<link rel="stylesheet" href="/css/leaflet.css">';
		echo '<!--[if lte IE 8]><link rel="stylesheet" href="/css/leaflet.ie.css" /><![endif]-->';
	}
}
$page = new AtlasPage();
