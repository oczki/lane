<?php
class Messenger
{
	public static function getMessages()
	{
		$ret = isset($_SESSION['messages'])
			? $_SESSION['messages']
			: [];

		unset($_SESSION['messages']);
		return $ret;
	}

	public static function warning($text)
	{
		self::addMessage(new Message(Message::MESSAGE_WARNING, $text));
	}

	public static function error($text)
	{
		self::addMessage(new Message(Message::MESSAGE_ERROR, $text));
	}

	public static function success($text)
	{
		self::addMessage(new Message(Message::MESSAGE_SUCCESS, $text));
	}

	public static function info($text)
	{
		self::addMessage(new Message(Message::MESSAGE_INFO, $text));
	}

	protected static function addMessage(Message $message)
	{
		if (!isset($_SESSION['messages']))
			$_SESSION['messages'] = [];

		$_SESSION['messages'] []= $message;
	}
}
