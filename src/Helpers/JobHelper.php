<?php
class JobHelper
{
	public static function getJobExecutorUrl()
	{
		$context = \Chibi\Registry::getContext();
		if (!$context->isLoggedIn)
			return null;
		return \Chibi\UrlHelper::route('user', 'exec', ['userName' => $context->userLogged->name]);
	}

	private static $jobId = 0;
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

	public static function factory($jobName, array $jobArgs = [])
	{
		$className = sprintf('%s%s',
			TextHelper::convertCase(
				$jobName,
				TextHelper::SPINAL_CASE,
				TextHelper::UPPER_CAMEL_CASE),
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
}
