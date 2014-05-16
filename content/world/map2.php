<?php
class MapPage extends Page {

	public function writeHtmlHeader() {
		echo '<title>Map'.STENDHAL_TITLE.'</title>';
		?>
		<style type="text/css">
			#canvas {border: 1px solid black}
			#container {width:99%}
		</style>
		<script type="text/javascript" src="<?php echo STENDHAL_FOLDER; ?>/css/jsxgraph-util.js"></script>
		<!--[if IE]><script type="text/javascript" src="<?php echo STENDHAL_FOLDER; ?>/css/excanvas.compiled.js"></script><![endif]-->
		<?php
	}

	function getBodyTagAttributes() {
		return 'id="body" onload="load()"';
	}

	function writeContent() {
		startBox("Map");
?>
<form name="mapform" onsubmit="return refreshButton()">
<label for="mapname">Map name: </label><input name="mapname" id="mapname" style="width:16em">
<label for="zoom">Zoom: </label><input name="zoom" id="zoom" value="100" maxlength="3" style="width:3em">
<input type="submit" value="Refresh"><br><br>
<table border="0">
<tr>
<td><label for="zoom">protection: </label></td><td><input name="alpha_protection" id="alpha_protection" value="0" maxlength="3" style="width:3em"></td>
<td><label for="zoom">collision: </label></td><td><input name="alpha_collision" id="alpha_collision" value="0" maxlength="3" style="width:3em"></td>
<td><label for="zoom">objects: </label></td><td><input name="alpha_objects" id="alpha_objects" value="0" maxlength="3" style="width:3em"></td>
<td><label for="zoom">4_roof_add: </label></td><td><input name="alpha_4_roof_add" id="alpha_4_roof_add" value="100" maxlength="3" style="width:3em"></td>
</tr><tr>
<td><label for="zoom">3_roof: </label></td><td><input name="alpha_3_roof" id="alpha_3_roof" value="100" maxlength="3" style="width:3em"></td>
<td><label for="zoom">2_object: </label></td><td><input name="alpha_2_object" id="alpha_2_object" value="100" maxlength="3" style="width:3em"></td>
<td><label for="zoom">1_terrain: </label></td><td><input name="alpha_1_terrain" id="alpha_1_terrain" value="100" maxlength="3" style="width:3em"></td>
<td><label for="zoom">0_floor: </label></td><td><input name="alpha_0_floor" id="alpha_0_floor" value="100" maxlength="3" style="width:3em"></td>
</tr>
</table><br>
<div>Status: <span id="status">Loading...</span><span id="debug"> </span></div>
</form>
		<?php endBox(); ?>

<canvas id="canvas" width="300" height="300">Sorry, this pages only works in modern web browsers.</canvas>

<script type="text/javascript">
	var lastMap = ""

	var offsetX = 0;
	var offsetY = 0;
	var sizeX = 23;
	var sizeY = 17

	var tileWidth = 32;
	var tileHeight = 32;
	var zoom = 100;

	var aImages;
	var layerNames;
	var layers;
	var firstgids;
	var numberOfXTiles;
	var numberOfYTiles;


	// Start http://www.webreference.com/programming/javascript/gr/column3/ 
	function ImagePreloader(images, callback) {
		// store the call-back
		this.callback = callback;

		// initialize internal state.
		this.nLoaded = 0;
		this.nProcessed = 0;
		aImages = new Array;

		// record the number of images.
		this.nImages = images.length;

		// for each image, call preload()
		for ( var i = 0; i < images.length; i++)
			this.preload(images[i]);
	}

	ImagePreloader.prototype.preload = function(image) {
		// create new Image object and add to array
		var oImage = new Image;
		aImages.push(oImage);

		// set up event handlers for the Image object
		oImage.onload = ImagePreloader.prototype.onload;
		oImage.onerror = ImagePreloader.prototype.onerror;
		oImage.onabort = ImagePreloader.prototype.onabort;

		// assign pointer back to this.
		oImage.oImagePreloader = this;
		oImage.bLoaded = false;

		// assign the .src property of the Image object
		oImage.src = image;
	}

	ImagePreloader.prototype.onComplete = function() {
		this.nProcessed++;
		if (this.nProcessed == this.nImages) {
			this.callback();
		}
	}

	ImagePreloader.prototype.onload = function() {
		if (this.oImagePreloader.nLoaded % 10 == 0) {
			status("Downloading images... " + this.oImagePreloader.nLoaded);
		}
		this.bLoaded = true;
		this.oImagePreloader.nLoaded++;
		this.oImagePreloader.onComplete();
	}

	ImagePreloader.prototype.onerror = function() {
		this.bError = true;
		this.oImagePreloader.onComplete();
		document.getElementById("debug").innerHTML = document.getElementById("debug").innerHTML + "<p> Error: " + this.src;
	}

	ImagePreloader.prototype.onabort = function() {
		this.bAbort = true;
		this.oImagePreloader.onComplete();
		document.getElementById("debug").innerHTML = document.getElementById("debug").innerHTML + "<p> Abort: " + this.src;
	}

	// End http://www.webreference.com/programming/javascript/gr/column3/


	var drawingError = false;
	var drawingLayer = 0;
	var targetTileWidth = 0;
	var targetTileHeight = 0;

	var counter = 0;
	var starttime = new Date().getTime();
	var balloonY = 0;

	function draw() {
		counter++;
		var canvas = document.getElementById("canvas");
		canvas.style.display = "none";
		targetTileWidth = Math.floor(tileWidth * zoom / 100);
		targetTileHeight = Math.floor(tileHeight * zoom / 100);
		canvas.width = sizeX * targetTileWidth;
		canvas.height = sizeY * targetTileHeight;
		drawingError = false;
		drawingLayer = 0;

		var ctx = canvas.getContext("2d");

		for (var drawingLayer=0; drawingLayer < layers.length; drawingLayer++) {
			var name = layerNames[drawingLayer];
			var element = document.getElementById("alpha_" + name);
			if (element) {
				ctx.globalAlpha = element.value.trim() / 100;
			} else {
				ctx.globalAlpha = 1.0;
			}
			if (ctx.globalAlpha > 0.1) {
				paintLayer(ctx, drawingLayer);
			}
		}

		balloonY--;
		if (balloonY < -64) {
			balloonY = (sizeY * targetTileHeight);
		}

		ctx.globalAlpha = 1.0;
		ctx.drawImage(aImages[aImages.length - 4],
				0, 0, 48, 64,
				(sizeX * targetTileWidth) / 2 - 24, balloonY, 48, 64);


		canvas.style.display = "block";

		status("Framerate: " + Math.floor(counter / (new Date().getTime() - starttime) * 1000), true);
		//offsetX = Math.floor(Math.random() * 10);
		setTimeout("draw()", 1);
	}

	function paintLayer(ctx, drawingLayer) {
		var layer = layers[drawingLayer];
		for (var y=0; y < sizeY; y++) {
			for (var x=0; x < sizeX; x++) {
				var gid = layer[(offsetY + y) * numberOfXTiles + (offsetX + x)];
				if (gid > 0) {
					var tileset = getTilesetForGid(gid);
					var base = firstgids[tileset];
					var idx = gid - base;
					var tilesetWidth = aImages[tileset].width;

					try {
						if (aImages[tileset].height > 0) {
							ctx.drawImage(aImages[tileset],
								(idx * tileWidth) % tilesetWidth, Math.floor((idx * tileWidth) / tilesetWidth) * tileHeight, tileWidth, tileHeight, 
								x * targetTileWidth, y * targetTileHeight, targetTileWidth, targetTileHeight);
						}
					} catch (e) {
						document.getElementById("debug").firstChild.data = document.getElementById("debug").firstChild.data + ", " + e;
						status("Error while drawing tileset " + tileset + " " + aImages[tileset] + ": " + e, true);
						drawingError = true;
					}
				}
			}
		}
	}

	/**
	 * Returns the index of the tileset a tile belongs to.
	 */
	function getTilesetForGid(value) {
		var pos;
		for (pos = 0; pos < firstgids.length; pos++) {
			if (value < firstgids[pos]) {
				break;
			}
		}
		return pos - 1;
	}

	var httpRequest;
	function makeRequest(url, callback) {
		if (window.XMLHttpRequest) {
			httpRequest = new XMLHttpRequest();
		} else if (window.ActiveXObject) {
			try {
				httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) {
				httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
			}
		}
		if (httpRequest.overrideMimeType) {
			httpRequest.overrideMimeType('text/xml');
		}
		httpRequest.onreadystatechange = callback;
		httpRequest.open('GET', url, true);
		httpRequest.send(null);
	}


	/**
	 * parses the map file, loads the tileset and resizes the canvas.
	 */
	function parseMap() {
		if (httpRequest.readyState != 4) {
			return;
		}
		if (httpRequest.status != 200) {
			status("Error: Could not find map", true);
			return;
		}
		status("Parsing map...", false);
		var xmldoc = httpRequest.responseXML;
		var root = xmldoc.getElementsByTagName('map').item(0);
		var images = new Array;
		firstgids = new Array;
		layers = new Array;
		layerNames = new Array;

		tileWidth = +root.getAttribute("tilewidth");
		tileHeight = +root.getAttribute("tileheight");

		for (var iNode = 0; iNode < root.childNodes.length; iNode++) {
			var node = root.childNodes.item(iNode);
			if (node.nodeName == "tileset") {
				filename = getTilesetFilename(node)
				images.push(filename);
				firstgids.push(node.getAttribute("firstgid"));
				status("Parsing map...   (Tileset: " + filename + ")", false);
			} else if (node.nodeName == "layer") {
				var layerName = node.getAttribute("name");
				status("Parsing map...   (Layer: " + layerName + ")", false);
				var data = node.getElementsByTagName("data")[0];
				var mapData = data.firstChild.nodeValue.trim();
				var decoder = new JXG.Util.Unzip(JXG.Util.Base64.decodeAsArray(mapData));
				var data = decoder.unzip()[0][0];
				readLayer(layerName, data);
			}
		}
		status("Downloading images...", false);
		images.push("data/sprites/outfit/detail_1.png")
		images.push("data/sprites/outfit/detail_2.png")
		images.push("data/sprites/outfit/detail_3.png")
		images.push("data/sprites/outfit/detail_4.png")
		new ImagePreloader(images, draw);

		numberOfXTiles = root.getAttribute("width")
		numberOfYTiles = root.getAttribute("height")
	}

	function getTilesetFilename(node) {
		var image = node.getElementsByTagName("image");
		var name = node.getAttribute("name");
		if (image.length > 0) {
			name = image[0].getAttribute("source")
		}
		return name.replace(/\.\.\/\.\.\//g, "");
	}

	/**
	 * reads the tile information for a layer
	 */
	function readLayer(name, dataString) {
		var layer = new Array;
		data = dataString;
		for (var i = 0; i < data.length - 3; i=i+4) {
			var tileId = (data.charCodeAt(i) >>> 0)
				+ (data.charCodeAt(i + 1) << 8)
				+ (data.charCodeAt(i + 2) << 16)
				+ (data.charCodeAt(i + 3) << 24);
			layer.push(tileId)
		}
		layerNames.push(name);
		layers.push(layer);
	}

	function load() {
		var location = window.location.hash.substring(2);
		if (location == "") {
			document.getElementById("mapname").value = "Level 0/semos/city.tmx";
			refreshButton();
		}
		checkMapChange();
	}

	function checkMapChange() {
		setTimeout("checkMapChange()", 200);
		var location = window.location.hash.substring(2).replace(/%20/, " ");;
		if (lastMap != location) {
			lastMap = location;
			loadMap();
		}
	}

	function loadMap() {
		var body = document.getElementById("body")
		body.style.cursor = "wait";
		var location = window.location.hash.substring(2).replace(/%20/, " ");
		if (location.indexOf(":") > -1) {
			status("Error: Invalid map name", true);
			return;
		}
		document.getElementById("mapname").value = location;
		// + makes an explicit type conversion required by Opera in drawImage
		zoom = +document.getElementById("zoom").value;
		status("Requesting map...", false);
		makeRequest("tiled/" + escape(location), parseMap);
	}

	function refreshButton() {
		var location = document.getElementById("mapname").value.trim();
		if (lastMap != location) {
			window.location.hash = "#!" + location;
			status("Preparing...", false);
		} else {
			// + makes an explicit type conversion required by Opera in drawImage
			zoom = +document.getElementById("zoom").value;
			draw();
		}
		return false;
	}

	function status(message, finished) {
		//document.getElementById("status").innerHTML = message;
		document.getElementById("status").firstChild.data = message;
		if (finished) {
			var body = document.getElementById("body")
			body.style.cursor = "auto";
		}
	}

	// String.trim() is too new for Firefox 2 and Konqueror
	String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g, "");
	}

</script>
<?php
	}
}
$page = new MapPage();