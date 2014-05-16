<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008  Miguel Angel Blanch Lardin

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

require_once 'configuration.php';

function createImageData($url) {
	$size = getimagesize($url);
	$loc = 0;
	$w = $size[0];
	$h = $size[1];

	if (strpos($url, "/ent/") !== false) {
		// Ent images are tiles of 1x2 so we choose a single tile.
		$w=$w;
		$h=$h/2;
		$loc=0;
	} else if (strpos($url, "/alternative/") === false) {
		// Images are tiles of 3x4 so we choose a single tile.
		$w=$w/3;
		$h=$h/4;
		$loc=$h*2;
	}

	$result=imagecreate($w,$h);
	$white=imagecolorallocate($result,255,255,255);
	imagefilledrectangle($result, 0,0,$w,$h,$white);

	$baseIm=imagecreatefrompng($url);
	imagecopy($result,$baseIm,0,0,0,$loc,$w,$h);
	return $result;
}


$url = $_GET['url'];
if ((strpos($url, '..') !== false) || (strpos($url, 'data/sprites/') !== 0) || (strpos($url, '.') < strlen($url) - 4)) {
	header('HTTP/1.1 404 Not Found', true, 404);
	die("Access denied.");
}

$etag = STENDHAL_VERSION.'-'.sha1($url);
if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
	$requestedEtag = $_SERVER['HTTP_IF_NONE_MATCH'];
}

header("Content-type: image/png");
header("Cache-Control: max-age=3888000, public"); // 45 * 24 * 60 * 60
header('Pragma: cache');
header('Etag: "'.$etag.'"');

if (isset($requestedEtag) && (($requestedEtag == $etag) || ($requestedEtag == '"'.$etag.'"'))) {
	header('HTTP/1.0 304 Not modified');
} else {
	imagepng(createImageData($url));
}
