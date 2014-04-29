<?php
class ApiController
{
	public static function getJobsFromInput()
	{
		$jobs = [];
		$jobTexts = InputHelper::getPost('jobs');
		if ($jobTexts === null)
			$jobTexts = [];
		foreach ($jobTexts as $jobArray)
		{
			$jobName = $jobArray['name'];
			$jobArgs = isset($jobArray['args']) ? $jobArray['args'] : [];
			$job = Api::jobFactory($jobName, $jobArgs);
			$jobs []= $job;
		}
		return $jobs;
	}

	public function runAction()
	{
		$context = getContext();
		$context->layoutName = 'layout-json';
		$context->viewName = 'messages';
		$context->json = [];
		if (!$context->isSubmit)
			return;

		try
		{
			if (Auth::isLoggedIn())
				$user = Auth::getLoggedInUser();
			elseif (InputHelper::getPost('user'))
				$user = Auth::login(InputHelper::getPost('user'), InputHelper::getPost('pass'), false);
			else
				$user = Auth::loginFromDigest();

			$jobs = self::getJobsFromInput();
			if (empty($jobs))
				throw new SimpleException('No jobs to execute.');

			$statuses = Api::run($jobs);
			$context->json['status'] = $statuses;
		}
		catch (Exception $e)
		{
			if (\Chibi\Util\Headers::getCode() != 401)
				\Chibi\Util\Headers::setCode(400);
			$context->json['error'] = $e->getMessage();
		}
	}
}
