<?php

use Phragile\Phragile;

class SprintsController extends Controller {

	public function show(Sprint $sprint)
	{
		if (!$sprint->exists)
		{
			return $this->sprintNotFound($sprint->phabricator_id);
		} elseif ($sprint->hasEnded() && !$sprint->sprintSnapshots->isEmpty())
		{
			return App::make('SprintSnapshotsController')->show($sprint->sprintSnapshots->first());
		} else
		{
			return $this->showWithLiveData($sprint);
		}
	}

	public function showWithLiveData(Sprint $sprint)
	{
		return View::make(
			'sprint.view',
			Phragile::getGlobalInstance()->newSprintLiveDataActionHandler()->getViewData($sprint)
		);
	}

	public function exportJSON(Sprint $sprint)
	{
		if ($sprint->hasEnded() && !$sprint->sprintSnapshots->isEmpty())
		{
			return App::make('SprintSnapshotsController')->exportJSON($sprint->sprintSnapshots->first());
		} else
		{
			return Response::json(
				Phragile::getGlobalInstance()->newSprintLiveDataActionHandler()->getExportData($sprint)
			);
		}
	}

	public function create(Project $project)
	{
		$user = Auth::user();
		$user->setPhabricatorURL($_ENV['PHABRICATOR_URL']);

		if (!$user->certificateValid())
		{
			Flash::warning('Please set a valid Conduit certificate before trying to create a new sprint.');
			return Redirect::back();
		}

		return View::make('sprint.create', compact('project'));
	}

	public function store(Project $project)
	{
		$sprint = new Sprint(array_merge(
			array_map('trim', Input::all()),
			['project_id' => $project->id]
		));
		$actionHandler = Phragile::getGlobalInstance()->newSprintStoreActionHandler();
		$actionHandler->performAction($sprint, Auth::user());

		return $actionHandler->getRedirect();
	}

	public function updateSettings(Sprint $sprint)
	{
		$sprint->update(Input::only('ignore_estimates'));

		Flash::success('The sprint settings have been updated');
		return Redirect::back();
	}

	public function delete(Sprint $sprint)
	{
		if ($sprint->delete()) {
			Flash::success('The sprint was deleted.');
			return Redirect::route('project_path', ['project' => $sprint->project->slug]);
		} else {
			Flash::error('The sprint could not be deleted. Please try again.');
			return Redirect::back();
		}
	}

	private function sprintNotFound($sprintPhabricatorId)
	{
		$actionHandler = Phragile::getGlobalInstance()->newSprintNotFoundActionHandler();
		return $actionHandler->performAction($sprintPhabricatorId);
	}

	public function connect()
	{
		$project = Project::find(Input::get('project'));

		return $this->store($project);
	}
}
