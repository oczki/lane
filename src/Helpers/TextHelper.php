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
}
