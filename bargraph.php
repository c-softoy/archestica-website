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
/**
 * This script creates a bar chart diagram.
 */

// This array of values is just here for the example.

$data=$_REQUEST['data'];
$values = explode(",",$data);

// Get the total number of columns we are going to plot

$columns  = count($values)-1; // HACK: "fake away" the terminating comma

// Get the height and width of the final image

$width = 560;
$height = 200;

// Set the amount of space between each column

$padding = 5;

// set the height needed to pad above column for number data
// and below for extra data string
$verticalpadding = 15;

// Get the width of 1 column

$column_width = $width / $columns ;

// Generate the image variables

$im        = imagecreate($width,$height+$verticalpadding +$verticalpadding );
$gray      = imagecolorallocate ($im,0x00,0x11,0xcc);
$gray_lite = imagecolorallocate ($im,0xee,0xee,0xee);
$gray_dark = imagecolorallocate ($im,0x7f,0x7f,0x7f);
$white     = imagecolorallocate ($im,0xff,0xff,0xff);
$black     = imagecolorallocate ($im,0x00,0x00,0x00);

// Fill in the background of the image

imagefilledrectangle($im,0,0,$width,$height+$verticalpadding +$verticalpadding ,$white);

$maxv = 1;

// Parse date and value/amount from list of values and
// calculate the maximum value we are going to plot as well.

for($i=0;$i<$columns;$i++) {

  // $pair[0] contains a date string like for example "M-d".
  // $pair[1] contains the actual amount to be plotted for example "15".

  $pair = explode('_', $values[$i]);

  if (count($pair) !== 2) {

    // There is not exactly one underscore in the value.
    // Ignore it.

    $values[$i] = NULL;
  }
  else {
    $maxv = max($pair[1],$maxv);

    $values[$i] = $pair;
  }
}

// $values now looks like this:
// array[day_offset] => array(
//  0 => date string
//  1 => amount/value
// )

// Now plot each column

for($i=0;$i<$columns;$i++) {
  if (is_null($values[$i])) {

    // The value for this index was not considered valid.
    // Ignore it completely.

    continue;
  }

	$column_height = ($height / 100) * (( $values[$i][1] / $maxv) *100);

	$x1 = ($columns-1-$i)*$column_width;
	$y1 = $height-$column_height + $verticalpadding ;
	$x2 = (($columns-$i)*$column_width)-$padding;
	$y2 = $height + $verticalpadding;

	imagefilledrectangle($im,$x1,$y1,$x2,$y2,$gray);
	// following usual convention, values of height of bar chart written just above each column
	imagestring($im, 3, $x1+($x2-$x1)/2-5,$y1-$verticalpadding, $values[$i][1], $black);
	imagestring($im, 2, $x1,$y2+2, $values[$i][0], $black);

	// This part is just for 3D effect
	imageline($im,$x1,$y1,$x1,$y2,$black);
	imageline($im,$x1,$y2,$x2,$y2,$black);
	imageline($im,$x2,$y1,$x2,$y2,$black);
}

// Send the PNG header information. Replace for JPEG or GIF or whatever

header ("Content-type: image/png");
imagepng($im);
?>
