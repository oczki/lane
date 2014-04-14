<?php
use Chibi\Sql as Sql;
use Chibi\Database as Database;

class Upgrade5 implements IUpgrade
{
	public static function execute()
	{
		foreach (UserService::getFilteredUsers(new UserFilter()) as $user)
		{
			$user->settings = new UserSettings();
			$user->settings->showGuestsLastUpdate = true;
			UserService::saveOrUpdate($user);
		}
	}
}
