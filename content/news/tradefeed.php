<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2013 Faiumoni e. V.

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
class TradeFeedPage extends Page {

	/**
	 * write the http header and prevents the stendhal webpage frame from rendering
	 *
	 * @return boolean false
	 */
	public function writeHttpHeader() {
		header('Content-Type: application/atom+xml', true);
		$this->writeFeed();
		return false;
	}

	public function writeFeed() {
		global $cache;
		$feed = $cache->fetch('stendhal-tradefeed');
		if (!isset($feed) || $feed == '') {
			$feed = $this->generateFeed();
			$cache->store('stendhal-tradefeed', $feed, 5*60);
		}
		echo $feed;
	}

	public function generateFeed() {
		$entries = TradeOffer::getTradeOffers();
		$res = $this->generateHeader($entries);
		foreach($entries as $entry) {
			$res .= $this->generateEntry($entry);
		}
		$res .= $this->generateFooter();
		return $res;
	}

	private function formatedDate($date) {
		$datetime = new DateTime($date);
		$datetime->setTimezone(new DateTimeZone('GMT'));
		return $datetime->format('Y-m-d\TH:i:s\Z');
	}

	private function generateHeader($entries) {
		$date = date(DATE_ATOM);
		if (count($entries) > 0) {
			$date = $entries[0]->timedate;
		}
		$res = '';
		$res .= '<feed xml:lang="en-US" xmlns="http://www.w3.org/2005/Atom">';
		$res .= '<title>Harold\'s Offers</title>';
		$res .= '<subtitle>Stendhal Trades</subtitle>';
		$res .= '<link href="https://stendhalgame.org/trade" rel="self"/> ';
		$res .= '<updated>'.$this->formatedDate($date).'</updated>';
		$res .= '<logo>/images/events/harold_logo.png</logo>';
		$res .= '<icon>/images/events/harold_icon.png</icon>';
		$res .= '<author>';
		$res .= '<name>Harold</name>';
		$res .= '<email>harold@stendhalgame.org</email>';
		$res .= '</author>';
		$res .= '<id>tag:stendhalgame.org,2013:https://stendhalgame.org/trade</id>'."\r\n";
		return $res;
	}

	private function generateEntry($entry) {
		$message = 'New offer for ' . htmlspecialchars($entry->quantity) . ' ' . htmlspecialchars($entry->itemname)
			 . ' at ' . htmlspecialchars($entry->price) . '. ' . htmlspecialchars($entry->stats);
		$category = getItem($entry->itemname)->class;
		$res = '';
		$res .= '<entry>';
		$res .= '<id>'.rewriteURL('https://stendhalgame.org/trade/'.$entry->id).'</id>';
		$res .= '<title>'.$message.'</title>';
		$res .= '<updated>'.$this->formatedDate($entry->timedate).'</updated>';
		$res .= '<published>'.$this->formatedDate($entry->timedate).'</published>';
		$res .= '<category term="'.htmlspecialchars($category) .'" />';
		$res .= '<link href="'.rewriteURL('https://stendhalgame.org/trade/'.$entry->id.'.html').'" rel="alternate"></link>';
		$res .= '<content>'.$message.'</content>';
		$res .= "</entry>\r\n";
		return $res;
	}

	private function generateFooter() {
		return '</feed>';
	}
}

$page = new TradeFeedPage();