<?php
/*
 Copyright (C) 2011 Faiumoni

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

class CMSPageVersion {
	public $id;
	public $content;
	public $title;
	public $lang;
	public $commitcomment;
	public $displaytitle;
	public $accountId;
	public $username;
	public $timedate;

	public function __construct($content, $displaytitle, $accountId, $timedate, $id=-1, $title='', $lang='', $commitcomment='', $username='') {
		$this->content = $content;
		$this->displaytitle = $displaytitle;
		$this->accountId = $accountId;
		$this->timedate = $timedate;
		$this->id = $id;
		$this->title = $title;
		$this->lang = $lang;
		$this->commitcomment = $commitcomment;
		$this->username = $username;
	}
}

/**
 * A mini content management system implemented using Stendhal Website technology
 *
 * @author hendrik
 */
class CMS {


	/**
	 * gets the most recent version of the specified page
	 * 
	 * @param string $lang language
	 * @param string $title page title
	 */
	public static function readNewestVersion($lang, $title) {
		$sql = "SELECT content, displaytitle, account_id, timedate"
			." FROM page_version WHERE id = ("
			." SELECT max(page_version.id) FROM page, page_version"
			." WHERE page.language = '".mysql_real_escape_string($lang). "'"
			." AND page.title = '".mysql_real_escape_string($title). "'"
			." AND page_version.page_id = page.id)";
		$result = mysql_query($sql, getWebsiteDB());
		$row = mysql_fetch_assoc($result);
		$res = null;
		if (isset($row) && $row != null) {
			$res = new CMSPageVersion($row['content'], $row['displaytitle'], $row['account_id'], $row['timedate']);
		}
		mysql_free_result($result);
		return $res;
	}

	/**
	 * gets the specified version of a page
	 * 
	 * @param int $id page_version.id
	 * @param string $title page title
	 */
	public static function readPageVersion($id) {
		$sql = "SELECT content, displaytitle, account_id, timedate"
			." FROM page_version WHERE id = ".intval($id);
		$result = mysql_query($sql, getWebsiteDB());
		$row = mysql_fetch_assoc($result);
		$res = null;
		if (isset($row) && $row != null) {
			$res = new CMSPageVersion($row['content'], $row['displaytitle'], $row['account_id'], $row['timedate']);
		}
		mysql_free_result($result);
		return $res;
	}


	/**
	 * reads the history
	 * 
	 * @param string $lang language
	 * @param string $title page title
	 */
	public static function readHistory($lang, $title) {
		$sql = "SELECT page_version.id, content, displaytitle, account_id, page_version.timedate, title, language, commitcomment, username"
			." FROM page_version, page, stendhal.account"
			." WHERE page_version.page_id = page.id"
			." AND page_version.account_id = stendhal.account.id";
		if (isset($title) && $title != '') {
			$sql = $sql 
				." AND page.language = '".mysql_real_escape_string($lang). "'"
				." AND page.title = '".mysql_real_escape_string($title). "'";
		}
		$sql = $sql . ' ORDER BY page_version.id DESC';
		$result = mysql_query($sql, getWebsiteDB());
		$res = array();
		while (($row = mysql_fetch_assoc($result)) != null) {
			$res[] = new CMSPageVersion($row['content'], $row['displaytitle'], 
					$row['account_id'], $row['timedate'], $row['id'], $row['title'],
					$row['language'], $row['commitcomment'], $row['username']);
		}
		mysql_free_result($result);
		return $res;
	}

	public static function getPreviousVersion($to) {
		$sql = "SELECT v2.id FROM page_version As v1, page_version As v2 WHERE v1.page_id=v2.page_id "
			." AND v2.timedate<v1.timedate AND v1.id=".intval($to)." ORDER BY v2.id DESC LIMIT 1";
		$res = intval(queryFirstCell($sql, getWebsiteDB()));
		return intval(queryFirstCell($sql, getWebsiteDB()));
	}

	public static function getLatestVersion($from) {
		$sql = "SELECT v2.id FROM page_version As v1, page_version As v2 WHERE v1.page_id=v2.page_id AND v1.id="
			.intval($from)." ORDER BY v2.id DESC LIMIT 1";
		return intval(queryFirstCell($sql, getWebsiteDB()));
	}

	public static function save($lang, $title, $content, $commitcomment, $displaytitle, $accountId) {
		$pageId = CMS::getPageIdCreateIfNecessary($lang, $title);
		$sql = "INSERT INTO page_version (page_id, content, commitcomment, displaytitle, account_id) VALUES"
			." ('".mysql_real_escape_string($pageId). "',"
			." '".mysql_real_escape_string($content). "',"
			." '".mysql_real_escape_string($commitcomment). "', "
			." '".mysql_real_escape_string($displaytitle). "', "
			." '".mysql_real_escape_string($accountId). "')";
		mysql_query($sql, getWebsiteDB());
	}


	/**
	 * gets the id of specified page, creating the page if it does not exists
	 * 
	 * @param string $lang language
	 * @param string $title page title
	 * @return id of page
	 */
	public static function getPageIdCreateIfNecessary($lang, $title) {
		$id = CMS::getPageId($lang, $title);
		if (isset($id)) {
			return $id;
		}
		CMS::createPage($lang, $title);
		return CMS::getPageId($lang, $title);
	}


	/**
	 * gets the id of specified page
	 * 
	 * @param string $lang language
	 * @param string $title page title
	 * @return id of page or <code>null</code>.
	 */
	public static function getPageId($lang, $title) {
		$sql = "SELECT id FROM page"
			." WHERE page.language = '".mysql_real_escape_string($lang). "'"
			." AND page.title = '".mysql_real_escape_string($title). "'";
		$result = mysql_query($sql, getWebsiteDB());
		$row = mysql_fetch_assoc($result);
		if (isset($row) && $row != null) {
			$res = $row[id];
		}
		mysql_free_result($result);
		return $res;
	}

	/**
	 * creates a new page
	 *
	 * @param string $lang language
	 * @param string $title page title
	 */
	public static function createPage($lang, $title) {
		$sql = "INSERT INTO page (language, title) VALUES"
			." ('".mysql_real_escape_string($lang). "',"
			." '".mysql_real_escape_string($title). "')";
		mysql_query($sql, getWebsiteDB());
	}
}