<?php
/**
 * Parses the output of /script DeepInspect.class
 *
 * @author hendrik
 */
class InspectParser {
	private $data;
	private $inspectEndIndex;
	private $inspectStartIndex;
	private $currentIndex;
	private $current;
	private $res;

	public function __construct($data) {
		$data = str_replace("\r\n", "\n", $data);
		$this->data = explode("\n", $data);
	}

	/**
	 * parses the chatlog which may contain any number of /script DeepInspect.class results
	 */
	public function parse() {
		$this->res = array();
		$this->inspectStartIndex = -1;
		$first = true;
		for ($i = 0; $i < count($this->data); $i++) {
			$line = $this->data[$i];

			if (preg_match('/\[..:..\] Script "DeepInspect.class" was successfully executed./', $line)
				|| (preg_match('/\[..:..\] Inspecting .*/', $line) && !$first)) {

				$this->current = array();
				$this->inspectEndIndex = $i -1;

				$this->parseDeepInspect();
				$this->res[] = $this->current;
				$this->inspectStartIndex = -1;
			}

			if ($this->inspectStartIndex < 0) {
				if (preg_match('/\[..:..\] Inspecting .*/', $line)) {
					$this->inspectStartIndex = $i;
					$first = false;
				}
			}
		}

		return $this->res;
	}

	/**
	 * Parses one single deep inspect invokation
	 */
	private function parseDeepInspect() {
		$this->currentIndex = $this->inspectStartIndex + 1;
		$this->parseTopLevelAttributes();
		$this->parseSlots();
	}

	/**
	 * parses the top level attributes
	 */
	private function parseTopLevelAttributes() {
		for ($i = $this->currentIndex; $i < $this->inspectEndIndex; $i++) {
			$line = $this->data[$i];
			if ($line == '') {
				break;
			}
			$temp = explode(': ', $line);
			$this->current[$temp[0]] = $temp[1];
		}
	}


	/**
	 * parses the slots
	 */
	private function parseSlots() {
		$slotName = '';
		for ($i = $this->currentIndex; $i < $this->inspectEndIndex; $i++) {
			$line = preg_replace('/\[..:..\] /', '', $this->data[$i]);
			if (preg_match('/^Slot /', $line)) {
				$slotName = substr($line, 5, strlen($line) - 7);
			} else if (preg_match('/^   (Item, )?RPObject with Attributes of /', $line)) {
				$this->current[$slotName][] = $this->parseRPObject($line);
			}
		}
	}

	/**
	 * Parses an RPObject line
	 *
	 * @param $line
	 */
	private function parseRPObject($line) {
		//   RPObject with Attributes of Class(): [visit_semos_tavern=1][first_poisoned=1][timed_outfit=1][visit_kikareukin_cave=1][visit_magic_city_n=1][visit_magic_city=1][first_death=1][visit_sub2_semos_catacombs=1][id=6][new_release77=1][first_private_message=1][db_id=90080][new_release75=1][first_attacked=1][first_login=1][visit_semos_dungeon_2=1][return_guardhouse=1][new_release=1][visit_semos_caves=1][first_kill=1][visit_semos_plains=1][visit_semos_dungeon=1][first_move=1][visit_semos_city=1][visit_imperial_caves=1][new_release69=1][new_release80=1][timed_naked=1][timed_rules=1] and RPSlots  with maps and RPLink  and RPEvents 
		//   Item, RPObject with Attributes of Class(item): [visibility=100][width=1][class=cloak][type=item][logid=10196][id=1][height=1][def=4][description=][name=dwarf cloak][subclass=dwarf_cloak][resistance=0][y=0][x=0] and RPSlots  with maps and RPLink  and RPEvents 
		$res = array();
		preg_match_all('/\[[^\]]*\]/', $line, $matches);
		foreach ($matches[0] as $match) {
			$pos = strpos($match, '=');
			$res[substr($match, 1, $pos - 1)] = substr($match, $pos + 1, strlen($match) - $pos - 2);
		}
		return $res;
	}
}