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
  * A class that represent a player, what it is and what it equips.
  */
class Player {
  /* Name of the player */
  public $name;
  /* Sentence that the player wrote using /sentence */
  public $sentence;
  /* Level of the player */
  public $level;
  /* An outfit representing the player look in game. */
  public $outfit;
  /* The colors of the outfit. */
  public $age;
  /* XP of the player. It is a special attribute. */
  public $xp;
  /* adminlevel */
  public $adminlevel;
  /* Attributes the player has as a array key=>value */
  public $attributes;
  /* Money the player has. */
  public $money;
  /* Equipment the player has in slots in a array slot=>item */
  public $equipment;
  
  /* When was this player last seen */
  public $lastseen;

  function __construct($name, $sentence, $age, $level, $xp, $married, $outfit, $outfitColors, $money, $adminlevel, $attributes, $equipment, $lastseen) {
    $this->name=$name;
    $this->sentence=$sentence;
    $this->age=$age;
    $this->level=$level;
    $this->outfit=$outfit;
    if (isset($outfitColors) && strlen($outfitColors) > 0) {
        $this->outfit=$outfit.'_'.$outfitColors;
    }
    $this->xp=$xp;
    $this->married=$married;
    $this->attributes=$attributes;
    $this->adminlevel=$adminlevel;
    $this->money=$money;
    $this->equipment=$equipment;
    $this->lastseen=$lastseen;
  }

	function show() {
		echo '<div class="playerBox">';
		echo '  <a href="'.rewriteURL('/character/'.surlencode($this->name).'.html').'">';
		echo '  <img src="'.rewriteURL('/images/outfit/'.surlencode($this->outfit).'.png').'" alt="" width="48" height="64">';
		echo '  <span class="block name">'.htmlspecialchars($this->name).'</span>';
		echo ' </a>';
		echo '  <div class="level">Level '.$this->level.'</div>';
		if ($this->sentence != '') {
			$temp = $this->sentence;
			if(strlen($temp)>=54) {
				$temp = substr($this->sentence, 0, strpos($this->sentence, ' ', 55) - 1);
			}
			if ($temp != $this->sentence) {
				$temp = $temp.'...';
			}
			echo ' <div class="quote">'.htmlspecialchars($temp).'</div>';
		} else {
			echo ' <div style="clear:left"></div>';
		}
		echo '</div>';
	}

	static function showFromArray($player) {
		echo '<div class="playerBox">';
		echo '  <a href="'.rewriteURL('/character/'.surlencode($player['charname']).'.html').'">';
		echo '  <img src="'.rewriteURL('/images/outfit/'.surlencode($player['outfit']).'.png').'" alt="" width="48" height="64">';
		echo '  <span class="block name">'.htmlspecialchars($player['charname']).'</span>';
		echo ' </a>';
		echo '  <div>Level: '.$player['level'].'</div>';
		if ($player['sentence'] != '') {
			$temp = $player['sentence'];
			if(strlen($temp)>=54) {
				$temp = substr($player['sentence'], 0, strpos($player['sentence'], ' ', 55) - 1);
			}
			if ($temp != $player['sentence']) {
				$temp = $temp.'...';
			}
			echo ' <div class="quote">'.htmlspecialchars($temp).'</div>';
		} else {
			echo ' <div style="clear:left"></div>';
		}
		echo '</div>';
	}

