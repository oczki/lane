<?php
use \Chibi\Database as Database;

class Api
{
	protected static $forcedUser = null;
	protected static $jobId = 0;

	public static function serializeJobHtml(AbstractJob $job)
	{
		++ self::$jobId;

		$map = ['name' => $job->getName(), 'args' => []];
		foreach ($job->getArguments() as $key => $value)
			$map['args'][$key] = $value;

		$fullMap = ['jobs' => [self::$jobId => $map]];

		$flattened = [];
		$walk = function($input, $prefix = '') use (&$walk, &$flattened)
		{
			foreach ($input as $key => $value)
			{
				$fullKey = !empty($prefix)
					? $prefix . '[' . $key . ']'
					: $key;

				if (is_array($value))
					$walk($value, $fullKey);
				else
					$flattened[$fullKey] = $value;
			}
		};
		$walk($fullMap);

		$html = '';
		foreach ($flattened as $key => $value)
			$html .= HtmlHelper::hiddenInputTag($key, ['value' => $value], true);

		return $html;
	}

	public static function jobFactory($jobName, array $jobArgs = [])
	{
		$className = sprintf('%s%s',
			TextCaseConverter::convert(
				$jobName,
				TextCaseConverter::SPINAL_CASE,
				TextCaseConverter::UPPER_CAMEL_CASE),
			'Job');

		try
		{
			$class = new ReflectionClass($className);
		}
		catch (Exception $e)
		{
			throw new SimpleException('Invalid job name: ' . $jobName . '.');
		}

		return $class->newInstanceArgs([$jobArgs]);
	}

	public static function forceSetApiUser($user)
	{
		self::$forcedUser = $user;
	}

	public static function getApiUser()
	{
		return self::$forcedUser ?: Auth::getLoggedInUser();
	}

	public static function getUrl()
	{
		return \Chibi\Router::linkTo(['ApiController', 'runAction']);
	}

	public static function run($jobs)
	{
		$jobs = is_array($jobs) ? $jobs : [$jobs];
		$statuses = [];
		Database::transaction(function() use ($jobs, &$statuses)
		{
			foreach ($jobs as $job)
			{
				if ($job->requiresAuthentication() and !self::getApiUser())
					throw new ValidationException('Not authorized.');

				$statuses []= $job->execute();
			}
		});

		return $statuses;
	}
}
