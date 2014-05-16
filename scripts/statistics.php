<?php
/*
Stendhal website - a website to manage and ease playing of Stendhal game
Copyright (C) 2008-2012  Faiumoni e. V.

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

class ServerStatistics {

	public static function checkServerIsOnline() {
		$sql = 'SELECT TIME_TO_SEC(TIMEDIFF(now(), timedate)) As diff FROM statistics ORDER BY id DESC LIMIT 1';
		$result = queryFirstCell($sql, getGameDB());
		return $result<300;
	}

	public static function readOnlineStatS() {
		$res = array();
		$sql = 'SELECT name, val FROM statistics_archive WHERE day = CURRENT_DATE()';
		$result = mysql_query($sql, getGameDB());

		while ($row = mysql_fetch_assoc($result)) {
			$res[$row['name']] = $row['val'];
		}

		mysql_free_result($result);
		return $res;
	}
}
