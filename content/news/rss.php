<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2010 the Arianne Project

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


class RssPage extends Page {

	public function writeHttpHeader() {
		header('Content-Type: application/rss+xml', true);
		$this->writeRss();
		return false;
	}

	public function writeRss() {
		$this->writeHeader();
		$news = getNews(' where news.active=1 ', 'created desc', 'limit 20');
		foreach($news as $entry) {
			$this->writeEntry($entry);
		}
		$this->writeFooter();
	}

	private function renderText($text) {
		// removed 100% scaling of images. It is used on the website to srink 
		// an image to fit into the news columns. In an rss reader, however,
		// it may zoom the image onto screen size, causing ugly artefacts
		$text = preg_replace('/<img (.*?)height *= *"100%"(.*?)>/', '<img \1\2>', $text);
		$text = preg_replace('/<img (.*?)width *= *"100%"(.*?)>/', '<img \1\2>', $text);
		return htmlspecialchars($text);
	}

	private function writeHeader() {
		echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
	<title>Stendhal News</title>
	<link>http://stendhalgame.org</link>
	<description>News feed of the free Stendhal online roleplaying game.</description>
	<language>en</language>
	<copyright>Arianne Project</copyright>
	<pubDate><?php echo date("D, d M Y H:i:s O");?></pubDate>
	<image>
		<url>http://stendhalgame.org/images/favicon.png</url>
		<title>Stendhal News</title>
		<link>http://stendhalgame.org</link>
	</image>
	<atom:link href="http://stendhalgame.org/rss/news.rss" rel="self" type="application/rss+xml" />
<?php
	}

	private function writeEntry($entry) {
		// we do not escape admin input here on purpose.
		// only trusted administrators are allowed to add news and they should
		// be allowed to use full html.
?>
	<item>
		<title><?php 
			echo htmlspecialchars($entry->title);
			if ($entry->updateCount > 0) {
				echo ' [Update No. '.$entry->updateCount.']';
			}
		?></title>
		<description><?php 
			echo $this->renderText($entry->extendedDescription);
			echo $this->renderText($entry->detailedDescription);
		?></description>
		<link><?php echo 'http://stendhalgame.org'.rewriteURL('/news/'.$entry->getNiceURL());?></link>
		<author>newsfeed@stendhalgame.org (Arianne Project)</author>
		<pubDate><?php echo date("D, d M Y H:i:s O", strtotime($entry->date));?></pubDate>
		<category><?php echo $entry->typeTitle;?></category>
		<guid><?php  echo 'http://stendhalgame.org'.rewriteURL('/news/'.$entry->getNiceURL()).'#id-'.$entry->id.'.'.$entry->updateCount;?></guid>
	</item>
<?php
	}

	private function writeFooter() {
?>
</channel>
</rss>
<?php
	}
}

$page = new RssPage();