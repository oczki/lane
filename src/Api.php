<?php
use \Chibi\Database as Database;

class Api
{
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

	public static function getUrl()
	{
		return \Chibi\UrlHelper::route('api', 'run');
	}

	public static function run($jobs, $owner, $skipCheck = false)
	{
		if (!$skipCheck)
		{
			if (!$owner or !ControllerHelper::canEditData($owner))
				throw new UnprivilegedOperationException();
		}

		$jobs = is_array($jobs) ? $jobs : [$jobs];
		$statuses = [];
		Database::transaction(function() use ($jobs, $owner, &$statuses)
		{
			foreach ($jobs as $job)
			{
				$statuses []= $job->execute($owner);
			}
		});

		return $statuses;
	}
}
