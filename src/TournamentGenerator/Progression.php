<?php

namespace TournamentGenerator;

/**
 *
 */
class Progression
{

	private $from;
	private $to;
	private $start = 0;
	private $len = null;
	private $filters = [];
	private $progressed = false;

	public function __construct(Group $from, Group $to, int $start = 0, int $len = null) {
		$this->from = $from;
		$this->to = $to;
		$this->start = $start;
		$this->len = $len;
	}

	public function __toString() {
		return 'Team from '.$this->from;
	}

	public function setFilters(array $filters) {
		$this->filters = $filters;
		return $this;
	}
	public function addFilter(TeamFilter ...$filters) {
		foreach ($filters as $filter) {
			$this->filters[] = $filter;
		}
		return $this;
	}

	public function progress(bool $blank = false) {
		if ($this->progressed) return $this;
		if ($blank) $teams = $this->from->isPlayed() ? $this->from->sortTeams(null, $this->filters) : $this->from->simulate($this->filters);
		else $teams = $this->from->sortTeams(null, $this->filters);

		if (count($this->filters) === 0 || $this->len !== null || $this->start !== 0) $next = array_splice($teams, $this->start, ($this->len === null ? count($teams) : $this->len));
		else $next = $teams;

		$i = 1;

		foreach ($next as $team) {
			if ($blank) {
				$this->to->addTeam(new BlankTeam($this.' - '.$i, $team, $this->from, $this));
				$i++;
			}
			else $team->addPoints($this->from->getProgressPoints());
		}

		$this->from->addProgressed($next);
		if (!$blank) $this->to->addTeam($next);
		$this->progressed = true;
		return $this;
	}
	public function reset() {
		$this->progressed = false;
		return $this;
	}

}
