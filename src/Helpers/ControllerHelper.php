<?php
class ControllerHelper
{
	private static $privilegesRevoked = [];

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
			Auth::isLoggedIn() and
			Auth::getLoggedInUser() and
			$user->id == Auth::getLoggedInUser()->id;
	}

	public static function attachLists($userName)
	{
		$context = \Chibi\Registry::getContext();
		if ($userName)
			$user = UserService::getByName($userName);
		elseif (Auth::isLoggedIn() and Auth::getLoggedInUser())
			$user = Auth::getLoggedInUser();

		if (!$user)
			throw new InvalidUserException($userName);

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

	public static function getJobsFromInput()
	{
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
		return $jobs;
	}

	public static function executeJobsSafely($jobs, $user)
	{
		if (!isset($user) or !self::canEditData($user))
			throw new UnprivilegedOperationException();

		JobExecutor::execute($jobs, $user);

		Messenger::success(count($jobs) . ' jobs executed successfully.');
	}
}
