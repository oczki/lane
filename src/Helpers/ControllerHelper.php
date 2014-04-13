<?php
class ControllerHelper
{
	public static function attachUser($userName = false)
	{
		$context = \Chibi\Registry::getContext();
		if ($userName)
			$user = UserService::getByName($userName);
		elseif (Auth::isLoggedIn() and Auth::getLoggedInUser())
			$user = Auth::getLoggedInUser();
		else
			$user = null;

		$context->user = $user;
	}

	public static function attachLists($userName)
	{
		$context = \Chibi\Registry::getContext();
		if ($userName)
			$user = UserService::getByName($userName);
		elseif (Auth::isLoggedIn() and Auth::getLoggedInUser())
			$user = Auth::getLoggedInUser();
		else
			$user = null;

		if (!$user)
			throw new InvalidUserException($userName);

		$job = Api::jobFactory('show-lists', ['user-name' => $user->name]);

		$context->lists = $job->getLists();
	}
}
