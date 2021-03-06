<?php

namespace TournamentGenerator;

/**
 *
 */
class TeamFilter
{

	/*
	* WHAT TO CONSIDER  *
	* * * * * * * * * * *
	* points
	* score
	* wins
	* losses
	* draws
	* second
	* third
	* team
	* notprogressed
	* progressed
	*/
	private $what = 'points';

	/*
	* HOW TO COMPARE  *
	* * * * * * * * * *
	* >
	* <
	* >=
	* <=
	* =
	* !=
	*/
	private $how = '>';

	/*
	* VALUE *
	*/
	private $val = 0;

	private $groups = [];

	function __construct(string $what = 'points', string $how = '>', $val = 0, array $groups = []){
		if (!in_array(strtolower($what), ['points', 'score', 'wins', 'draws', 'losses', 'second', 'third', 'team', 'notprogressed', 'progressed'])) throw new \Exception('Trying to filter unexisting type ('.$what.')');
		$this->what = strtolower($what);
		if (!in_array($how, ['>', '<', '>=', '<=', '=', '!='])) throw new \Exception('Trying to filter with unexisting operator ('.$how.')');
		$this->how = $how;
		if (!(gettype($val) === 'integer' && strtolower($what) !== 'team') && !($val instanceof Team && strtolower($what) === 'team')) throw new \Exception('Unsupported filter value type ('.gettype($val).')');
		$this->val = $val;
		$this->groups = array_map(function($a) { return $a->getId(); }, array_filter($groups, function($a) {return ($a instanceof Group);}));
	}
	public function __toString() {
		return 'Filter: '.$this->what.' '.($this->what !== 'notprogressed' && $this->what !== 'progressed' ? $this->how.' '.$this->val : '');
	}

	public function validate(Team $team, $groupsId, string $operation = 'sum', Group $from = null) {
		if (count($this->groups) > 0) $groupsId = array_unique(array_merge($this->groups, (gettype($groupsId) === 'array' ? $groupsId : [$groupsId])), SORT_REGULAR);

		if ($this->what == 'team') return ($this->how === '!=' ? !$this->validateTeam($team) : $this->validateTeam($team));
		elseif ($this->what == 'notprogressed') return !$this->validateProgressed($team, $from);
		elseif ($this->what == 'progressed') return $this->validateProgressed($team, $from);

		return $this->validateCalc($team, $groupsId, $operation);
	}

	private function validateTeam(Team $team) {
		return $this->val === $team;
	}
	private function validateProgressed(Team $team, Group $from = null) {
		if ($from === null) throw new \Exception('Group $from was not defined.');
		return $from->isProgressed($team);
	}
	private function validateCalc(Team $team, array $groupsId, string $operation = 'sum') {
		if (gettype($groupsId) === 'array' && !in_array(strtolower($operation), ['sum', 'avg', 'max', 'min'])) throw new \Exception('Unknown operation of '.$operation.'. Only "sum", "avg", "min", "max" possible.');

		return Utilis\FilterComparator::compare($operation, $this->val, $this->how, $this->what, $team, $groupsId);

	}

}
