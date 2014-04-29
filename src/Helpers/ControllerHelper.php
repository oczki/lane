<?php
class ControllerHelper
{
	public static function attachUser($userName = false)
	{
		$context = getContext();
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
		$context = getContext();
		if ($userName)
			$user = UserService::getByName($userName);
		elseif (Auth::isLoggedIn() and Auth::getLoggedInUser())
			$user = Auth::getLoggedInUser();
		else
			$user = null;

		if (!$user)
			throw new InvalidUserException($userName);

		$job = Api::jobFactory('get-lists', ['user-name' => $user->name]);

		$context->lists = $job->getLists();
	}

	public static function markReturn($linkText = null, $link = null)
	{
		$context = getContext();
		if (isset($context->returnLinkText))
			return;
		$context->returnLinkText = $linkText ?: 'Return to lane';
		$context->returnLink = $link ?: \Chibi\Router::linkTo(['IndexController', 'indexAction']);
	}

	public static function forward($url)
	{
		\Chibi\Util\Headers::setCode(303);
		\Chibi\Util\Url::forward($url);
		exit;
	}
}
