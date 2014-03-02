<?php
class TextHelper
{
	public static function pluralize($number, $singularUnitText)
	{
		if ($number == 1)
			return '1 ' . $singularUnitText;
		else
			return $number . ' ' . $singularUnitText . 's';
	}

	public static function randomString($alphabet, $length)
	{
		$alphabet = str_split($alphabet);
		if (empty($alphabet))
			throw new Exception('Alphabet is empty.');

		$out = '';
		for ($i = 0; $i < $length; $i ++)
			$out .= $alphabet[array_rand($alphabet)];
		return $out;
	}

	const SNAKE_CASE = 1; //snake_case
	const SPINAL_CASE = 2; //train-case
	const TRAIN_CASE = 3; //Train-Case
	const CAMEL_CASE = 4; //CamelCase
	const UPPER_CAMEL_CASE = 5; //CamelCase
	const LOWER_CAMEL_CASE = 6; //camelCase
	public static function convertCase($text, $from, $to)
	{
		if ($from == self::CAMEL_CASE or $from == self::LOWER_CAMEL_CASE or $from == self::UPPER_CAMEL_CASE)
			$trans = preg_split('/(?<=[a-z])(?=[A-Z])/', $text);
		elseif ($from == self::SNAKE_CASE)
			$trans = explode('_', $text);
		elseif ($from == self::TRAIN_CASE or $from == self::SPINAL_CASE)
			$trans = explode('-', $text);
		else
			throw new Exception('Unknown conversion source');

		if ($to == self::SNAKE_CASE)
			return join('_', array_map('strtolower', $trans));
		elseif ($to == self::SPINAL_CASE)
			return join('-', array_map('strtolower', $trans));
		elseif ($to == self::TRAIN_CASE)
			return join('-', array_map('ucfirst', array_map('strtolower', $trans)));
		elseif ($to == self::CAMEL_CASE or $to == self::UPPER_CAMEL_CASE)
			return join('', array_map('ucfirst', array_map('strtolower', $trans)));
		elseif ($to == self::LOWER_CAMEL_CASE)
			return lcfirst(join('', array_map('ucfirst', array_map('strtolower', $trans))));
		else
			throw new Exception('Unknown conversion target');
	}

	public static function keepWhiteSpace($text)
	{
		$text = str_replace(
			["\t", "\r", "\n"],
			['&#9;', '&#13;', '&#10;'],
			$text);
		$text = str_replace('  ', '&nbsp; ', $text);
		return $text;
	}

	public static function formatTimeDelta($delta)
	{
		if ($delta === null)
			return 'Unknown';

		$delta = max(0, $delta);

		$mul = 60;
		if ($delta < $mul)
			return 'just now';
		if ($delta < $mul * 2)
			return 'a minute ago';

		$prevMul = $mul; $mul *= 60;
		if ($delta < $mul)
			return round($delta / $prevMul) . ' minutes ago';
		if ($delta < $mul * 2)
			return 'an hour ago';

		$prevMul = $mul; $mul *= 24;
		if ($delta < $mul)
			return round($delta / $prevMul) . ' hours ago';
		if ($delta < $mul * 2)
			return 'yesterday';

		$prevMul = $mul; $mul *= 30.42;
		if ($delta < $mul)
			return round($delta / $prevMul) . ' days ago';
		if ($delta < $mul * 2)
			return 'a month ago';

		$prevMul = $mul; $mul *= 12;
		if ($delta < $mul)
			return round($delta / $prevMul) . ' months ago';
		if ($delta < $mul * 2)
			return 'a year ago';

		return round($delta / $prevMul) . ' years ago';
	}
}