  function getDeaths() {
    $result = mysql_query("
    select
      timedate,
      source
    from gameEvents
    where
      event='killed' and
      param1='".mysql_real_escape_string($this->name)."' and
      datediff(now(),timedate)<=7*52 and
      (param2 = 'C P' or param2 = 'E P' or param2 = 'P P')
    order by timedate desc
    limit 4", getGameDB());

    $kills=array();

    /*
     * TODO: Refactor to use the new table.
     */

    while($row=mysql_fetch_assoc($result)) {
      $kills[$row['timedate']]=$row['source'];
    }

    mysql_free_result($result);
    return $kills;
  }

  function getAccountInfo() {
	$result=mysql_query('select characters.timedate, account.status, characters.status As charstatus from account, characters where account.id=characters.player_id AND charname="'.mysql_real_escape_string($this->name).'"',getGameDB());    $account=array();

    $row=mysql_fetch_assoc($result);

    $account["register"]=$row["timedate"];
    $account["status"]=$row["status"];
    $account["charstatus"]=$row["charstatus"];

    mysql_free_result($result);

    return $account;
  }

	function getHallOfFameScore($fametype) {
		$tableSuffix = 'alltimes';
		$result = mysql_query('select points from halloffame_archive_'.$tableSuffix.' where day = CURRENT_DATE() and charname="'.mysql_real_escape_string($this->name).'" and fametype="'.mysql_real_escape_string($fametype).'"',getGameDB());

		while($row = mysql_fetch_assoc($result)) {
			$points = $row['points'];
		}

		mysql_free_result($result);
		if (!isset($points)) {
			$points=0;
		}
		return $points;
	}
}

/**
  * Returns a list of players online and offline that meet the given condition.
  * Note: Parameters must be sql escaped.
  */
function getPlayers($where='', $sortby='name', $cond='limit 2') {
	return _getPlayers('select distinct character_stats.* from character_stats '.$where.' order by '.$sortby.' '.$cond, getGameDB());
}

function getPlayer($name) {
	$player=_getPlayers('select character_stats.* from character_stats where name="'.mysql_real_escape_string($name).'" limit 1', getGameDB());
    return $player[0];
}

function getBestPlayer($tableSuffix, $where='') {
	$query = 'select halloffame_archive_'.$tableSuffix.'.points, halloffame_archive_'.$tableSuffix.'.charname, character_stats.age, character_stats.level, character_stats.xp, character_stats.outfit, character_stats.outfit_colors, character_stats.sentence from halloffame_archive_'.$tableSuffix.' join character_stats on (charname=name) '.$where.' and day = CURRENT_DATE() and fametype = "R" order by rank limit 1';
	$list = queryWithCache($query, 60*60, getGameDB());
	if (count($list) > 0) {
		return $list[0];
	} else {
		return false;
	}
}


/**
  * Returns a list of players online and offline that meet the given condition from HOF
  * Note: Parameters must be sql escaped.
  */
function getHOFPlayers($tableSuffix, $where='', $fametype = '', $cond='limit 2') {
	$query = 'select distinct halloffame_archive_'.$tableSuffix.'.charname, halloffame_archive_'.$tableSuffix.'.rank, halloffame_archive_'.$tableSuffix.'.points, character_stats.outfit, character_stats.outfit_colors  from halloffame_archive_'.$tableSuffix.' join character_stats on (charname=name) '.$where.' and day = CURRENT_DATE() and fametype = "'.mysql_real_escape_string($fametype).'" order by rank '.$cond;
	$result = mysql_query($query,getGameDB());
	$list=array();

	while($row=mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	mysql_free_result($result);
	return $list;
}

/**
  * Returns a list of players that are online right now.
  */
function getOnlinePlayers() {
	return _getPlayers('select character_stats.* from character_stats where online=1 order by name');
}


/**
 * Returns a list of characters owned by this account.
 *
 * @param string $username
 * @return List of Players
 */
function getCharactersForUsername($username) {
	return _getPlayers('SELECT character_stats.* FROM character_stats, characters, account '
		.'WHERE account.username=\''.mysql_real_escape_string($username).'\' AND '
		.'characters.player_id=account.id AND character_stats.name=characters.charname '
		.'ORDER BY character_stats.name');
}

function _getPlayers($query) {
//	echo $query;
    $result = mysql_query($query,getGameDB());
    $list=array();

    while($row=mysql_fetch_assoc($result)) {
      $attributes=array();
      $attributes['atk']=$row['atk'];
      $attributes['def']=$row['def'];
      $attributes['hp']=$row['hp'];
      $attributes['karma']=$row['karma'];

      $equipment=array();
      $equipment['head']=$row['head'];
      $equipment['lhand']=$row['lhand'];
      $equipment['armor']=$row['armor'];
      $equipment['rhand']=$row['rhand'];
      $equipment['legs']=$row['legs'];
      $equipment['feet']=$row['feet'];
      $equipment['cloak']=$row['cloak'];
      $equipment['finger']=$row['finger'];

      $list[]=new Player($row['name'],
                     $row['sentence'],
                     $row['age'],
                     $row['level'],
                     $row['xp'],
                     $row['married'],
                     $row['outfit'],
                     $row['outfit_colors'],
                     $row['money'],
                     $row['admin'],
                     $attributes,
                     $equipment,
                     $row['lastseen']);
    }

    mysql_free_result($result);

    return $list;
}

/**
 * Fetches all the ranks for the specified character
 *
 * @param String $charname
 */
function getCharacterRanks($charname) {
	$query = "SELECT fametype, rank FROM halloffame_archive_recent WHERE charname='".mysql_real_escape_string($charname)."' AND day=CURRENT_DATE()";
	$result = mysql_query($query, getGameDB());
	// if the player has not played recently, we fetch the all times data
	// this way it is not obvious that the account was abandoned
	if (mysql_num_rows($result) == 0) {
		mysql_free_result($result);
		$query = "SELECT fametype, rank FROM halloffame_archive_alltimes WHERE charname='".mysql_real_escape_string($charname)."' AND day=CURRENT_DATE()";
		$result = mysql_query($query, getGameDB());
		$res['__'] = 'alltimes';
	}

	while($row = mysql_fetch_assoc($result)) {
		$res[$row['fametype']] = $row['rank'];
	}

	mysql_free_result($result);
	return $res;
}


/**
 * gets the hall of fame history for this character
 *
 * @param string $charname
 */
function getHallOfFameHistory($charname) {
	$query = "SELECT day, fametype, rank FROM halloffame_archive_recent WHERE charname='".mysql_real_escape_string($charname)."' ORDER BY day";
	$result = mysql_query($query, getGameDB());

	$res = array();
	$res['D'] = array();
	$res['M'] = array();
	$res['P'] = array();
	$res['A'] = array();
	$res['T'] = array();
	$res['F'] = array();
	$res['W'] = array();
	$res['X'] = array();
	$res['B'] = array();
	$res['R'] = array();
	$res['@'] = array();

	while($row = mysql_fetch_assoc($result)) {
		$res[$row['fametype']][] = intval($row['rank'], 10);
	}

	mysql_free_result($result);
	return $res;
}