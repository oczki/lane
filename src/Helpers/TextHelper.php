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

	public static function randomString($length, $alphabet = null)
	{
		if ($alphabet === null)
		{
			$alphabet =
				'0123456789_-' .
				'abcdefghijklmnopqrstuvwxyz' .
				'ABCDEFGHJIKLMNOPQRSTUVWXYZ';
		}

		$alphabet = str_split($alphabet);
		if (empty($alphabet))
			throw new Exception('Alphabet is empty.');

		$out = '';
		for ($i = 0; $i < $length; $i ++)
			$out .= $alphabet[array_rand($alphabet)];
		return $out;
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
