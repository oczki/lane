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

		return DaoHelper::transformEntities('UserEntity', Database::fetchAll($stmt));
	}

	public static function saveOrUpdate(UserEntity $userEntity)
	{
		return DaoHelper::saveOrUpdate('user', $userEntity);
	}

	public static function delete(UserEntity $userEntity)
	{
		return DaoHelper::delete($userEntity);
	}
}
