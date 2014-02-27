<?php
use Chibi\Sql as Sql;
use Chibi\Database as Database;

class Upgrade1 implements IUpgrade
{
	public static function execute()
	{
		$stmt = new Sql\CreateTableStatement();
		$stmt->setTable('executed_upgrades');
		$stmt->addColumn('upgrade_number', Sql\CreateTableStatement::TYPE_INTEGER);
		$stmt->setPrimaryKey('upgrade_number');
		Database::exec($stmt);
	}
}
