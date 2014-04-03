<?php
class ControllerHelper
{
	private static $privilegesRevoked = [];

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
	}

	public static function revokePrivileges(UserEntity $user)
	{
		self::$privilegesRevoked[$user->id] = true;
	}

	public static function canEditData($user)
	{
		$context = \Chibi\Registry::getContext();

		return
			$user and
			!isset(self::$privilegesRevoked[$user->id]) and
			$context->isLoggedIn and
			$user->id == $context->userLogged->id;
	}

	public static function attachLists($userName)
	{
		$context = \Chibi\Registry::getContext();
		if ($userName)
			$user = UserService::getByName($userName);
		elseif ($context->isLoggedIn and $context->userLogged)
			$user = $context->userLogged;
		else
			throw new SimpleException('Unknown user.');

		$context->lists = ListService::getByUserId($user->id);

		if (empty($context->lists))
		{
			$job = new ListAddJob([
				'new-list-name' => 'New blank list',
				'new-list-visibility' => true]);

			JobExecutor::execute($job, $user);

			$context->lists = ListService::getByUserId($user->id);
		}
	}

	public static function runJobExecutorForCurrentContext()
	{
		$context = \Chibi\Registry::getContext();

		if (!isset($context->user) or !self::canEditData($context->user))
			throw new SimpleException('Cannot execute jobs for this user.');

		$jobs = [];
		$jobTexts = InputHelper::getPost('jobs');
		if ($jobTexts === null)
			$jobTexts = [];
		foreach ($jobTexts as $jobArray)
		{
			$jobName = $jobArray['name'];
			$jobArgs = $jobArray['args'];
			$job = JobHelper::factory($jobName, $jobArgs);
			$jobs []= $job;
		}

		JobExecutor::execute($jobs, $context->user);

		Messenger::success(count($jobs) . ' jobs executed successfully.');
	}
}
