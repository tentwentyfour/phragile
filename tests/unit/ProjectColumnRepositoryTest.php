<?php

use Phragile\PhabricatorAPI;
use Phragile\ColumnChangeTransaction;
use Phragile\ProjectColumnRepository;

/**
 * @covers Phragile\ProjectColumnRepository
 */
class ProjectColumnRepositoryTest extends PHPUnit_Framework_TestCase {

	private $workboardColumns = [
		'PHID-123abc' => 'backlog',
		'PHID-321cba' => 'done',
		'PHID-abc123' => 'to do',
	];

	private function newProjectColumnRepository()
	{
		$transactions =	[
			'task1' => [
				new ColumnChangeTransaction([
					'timestamp' => DateTime::createFromFormat('d.m.Y H:i:s', '01.01.2016 10:00:00')->format('U'),
					'workboardPHID' => 'PHID-PROJ-FOO',
					'oldColumnPHID' => 'PHID-123abc',
					'newColumnPHID' => 'PHID-abc123',
				]),
				new ColumnChangeTransaction([
					'timestamp' => DateTime::createFromFormat('d.m.Y H:i:s', '02.01.2016 10:00:00')->format('U'),
					'workboardPHID' => 'PHID-PROJ-FOO',
					'oldColumnPHID' => 'PHID-abc123',
					'newColumnPHID' => 'PHID-321cba',
				]),
			],
		];
		return new ProjectColumnRepository('PHID-PROJ-FOO', $transactions, $this->newPhabricatorAPI());
	}

	private function newPhabricatorAPI()
	{
		$phabricatorAPI = $this->getMockBuilder(PhabricatorAPI::class)
			->disableOriginalConstructor()
			->getMock();
		$phabricatorAPI->method('queryPHIDs')->will($this->returnCallback(function()
		{
			return array_map(function($column)
			{
				return ['name' => $column];
			}, $this->workboardColumns);
		}));
		return $phabricatorAPI;
	}

	public function columnPhidProvider()
	{
		return array_map(
			function($key, $value) {
				return [$key, $value];
			},
			array_keys($this->workboardColumns),
			$this->workboardColumns
		);
	}

	/**
	 * @dataProvider columnPhidProvider
	 */
	public function testGivenExistingColumnPhid_getColumnNameReturnsTheName($columnPhid, $expectedName)
	{
		$repository = $this->newProjectColumnRepository();
		$this->assertEquals($expectedName, $repository->getColumnName($columnPhid));
	}

	public function testGivenNull_getColumnNameReturnsNull()
	{
		$repository = $this->newProjectColumnRepository();
		$this->assertNull($repository->getColumnName(null));
	}

	public function columnNameProvider()
	{
		return array_map(
			function($key, $value) {
				return [$value, $key];
			},
			array_keys($this->workboardColumns),
			$this->workboardColumns
		);
	}

	/**
	 * @dataProvider columnNameProvider
	 */
	public function testGivenExistingColumnName_getColumnPHIDReturnsPhid($columnName, $expectedPhid)
	{
		$repository = $this->newProjectColumnRepository();
		$this->assertEquals($expectedPhid, $repository->getColumnPhid($columnName));
	}

	public function testGivenNonExistingColumnName_getColumnPHIDReturnsNull()
	{
		$repository = $this->newProjectColumnRepository();
		$this->assertNull($repository->getColumnPHID('PHID-no-such-column'));
	}

}
