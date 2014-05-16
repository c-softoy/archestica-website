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


/**
 * entries in the itemlog
 *
 * @author hendrik
 */
class ItemLogEntry {
	/** id of log entry */
	public $id;
	/** date and time of event */
	public $timedate;
	/** id of item */
	public $itemid;
	/** name of player */
	public $source;
	/** event */
	public $event;
	/** parameters */
	public $param1;
	/** parameters */
	public $param2;
	/** parameters */
	public $param3;
	/** parameters */
	public $param4;

	function __construct($id, $timedate, $itemid, $source, $event, $param1, $param2, $param3, $param4) {
		$this->id = $id;
		$this->timedate = $timedate;
		$this->itemid = $itemid;
		$this->source = $source;
		$this->event = $event;
		$this->param1 = $param1;
		$this->param2 = $param2;
		$this->param3 = $param3;
		$this->param4 = $param4;
	}
}


/**
 * Methods to query the item log
 *
 * @author hendrik
 */
class ItemLog {

	public static function getLogEntriesForItem($itemid) {
		if (! preg_match('/^[0-9, ]*$/', $itemid)) {
			return;
		}
		$query = 'SELECT * FROM itemlog WHERE itemid IN ('.$itemid.') ORDER BY itemid, id';
		return ItemLog::_getItemLogEntries($query);
	}

	/**
	 * gets an array of itemlog entries 
	 *
	 *
	 * @param string $query sql query on itemlog table
	 * @return array of ItemLogEntry objects
	 */
	public static function _getItemLogEntries($query) {
		$result = mysql_query($query, getGameDB());
		$res = array();

		while ($row = mysql_fetch_assoc($result)) {
			$res[] = new ItemLogEntry($row['id'], $row['timedate'],
				$row['itemid'], $row['source'], $row['event'], 
				$row['param1'], $row['param2'], $row['param3'], $row['param4']);
		}

		mysql_free_result($result);
		return $res;
	}
}