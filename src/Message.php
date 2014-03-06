<?php
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
