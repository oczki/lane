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

	public static function attachLists()
	{
		$context = \Chibi\Registry::getContext();
		$user = $context->user;
		if (empty($user))
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

	public static function setLayout()
	{
		$context = \Chibi\Registry::getContext();
		$context->allowIndexing = false;
		$context->layoutName = 'layout-bare';
	}

	public static function runJobExecutorForCurrentContext()
	{
		$context = \Chibi\Registry::getContext();

		if (!isset($context->canEdit) or !$context->canEdit)
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
