<?php
class HtmlHelper
{
	public static function tag($tag, array $params = [], $selfClose = false)
	{
		$html = '<' . $tag;
		foreach ($params as $k => $v)
			$html .= ' ' . $k . '="' . htmlspecialchars($v) . '"';
		if ($selfClose)
			$html .= '/';
		$html .= '>';
		return $html;
	}

	public static function tagClose($tag)
	{
		return '</' . $tag . '>';
	}

	public static function labelDecorator($text, $inputHtml, $additionalText = '')
	{
		//stupid hacks, but if it works and it's only here...
		//add it for input inside so that labels can have for="..." attribute
		preg_match('/id="([a-zA-Z0-9_-]+)/i', $inputHtml, $matches);
		if (isset($matches[1]))
		{
			$id = $matches[1];
		}
		else
		{
			$id = self::makeUniqueId();
			$inputHtml = preg_replace('/\/?>$/', ' id="' . $id . '"\0', $inputHtml);
		}

		$html = self::tag('div', ['class' => 'input-row'], false);
		$html .= self::labelTag($text ? rtrim(trim($text), ':') . ':' : '', ['for' => $id]);
		$html .= $inputHtml;

		//additional label for checkboxes and radioboxes
		if (strpos($inputHtml, 'checkbox') !== false or
			strpos($inputHtml, 'radio') !== false)
		{
			$html .= self::labelTag($additionalText, ['class' => 'checkbox', 'for' => $id]);
		}

		$html .= self::tagClose('div');
		return $html;
	}

	public static function labelTag($text, array $params = [])
	{
		$html = self::tag('label', $params, false);
		$html .= $text;
		$html .= self::tagClose('label');
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

	public static function radioInputTag($name, $checked = false, array $params = [])
	{
		$otherParams = [];
		if ($checked)
			$otherParams['checked'] = 'checked';

		return self::inputTag('radio', $name, array_merge($otherParams, $params), true);
	}

	private static $usedIds = [];
	public static function makeUniqueId()
	{
		do
		{
			$id = 'x' . substr(md5(mt_rand() . microtime(true)), 0, 8);
		}
		while (in_array($id, self::$usedIds));
		self::$usedIds []= $id;
		return $id;
	}
}
