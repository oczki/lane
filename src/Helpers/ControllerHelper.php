<?php
class ControllerHelper
{
	public static function attachUser($userName = false)
	{
		$context = \Chibi\Registry::getContext();
		if ($userName)
			$user = UserService::getByName($userName);
		elseif ($context->isLoggedIn and $context->userLogged)
			$user = $context->userLogged;
		else
			$user = null;

		$context->user = $user;
		$context->canEdit =
			$context->isLoggedIn and
			$context->user and
			$context->user->id == $context->userLogged->id;
	}

	public static function attachList()
	{
		$context = \Chibi\Registry::getContext();
		$user = $context->user;
		if (empty($user))
			throw new SimpleException('Unknown user.');
		$context->lists = ListService::getByUserId($user->id);
	}

	public static function setLayout()
	{
		$context = \Chibi\Registry::getContext();
		$context->allowIndexing = false;
		$context->layoutName = 'layout-bare';
	}
}
