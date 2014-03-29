<?php
use \Chibi\Database as Database;

class JobExecutor
{
	public static function execute($jobs, UserEntity $user)
	{
		$jobs = is_array($jobs) ? $jobs : [$jobs];
		Database::transaction(function() use ($jobs, $user)
		{
			foreach ($jobs as $job)
			{
				$job->execute($user);
			}
		});
	}
}
