<?php

use Phragile\BurndownChart;
use Phragile\ClosedTimeByStatusFieldDispatcher;
use Phragile\ClosedTimeByWorkboardDispatcher;
use Phragile\ClosedTimeDispatcher;
use Phragile\Task;

class BurndownChartTest extends TestCase {

	private function mockWithTransactions(array $tasks, array $transactions)
	{
		return $this->mockWithTransactionsAndClosedTimeDispatcher($tasks, $transactions, new ClosedTimeByStatusFieldDispatcher());
	}

	private function mockWithTransactionsAndClosedTimeDispatcher(
		array $tasks, array $transactions, ClosedTimeDispatcher $dispatcher
	)
	{
		$taskListMock = $this->getMockBuilder('Phragile\TaskList')
			->disableOriginalConstructor()
			->getMock();
		$taskListMock->method('getTasks')->willReturn($tasks);
		$taskListMock->method('findTaskByID')->will($this->returnCallback(function($id) use($tasks)
		{
			return $tasks[$id];
		}));

		return new BurndownChart(
			new Sprint(['sprint_start' => '2014-12-01', 'sprint_end' => '2014-12-14']),
			$taskListMock,
			$transactions,
			$dispatcher
		);
	}

	private function mockWithTransactionsInWorkboardMode(array $tasks, array $transactions)
	{
		return $this->mockWithTransactionsAndClosedTimeDispatcher(
			$tasks,
			$transactions,
			new ClosedTimeByWorkboardDispatcher($this->testProjectPHID, $this->closedColumnPHIDs)
		);
	}

	private $testProjectPHID = 'PHID-123';

	private $tasks = [
		'1' => [
			'id' => 1,
			'closed' => true,
			'points' => 8
		],
		'2' => [
			'id' => 2,
			'closed' => true,
			'points' => 2
		]
	];

	/**
	 * @before
	 */
	public function initDummyTasks()
	{
		$this->tasks = array_map(function($taskData)
		{
			return new Task(array_merge($taskData, [
				'title' => 'A Task',
				'priority' => 'Normal',
				'status' => 'Open',
				'assigneePHID' => null,
			]));
		}, $this->tasks);
	}

	private $closedColumnPHIDs = ['123abc', 'abc123'];

	public function testClosedPerDayAddsStoryPoints()
	{
		$burndown = $this->mockWithTransactions(
			$this->tasks,
			[
				'1' => [[
					'transactionType' => 'status',
					'oldValue' => 'open',
					'newValue' => 'resolved',
					'dateCreated' => '1418040000', // Dec 8
				]],
				'2' => [[
					'transactionType' => 'status',
					'oldValue' => 'open',
					'newValue' => 'resolved',
					'dateCreated' => '1418050000', // Dec 8
				]]
			]
		);

		$this->assertSame(10, $burndown->getPointsClosedPerDay()['2014-12-08']);
	}

	public function testClosedPerDayDetectsBefore()
	{
		$burndown = $this->mockWithTransactions(
			$this->tasks,
			['1' => [[
				'transactionType' => 'status',
				'oldValue' => 'open',
				'newValue' => 'resolved',
				'dateCreated' => '1415664000', // Nov 11
			]]]
		);

		$this->assertSame(8, $burndown->getPointsClosedBeforeSprint());
	}

	public function testClosedPerDayIgnoresClosedToClosedTransaction()
	{
		$burndown = $this->mockWithTransactions(
			['1' => $this->tasks['1']],
			[
				'1' => [
					[
						'transactionType' => 'status',
						'oldValue' => 'open',
						'newValue' => 'resolved',
						'dateCreated' => '1418040000', // Dec 8
					],
					[
						'transactionType' => 'status',
						'oldValue' => 'resolved',
						'newValue' => 'invalid',
						'dateCreated' => '1418130000', // Dec 9
					]
				]
			]
		);

		$closed = $burndown->getPointsClosedPerDay();
		$this->assertSame(0, $closed['2014-12-09']);
		$this->assertSame(8, $closed['2014-12-08']);
	}

	public function testClosedPerDayOverridesTimeWhenClosedReopenedAndClosedAgain()
	{
		$burndown = $this->mockWithTransactions(
			['1' => $this->tasks['1']],
			[
				'1' => [
					[
						'transactionType' => 'status',
						'oldValue' => 'open',
						'newValue' => 'resolved',
						'dateCreated' => '1418040000', // Dec 8
					],
					[
						'transactionType' => 'status',
						'oldValue' => 'resolved',
						'newValue' => 'open',
						'dateCreated' => '1418050000',
					],
					[
						'transactionType' => 'status',
						'oldValue' => 'open',
						'newValue' => 'resolved',
						'dateCreated' => '1418130000', // Dec 9
					]
				]
			]
		);

		$closed = $burndown->getPointsClosedPerDay();
		$this->assertSame(0, $closed['2014-12-08']);
		$this->assertSame(8, $closed['2014-12-09']);
	}

