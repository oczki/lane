<?php
use Chibi\Sql as Sql;
use Chibi\Database as Database;

class Upgrade2 implements IUpgrade
{
	public static function execute()
	{
		$stmt = new Sql\CreateTableStatement();
		$stmt->setTable('user');
		$stmt->addColumn('id', Sql\CreateTableStatement::TYPE_INTEGER);
		$stmt->addColumn('name', Sql\CreateTableStatement::TYPE_VARCHAR, 20);
		$stmt->addColumn('pass_hash', Sql\CreateTableStatement::TYPE_VARCHAR, 32);
		$stmt->addColumn('email', Sql\CreateTableStatement::TYPE_VARCHAR, 100);
		$stmt->setPrimaryKey('id');
		$stmt->addCheckContraint(new Sql\NegationFunctor(new Sql\IsFunctor('name', new Sql\NullFunctor())));
		Database::exec($stmt);
	}
}
