<?php
class HtmlHelper
{
	public static function tag($tag, array $params = [], $selfClose = false)
	{
		$html = '<' . $tag;
		foreach ($params as $k => $v)
			$html .= ' ' . $k . '="' . $v . '"';
		if ($selfClose)
			$html .= '/';
		$html .= '>';
		return $html;
	}

	public static function tagClose($tag)
	{
		return '</' . $tag . '>';
	}

	public static function labelDecorator($text, $inputHtml)
	{
		$html = self::tag('div', ['class' => 'input-row'], false);

		if (strpos($inputHtml, 'checkbox') !== false)
		{
			$html .= self::tag('label', [], false);
			$html .= self::tagClose('label');
			$html .= $inputHtml;

			$html .= self::tag('label', ['class' => 'checkbox'], false);
			$html .= $text;
			$html .= self::tagClose('label');
		}
		else
		{
			$html .= self::tag('label', [], false);
			if (!empty($text))
				$html .= rtrim(trim($text), ':') . ':';
			$html .= self::tagClose('label');

			$html .= $inputHtml;
		}

		$html .= self::tagClose('div');
		return $html;
	}

	public static function inputTag($type, $name, array $params = [])
	{
		$otherParams = [];
		$otherParams['type'] = $type;
		if ($name !== null)
		{
			$otherParams['name'] = $name;
			$otherParams['value'] = InputHelper::getPost($name);
		}
		return self::tag('input', array_merge($otherParams, $params), true);
	}

	public static function textInputTag($name, array $params = [])
	{
		return self::inputTag('text', $name, $params);
	}

	public static function passwordInputTag($name, array $params = [])
	{
		return self::inputTag('password', $name, $params);
	}

	public static function submitInputTag($text, array $params = [])
	{
		return self::inputTag('submit', null, array_merge(['value' => $text], $params));
	}

	public static function hiddenInputTag($name, array $params = [])
	{
		return self::inputTag('hidden', $name, $params);
	}

	public static function checkboxInputTag($name, $checked = false, array $params = [])
	{
		$otherParams = [];
		$otherParams['value'] = '1';
		if ($checked)
			$otherParams['checked'] = 'checked';

		return
			self::hiddenInputTag($name, ['value' => '0']) .
			self::inputTag('checkbox', $name, array_merge($otherParams, $params), true);
	}
}
