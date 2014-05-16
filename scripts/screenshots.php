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
  * This represent a Screenshot.
  */
class Screenshot {
  public $id;
  /* URL of the screenshot. */
  public $url;
  /* One line description of the event. */
  public $description;
  
  function __construct($id, $url, $description) {
    $this->id=$id;
    $this->url=$url;    
    $this->description=$description;
  }
  
  function showThumbnail() {
    echo '<img class="screenshot" src="'.rewriteURL('/images/thumbnail/'.htmlspecialchars($this->url)).'" alt="'.htmlspecialchars($this->description).'">';  
  }
  
  function show() {
    echo '<img class="screenshot" src="/'.htmlspecialchars($this->url).'" alt="'.htmlspecialchars($this->description).'">';
  }
};

/**
  * Return a list of of the screenshots.
  * Each screenshot is a URL to the image.
  * Note: All parameters need to be SQL escaped.
  */
function getScreenshots($where='', $cond='') {
	$query='select * from screenshots '.$where.' order by created desc '.$cond;
    $result = mysql_query($query, getWebsiteDB());
    $list=array();
    
    while($row=mysql_fetch_assoc($result)) {
      $list[]=new Screenshot($row['id'],$row['url'],$row['description']);
    }
    
    mysql_free_result($result);
    
    return $list;
}

function getLatestScreenshot() {
  $list=getScreenshots('','limit 1');
  return $list[0];
}

function addScreenshot($url, $description, $approved=false) {
    $url=mysql_real_escape_string($url);
    $description=mysql_real_escape_string($description);
    
    $query='insert into screenshots values(null,"'.$url.'","'.$description.'", null, true)';
    mysql_query($query, getWebsiteDB());
    if(mysql_affected_rows()!=1) {
        echo '<span class="error">There has been a problem while inserting screenshot: '.mysql_affected_rows().'</span>';
        echo '<span class="error_cause">'.$query.'</span>';
        return;
    }
}

function deleteScreenshot($id) {
	$query='delete from screenshots where id="'.mysql_real_escape_string($id).'"';
    mysql_query($query, getWebsiteDB());
    if(mysql_affected_rows()!=1) {
        echo '<span class="error">There has been a problem while deleting screenshots.</span>';
        echo '<span class="error_cause">'.$query.'</span>';
        return;
    }
}

function updateScreenshot($id, $url, $description, $approved=false) {
    $id=mysql_real_escape_string($id);
    $url=mysql_real_escape_string($url);
    $description=mysql_real_escape_string($description);
    
    $query='update screenshots set url="'.$url.'", description="'.$description.'" where id="'.$id.'"';
    mysql_query($query, getWebsiteDB());
    if(mysql_affected_rows()!=1) {
        echo '<span class="error">There has been a problem while updating screenshots.</span>';
        echo '<span class="error_cause">'.$query.'</span>';
        return;
    }
}

?>