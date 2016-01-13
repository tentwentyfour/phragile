<?php

class Project extends Eloquent {

	protected $fillable = ['title', 'slug', 'closed_statuses', 'workboard_mode', 'ignored_columns', 'default_column'];

	/**
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function sprints()
	{
		return $this->hasMany('Sprint')->orderBy('sprint_start', 'desc');
	}

	/**
	 * @return \Illuminate\Validation\Validator
	 */
	public function validate()
	{
		return Validator::make(
			$this->getAttributes(),
			['title' => 'required|unique:projects'],
			['unique' => 'A project with this title already exists.']
		);
	}

	public function currentSprint()
	{
		return $this->newestPastSprint() ?: $this->closestFutureSprint();
	}

	public function getClosedColumns()
	{
		return preg_split('/\s*,\s*/', $this->closed_statuses);
	}

	public function getIgnoredColumns()
	{
		return preg_split('/\s*,\s*/', $this->ignored_columns);
	}

	public function getDefaultColumn()
	{
		return $this->default_column ?: 'Backlog';
	}

	private function newestPastSprint()
	{
		return Sprint::where('sprint_start', '<=', date('Y-m-d'))
			->where('project_id', $this->id)
			->orderBy('sprint_start', 'desc')
			->first();
	}

	private function closestFutureSprint()
	{
		return Sprint::where('sprint_start', '>', date('Y-m-d'))
			->where('project_id', $this->id)
			->orderBy('sprint_start', 'asc')
			->first();
	}
}
