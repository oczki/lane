<?php
use Chibi\Sql as Sql;
use Chibi\Database as Database;

class Upgrade4 implements IUpgrade
{
	public static function execute()
	{
		$stmt = new Sql\RawStatement('ALTER TABLE user ADD COLUMN settings BLOB');
		Database::exec($stmt);
	}
}
