<?php
/**
 * An interface to handle systematic literature review data. Not really related to Stendhal.
 */


/**
  * Returns a list of slr for a specified reviewer
  */
function getAllSlr($reviewer = null) {
	$filter = '';
	if (isset($reviewer)) {
		$filter = "WHERE reviewer='".mysql_real_escape_String($reviewer)."'";
	}
	$sql = "SELECT * FROM slr WHERE id in (SELECT max(id) FROM slr ".$filter." GROUP BY reviewer,paper_bibkey) ORDER BY paper_bibkey;";
	return getSlrArray($sql);
}

function getSlr($id) {
	$sql = "SELECT * FROM slr WHERE id ='".mysql_real_escape_String($id)."'";
	return getSlrArray($sql);
}

function getSlrArray($sql) {
	$result = mysql_query($sql, getWebsiteDB());

	$list = array();
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}

	mysql_free_result($result);
	return $list;
}

function getSlrMetadata() {
	$sql = "SELECT column_name, column_type, column_comment FROM information_schema.columns WHERE table_name = 'slr';";
	return getSlrArray($sql);
}

function addSlr($metadata, $parameterMap) {
	$query = generateSlrInsertFromMap('slr', $metadata, $parameterMap);
	mysql_query($query, getWebsiteDB());
	if(mysql_affected_rows()==0) {
		echo '<span class="error">There has been a problem while inserting slr:'.mysql_affected_rows().'</span>';
		echo '<span class="error_cause">'.$query.'</span>';
		return;
	}

	$result=mysql_query('select LAST_INSERT_ID() As lastid from slr;', getWebsiteDB());
	while($rowimages=mysql_fetch_assoc($result)) {      
		$slrid = $rowimages['lastid'];
	}
	mysql_free_result($result);
	return $slrid;	
}


/**
 * generates an insert statement from a given parameter map
 * excludes the following column names from the insert statement:
 * id and timedate
 */
function generateSlrInsertFromMap ($tablename, $metadata, $parameterMap) {
	$readonly = array('id', 'timedate');
	
	$statement = "INSERT INTO ".$tablename." (";
	
	$columnList = "";
	$valueList = "";

	foreach ($metadata As $meta) {
		$column = $meta['column_name'];
		if (in_array($meta['column_name'], $readonly)) {
			continue;
		}
		if (isset($parameterMap[$column])) {
			$columnList .= $column.",";
			$valueList .="'".mysql_real_escape_string($parameterMap[$column])."',";
		}
	}

	$columnList[strlen($columnList)-1] = " ";
	$valueList[strlen($valueList)-1] = " ";
	
	$statement .= $columnList.") VALUES (".$valueList.");";
	
	return $statement;
}



/**
  * A class representing a slr item without comments.
  */
class SlrType {
	public $id;

	/** Title */
	public $title;

	/** Image */
	public $image;

	function __construct($id, $title, $image) {
		$this->id=$id;
		$this->title=$title;
		$this->image=$image;
	}
}

function getSlrTypes() {
	$sql = 'SELECT * FROM slr_type ORDER BY title';

	$result = mysql_query($sql, getWebsiteDB());
	$list = array();

	while($row = mysql_fetch_assoc($result)) {
		$list[]=new SlrType(
			$row['id'],
			$row['title'],
			$row['image']
		);
	}

	mysql_free_result($result);
	return $list;
}
?>