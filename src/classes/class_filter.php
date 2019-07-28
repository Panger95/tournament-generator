<?php

namespace TournamentGenerator;

/**
 *
 */
class Filter
{

	private $group;
	private $filters = [];

	function __construct(Group $group, array $filters) {
		$this->group = $group;
		$this->filters = $filters;
	}

	/**
	 * Apply filters
	 * @param array &$teams
	 */
	public function filter(array &$teams) {
		foreach ($this->filters as $key => $filter) {
			if (gettype($filter) === 'array') {
				$this->filterMulti($teams, $filter, $key);
				continue;
			}
			elseif ($filter instanceof TeamFilter) {
				$teams = array_filter($teams, function($team) use ($filter) {return $filter->validate($team, $this->group->id, 'sum', $this->group); });
				continue;
			}
			throw new \Exception('Filer ['.$key.'] is not an instance of TeamFilter class');
		}
		return $teams;
	}

	private function filterMulti(array &$teams, array $filters, string $how = 'and') {
		switch (strtolower($how)) {
			case 'and':
				foreach ($teams as $tkey => $team) {
					if (!$this->filterAnd($team, $filter)) unset($teams[$tkey]); // IF FILTER IS NOT VALIDATED REMOVE TEAM FROM RETURN ARRAY
				}
				return true;
			case 'or':
				foreach ($teams as $tkey => $team) {
					if (!$this->filterOr($team, $filter)) unset($teams[$tkey]); // IF FILTER IS NOT VALIDATED REMOVE TEAM FROM RETURN ARRAY
				}
				return true;
		}
		throw new \Exception('Unknown opperand type "'.$key.'". Expected "and" or "or".');
	}

	private function filterAnd(Team $team, array $filters) {
		foreach ($filters as $key => $value) {
			if (gettype($value) === 'array') {
				switch (strtolower($key)) {
					case 'and':
						if ($this->filterAnd($team, $value)) return false;
						break;
					case 'or':
						if ($this->filterOr($team, $value)) return false;
						break;
					default:
						throw new \Exception('Unknown opperand type "'.$key.'". Expected "and" or "or".');
						break;
				}
				continue;
			}
			elseif ($value instanceof TeamFilter) {
				if (!$value->validate($team, $this->group->id, 'sum', $this->group)) return false;
				continue;
			}
			throw new \Exception('Filer ['.$key.'] is not an instance of TeamFilter class');
		}
		return true;
	}
	private function filterOr(Team $team, array $filters) {
		foreach ($filters as $key => $value) {
			if (gettype($value) === 'array') {
				switch (strtolower($key)) {
					case 'and':
						if ($this->filterAnd($team, $value)) return true;
						break;
					case 'or':
						if ($this->filterOr($team, $value)) return true;
						break;
					default:
						throw new \Exception('Unknown opperand type "'.$key.'". Expected "and" or "or".');
						break;
				}
				continue;
			}
			elseif ($value instanceof TeamFilter) {
				if (!$value->validate($team, $this->group->id, 'sum', $this->group)) return true;
				continue;
			}
			throw new \Exception('Filer ['.$key.'] is not an instance of TeamFilter class');
		}
		return false;
	}
}