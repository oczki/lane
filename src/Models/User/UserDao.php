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

		if ($userFilter->email !== null)
		{
			$stmt->getCriterion()->add(
				new Sql\EqualsFunctor('email', new Sql\Binding($userFilter->email)));
		}

		$rows = Database::fetchAll($stmt);
		$userEntities = DaoHelper::transformEntities('UserEntity', $rows);

		foreach ($rows as $row)
		{
			$user = &$userEntities[$row['id']];
			$user->settings = self::deserializeSettings($user->settings);
			unset($user);
		}

		return $userEntities;
	}

	public static function saveOrUpdate(UserEntity $user)
	{
		$user->settings = self::serializeSettings($user->settings);
		try
		{
			$ret = DaoHelper::saveOrUpdate('user', $user);
		}
		finally
		{
			$user->settings = self::deserializeSettings($user->settings);
		}
		return $ret;
	}

	public static function delete(UserEntity $user)
	{
		return DaoHelper::delete('user', $user);
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
