<?php

namespace Phragile;

class TaskList {
	private $tasks = null;
	private $statusDispatcher = null;

	/**
	 * TaskList constructor.
	 * @param array $phabricatorTaskData
	 * @param StatusDispatcher $statusDispatcher
	 * @param array $options
	 * 		@option boolean "ignore_estimates"
	 * 		@option array "ignored_columns"
	 */
	public function __construct(array $phabricatorTaskData, StatusDispatcher $statusDispatcher, array $options)
	{
		$this->statusDispatcher = $statusDispatcher;
		$this->tasks = $this->processTasks($phabricatorTaskData, $options);
	}

	private function processTasks($taskData, $options)
	{
		return array_filter(
			array_map(function($task) use($options)
			{
				return [
					'title' => $task['title'],
					'priority' => $task['priority'],
					'status' => $this->statusDispatcher->getStatus($task),
					'story_points' => $options['ignore_estimates'] ? 1 : $task['auxiliary'][env('MANIPHEST_STORY_POINTS_FIELD')],
					'closed' => $this->statusDispatcher->isClosed($task),
					'id' => $task['id'],
					'assignee' => $task['ownerPHID'],
				];
			}, array_values($taskData)),
			function($task) use($options)
			{
				return !in_array($task['status'], $options['ignored_columns']);
			}
		);
	}

	/**
	 * @return array[]
	 */
	public function getTasks()
	{
		return $this->tasks;
	}

	/**
	 * @return array[] Associative array of task number and story points per status
	 */
	public function getTasksPerStatus()
	{
		return array_reduce($this->tasks, function($acc, $task)
		{
			$acc['total']['tasks'] += 1;
			$acc['total']['points'] += $task['story_points'];

			if (isset($acc[$task['status']]))
			{
				$acc[$task['status']]['tasks'] += 1;
				$acc[$task['status']]['points'] += $task['story_points'];
			} else
			{
				$acc[$task['status']] = [
					'tasks' => 1,
					'points' => $task['story_points']
				];
			}

			return $acc;
		}, ['total' => ['points' => 0, 'tasks' => 0]]);
	}

	/**
	 * @param int $id
	 * @return array Task data
	 */
	public function findTaskByID($id)
	{
		foreach ($this->tasks as $task)
		{
			if ($task['id'] == $id)
			{
				return $task;
			}
		}
	}

	/**
	 * @return int[] List of IDs of closed tasks
	 */
	public function getClosedTaskIDs()
	{
		return array_map(function($task)
		{
			return $task['id'];
		}, array_filter($this->tasks, function($task)
			{
				return $task['closed'];
			})
		);
	}
}
