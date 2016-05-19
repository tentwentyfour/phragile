<?php
namespace Phragile;

class TransactionRawDataFilter {
	/**
	 * Filters out irrelevant transactions fetched from Phabricator
	 *
	 * @param array $transactions
	 * @return array $transactions
	 */
	public function filter(array $transactions)
	{
		return array_map(function($taskTransactions)
		{
			return array_filter($taskTransactions, [$this, 'isRelevantTransaction']);
		}, $transactions);
	}

	protected function isRelevantTransaction(array $transaction)
	{
		return $this->isWorkboardTransaction($transaction) || $this->isStatusTransaction($transaction);
	}

	protected function isWorkboardTransaction(array $transaction)
	{
		return $transaction['transactionType'] === TransactionRawDataProcessor::COLUMN_CHANGE_TRANSACTION;
	}

	protected function isStatusTransaction(array $transaction)
	{
		return $transaction['transactionType'] === TransactionRawDataProcessor::STATUS_CHANGE_TRANSACTION
		    || $transaction['transactionType'] === TransactionRawDataProcessor::MERGE_AND_CLOSE_TRANSACTION;
	}
}
