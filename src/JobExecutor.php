<?php
use \Chibi\Database as Database;

class JobExecutor
{
	public static function execute($jobs)
	{
		$jobs = is_array($jobs) ? $jobs : [$jobs];
		Database::transaction(function() use ($jobs)
		{
			foreach ($jobs as $job)
			{
				$job->execute();
			}
		});
	}
}
