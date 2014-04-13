<?php
use Chibi\Database as Database;

class UserService
{
	public static function hashPassword(UserEntity $user, $password)
	{
		return md5($user->name . ':lane:' . $password);
	}

	public static function getFilteredUsers(UserFilter $userFilter)
	{
		return UserDao::getFilteredUsers($userFilter);
	}

	public static function getFilteredUser(UserFilter $userFilter)
	{
		$users = self::getFilteredUsers($userFilter);
		if (empty($users))
			return null;
		$user = reset($users);
		return $user;
	}

	public static function getById($id)
	{
		$filter = new UserFilter();
		$filter->id = $id;
		return self::getFilteredUser($filter);
	}

	public static function getByName($name)
	{
		$filter = new UserFilter();
		$filter->name = $name;
		return self::getFilteredUser($filter);
	}

	public static function getByEmail($email)
	{
		$filter = new UserFilter();
		$filter->email = $email;
		return self::getFilteredUser($filter);
	}

	public static function saveOrUpdate(UserEntity $userEntity)
	{
		return UserDao::saveOrUpdate($userEntity);
	}

	public static function delete(UserEntity $userEntity)
	{
		Database::transaction(function() use ($userEntity)
		{
			foreach (ListService::getByUserId($userEntity->id) as $listEntity)
				ListService::delete($listEntity);
			return UserDao::delete($userEntity);
		});
	}
}
