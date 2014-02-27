<?php
use Chibi\Sql;
use Chibi\Database as Database;

class UpgradeDao
{
	public static function isExecuted($upgradeNumber)
	{
		$stmt = new Sql\SelectStatement();
		$stmt->setTable('executed_upgrades');
		$stmt->setCriterion(new SQl\EqualsFunctor(
			'upgrade_number',
			new Sql\Binding($upgradeNumber)));
		$row = Database::fetchOne($stmt);
		return !empty($row);
	}

	public static function markAsExecuted($upgradeNumber, $executed)
	{
		if (!$executed and self::isExecuted($upgradeNumber))
		{
			$stmt = new Sql\DeleteStatement();
			$stmt->setTable('executed_upgrades');
			$stmt->setCriterion(new Sql\EqualsFunctor(
				'upgrade_number',
				new Sql\Binding($upgradeNumber)));

			Database::exec($stmt);
		}
		else if ($executed and !self::isExecuted($upgradeNumber))
		{
			$stmt = new Sql\InsertStatement();
			$stmt->setTable('executed_upgrades');
			$stmt->setColumn('upgrade_number', new Sql\Binding($upgradeNumber));
			Database::exec($stmt);
		}
	}
}
