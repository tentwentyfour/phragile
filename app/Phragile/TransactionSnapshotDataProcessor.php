<?php

namespace Phragile;

class TransactionSnapshotDataProcessor {

	public function process(array $snapshotData)
	{
		$transactions = [];
		foreach ($snapshotData as $taskID => $taskTransactions)
		{
			$transactions[$taskID] = array_filter(array_map([$this, 'getSnapshotTransaction'], $taskTransactions));
		}
		return $transactions;
	}

	/**
	 * @param array $snapshotTransaction
	 * @return Transaction|false
	 */
	private function getSnapshotTransaction(array $snapshotTransaction)
	{
		if (!array_key_exists('type', $snapshotTransaction))
		{
			return false;
		}
		if ($snapshotTransaction['type'] === ColumnChangeTransaction::TYPE)
		{
			return new ColumnChangeTransaction([
				'timestamp' => $snapshotTransaction['timestamp'],
				'workboardPHID' => $snapshotTransaction['workboardPHID'],
				'oldColumnPHID' => $snapshotTransaction['oldColumnPHID'],
				'newColumnPHID' => $snapshotTransaction['newColumnPHID'],
			]);
		} elseif ($snapshotTransaction['type'] === StatusChangeTransaction::TYPE)
		{
			return new StatusChangeTransaction([
				'timestamp' => $snapshotTransaction['timestamp'],
				'oldStatus' => $snapshotTransaction['oldStatus'],
				'newStatus' => $snapshotTransaction['newStatus']
			]);
		} elseif ($snapshotTransaction['type'] === MergedIntoTransaction::TYPE)
		{
			return new MergedIntoTransaction($snapshotTransaction['timestamp']);
		}
		return false;
	}

}
