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

	/**
	* @route /api
	* @route /api/
	*/
	public function runAction()
	{
		$this->context->layoutName = 'layout-json';
		$this->context->viewName = 'messages';
		$this->context->json = [];

		try
		{
			if (!$this->context->isSubmit)
				throw new SimpleException('Not a POST request.');

			if (Auth::isLoggedIn())
				$user = Auth::getLoggedInUser();
			elseif (InputHelper::getPost('user'))
				$user = Auth::login(InputHelper::getPost('user'), InputHelper::getPost('pass'), false);
			else
				$user = Auth::loginFromDigest();

			if (!$user)
				throw new ValidationException('Not authorized.');


			$jobs = self::getJobsFromInput();
			if (empty($jobs))
				throw new SimpleException('No jobs to execute.');

			$statuses = Api::run($jobs, $user);
			$this->context->json['status'] = $statuses;
		}
		catch (Exception $e)
		{
			if (\Chibi\HeadersHelper::getCode() != 401)
				\Chibi\HeadersHelper::setCode(400);
			$this->context->json['error'] = $e->getMessage();
		}
	}
}
