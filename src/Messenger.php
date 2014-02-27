<?php
class Messenger
{
	private static $messages = [];

	public static function getMessages()
	{
		return static::$messages;
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
		self::$messages []= $message;
	}
}

class Message
{
	const MESSAGE_WARNING = 'warning';
	const MESSAGE_ERROR = 'error';
	const MESSAGE_SUCCESS = 'success';
	const MESSAGE_INFO = 'info';

	protected $messageType;
	protected $messageText;

	public function __construct($messageType, $messageText)
	{
		$this->messageType = $messageType;
		$this->messageText = $messageText;
	}

	public function getType()
	{
		return $this->messageType;
	}

	public function getText()
	{
		return $this->messageText;
	}
}
