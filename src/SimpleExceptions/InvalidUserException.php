<?php
class InvalidUserException extends SimpleException
{
	public function __construct($userName)
	{
		if ($userName !== null)
			parent::__construct('User with name = ' . $userName . ' wasn\'t found.');

		else
			parent::__construct('Invalid user.');
	}
}
