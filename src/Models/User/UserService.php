<?php
class UserService
{
	public static function hashPassword($password)
	{
		return sha1($password);
	}

	public static function getFilteredUsers(UserFilter $userFilter)
	{
		return UserDao::getFilteredUsers($userFilter);
	}

	public static function getById($id)
	{
		$filter = new UserFilter();
		$filter->id = $id;
		$users = self::getFilteredUsers($filter);
		if (empty($users))
			return null;
		$user = reset($users);
		return $user;
	}

	public static function getByName($name)
	{
		$filter = new UserFilter();
		$filter->name = $name;
		$users = self::getFilteredUsers($filter);
		if (empty($users))
			return null;
		$user = reset($users);
		return $user;
	}

	public static function saveOrUpdate(UserEntity $userEntity)
	{
		return UserDao::saveOrUpdate($userEntity);
	}

	public static function delete(UserEntity $userEntity)
	{
		return UserDao::delete($userEntity);
	}
}
