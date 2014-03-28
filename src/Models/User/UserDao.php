<?php
use Chibi\Sql;
use Chibi\Database as Database;

class UserDao
{
	public static function getFilteredUsers(UserFilter $userFilter)
	{
		$stmt = new Sql\SelectStatement();
		$stmt->setColumn('user.*');
		$stmt->setTable('user');
		$stmt->setCriterion(new Sql\ConjunctionFunctor());

		if ($userFilter->id !== null)
		{
			$stmt->getCriterion()->add(
				new Sql\EqualsFunctor('id', new Sql\Binding($userFilter->id)));
		}

		if ($userFilter->name !== null)
		{
			$stmt->getCriterion()->add(
				new Sql\EqualsFunctor('name', new Sql\Binding($userFilter->name)));
		}

		$rows = Database::fetchAll($stmt);
		$userEntities = DaoHelper::transformEntities('UserEntity', $rows);

		foreach ($rows as $row)
		{
			$userEntity = &$userEntities[$row['id']];
			$userEntity->settings = self::deserializeSettings($userEntity->settings);
			unset($userEntity);
		}

		return $userEntities;
	}

	public static function saveOrUpdate(UserEntity $userEntity)
	{
		$userEntity->settings = self::serializeSettings($userEntity->settings);
		try
		{
			$ret = DaoHelper::saveOrUpdate('user', $userEntity);
		}
		finally
		{
			$userEntity->settings = self::deserializeSettings($userEntity->settings);
		}
		return $ret;
	}

	public static function delete(UserEntity $userEntity)
	{
		return DaoHelper::delete('user', $userEntity);
	}

	private static function serializeSettings($settings)
	{
		return gzdeflate(serialize($settings));
	}

	private static function deserializeSettings($settings)
	{
		if (!$settings)
			return null;
		return unserialize(gzinflate($settings));
	}
}
