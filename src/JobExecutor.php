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

	public static function parse($jobText)
	{
		if (!isset($jobText['name']))
			throw new Exception('Job name not available.');

		$jobName = $jobText['name'];
		$jobArgs = !empty($jobText['args'])
			? $jobText['args']
			: [];

		$className = sprintf('%s%s',
			TextHelper::convertCase(
				$jobName,
				TextHelper::TRAIN_CASE,
				TextHelper::UPPER_CAMEL_CASE),
			'Job');

		try
		{
			$class = new ReflectionClass($className);
			$constructor = new ReflectionMethod($className, '__construct');
			$expectedArgumentCount = $constructor->getNumberOfRequiredParameters();
		}
		catch (Exception $e)
		{
			throw new SimpleException('Invalid job name.');
		}

		if (count($jobArgs) < $expectedArgumentCount)
		{
			throw new SimpleException(sprintf(
				'Too few job arguments (expected at least %d, got %d).',
				$expectedArgumentCount,
				count($jobArgs)));
		}

		return $class->newInstanceArgs($jobArgs);
	}
}