	public function testClosedPerDayIgnoresStatusChangeInWorkboardMode()
	{
		$burndown = $this->mockWithTransactionsInWorkboardMode(
			$this->tasks,
			[
				'1' => [[
					'transactionType' => 'core:columns',
					'newValue' => [[
						'fromColumnPHIDs' => ['anyNotClosed' => 'anyNotClosed'],
						'columnPHID' => $this->closedColumnPHIDs[1],
						'boardPHID' => $this->testProjectPHID,
					]],
					'dateCreated' => '1418040000', // Dec 8
				]],
				'2' => [[
					'transactionType' => 'core:columns',
					'newValue' => [[
						'fromColumnPHIDs' => ['anyNotClosed' => 'anyNotClosed'],
						'columnPHID' => $this->closedColumnPHIDs[0],
						'boardPHID' => $this->testProjectPHID,
					]],
					'dateCreated' => '1418050000', // Dec 8
				]]
			]
		);

		$this->assertSame(10, $burndown->getPointsClosedPerDay()['2014-12-08']);
	}

	public function testClosedPerDayConsidersMostRecentColumnChangeInWorkboardMode()
	{
		$burndown = $this->mockWithTransactionsInWorkboardMode(
			$this->tasks,
			[
				'1' => [
					[
						'transactionType' => 'core:columns',
						'newValue' => [[
							'fromColumnPHIDs' => ['anyNotClosed' => 'anyNotClosed'],
							'columnPHID' => $this->closedColumnPHIDs[1],
							'boardPHID' => $this->testProjectPHID,
						]],
						'dateCreated' => DateTime::createFromFormat('d.m.Y H:i:s', '08.12.2014 10:00:00')->format('U'),
					],
					[
						'transactionType' => 'core:columns',
						'newValue' => [[
							'fromColumnPHIDs' => [$this->closedColumnPHIDs[1] => $this->closedColumnPHIDs[1]],
							'columnPHID' => 'anyNotClosed',
							'boardPHID' => $this->testProjectPHID,
						]],
						'dateCreated' => DateTime::createFromFormat('d.m.Y H:i:s', '08.12.2014 12:00:00')->format('U'),
					],
					[
						'transactionType' => 'core:columns',
						'newValue' => [[
							'fromColumnPHIDs' => ['anyNotClosed' => 'anyNotClosed'],
							'columnPHID' => $this->closedColumnPHIDs[1],
							'boardPHID' => $this->testProjectPHID,
						]],
						'dateCreated' => DateTime::createFromFormat('d.m.Y H:i:s', '09.12.2014 10:00:00')->format('U'),
					],
				],
			]
		);

		$this->assertSame(0, $burndown->getPointsClosedPerDay()['2014-12-08']);
		$this->assertSame(8, $burndown->getPointsClosedPerDay()['2014-12-09']);
	}

	public function testClosedPerDayAddsStoryPointsInWorkboardMode()
	{
		$burndown = $this->mockWithTransactionsInWorkboardMode(
			$this->tasks,
			[
				'1' => [[
					'transactionType' => 'status',
					'oldValue' => 'open',
					'newValue' => 'resolved',
					'dateCreated' => '1418040000', // Dec 8
				]],
				'2' => [[
					'transactionType' => 'status',
					'oldValue' => 'open',
					'newValue' => 'resolved',
					'dateCreated' => '1418050000', // Dec 8
				]]
			]
		);

		$this->assertSame(0, $burndown->getPointsClosedPerDay()['2014-12-08']);
	}

	public function testOpenTaskTransactionsAreIgnored()
	{
		$burndown = $this->mockWithTransactions(
			['500' => new Task([
				'title' => 'A Task',
				'priority' => 'Normal',
				'id' => 500,
				'status' => 'Open',
				'closed' => false,
				'assigneePHID' => null,
				'points' => 5,
			])],
			['500' => [[ // this transaction's task is not closed and should be ignored
				'transactionType' => 'status',
				'oldValue' => 'open',
				'newValue' => 'resolved',
				'dateCreated' => '1415664000', // Nov 11
			]]]
		);

		$this->assertNull($burndown->getPointsClosedBeforeSprint());
	}

	public function testClosedPerDayDetectsMergedTasks()
	{
		$burndown = $this->mockWithTransactions(
			$this->tasks,
			[
				'1' => [[
					        'transactionType' => 'mergedinto',
					        'dateCreated' => '1418040000', // Dec 8
				        ]],
			]
		);

		$this->assertSame(8, $burndown->getPointsClosedPerDay()['2014-12-08']);
	}
}
