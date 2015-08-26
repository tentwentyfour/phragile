<?php
namespace Phragile;

class StatusByWorkboardDispatcher implements StatusDispatcher {
	private $transactions = [];
	private $columns = null;
	private $phid = null;

	/**
	 * @var array - Maps tasks to the columnPHIDs of their current workboard column
	 */
	private $taskColumnPHIDs = [];

	/**
	 * @var array - names of columns indicating that a task is closed
	 */
	private $closedColumnNames = [];

	public function __construct($phid, TransactionList $transactions, ProjectColumnRepository $columns, array $closedColumnNames)
	{
		$this->phid = $phid;
		$this->transactions = $transactions->getChronologicallySorted();
		$this->taskColumnPHIDs = $this->extractColumnIDs($this->transactions);
		$this->columns = $columns;
		$this->closedColumnNames = $closedColumnNames;
	}

	public function getStatus(array $task)
	{
		$phid = isset($this->taskColumnPHIDs[$task['id']]) ? $this->taskColumnPHIDs[$task['id']] : null;
		return $this->columns->getColumnName($phid) ?: 'Backlog';
	}

	private function extractColumnIDs(array $transactions)
	{
		return array_map([$this, 'findCurrentColumn'], $transactions);
	}

	private function findCurrentColumn(array $taskTransactions)
	{
		return array_reduce($taskTransactions, function($column, $transaction)
		{
			return $transaction['transactionType'] === 'projectcolumn'
				&& $transaction['oldValue']['projectPHID'] === $this->phid
				? $transaction['newValue']['columnPHIDs'][0]
				: $column;
		});
	}

	private function getTaskColumnName(array $task)
	{
		$phid = isset($this->taskColumnPHIDs[$task['id']]) ? $this->taskColumnPHIDs[$task['id']] : null;
		return $this->columns->getColumnName($phid) ?: 'Backlog';
	}

	public function isClosed(array $task)
	{
		return in_array(
			$this->getTaskColumnName($task),
			$this->closedColumnNames
		);
	}
}
