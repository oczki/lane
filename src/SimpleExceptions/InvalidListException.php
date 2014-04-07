<?php
class InvalidListException extends SimpleException
{
	const REASON_NOT_FOUND = 1;
	const REASON_PRIVATE = 2;

	public function __construct($id, $reason = self::REASON_NOT_FOUND)
	{
		if ($id !== null and $reason == self::REASON_NOT_FOUND)
			parent::__construct('List with ID = ' . $id . ' wasn\'t found.');

		elseif ($id !== null and $reason == self::REASON_PRIVATE)
			parent::__construct('List with ID = ' . $id . ' isn\'t available for public.');

		elseif ($id === null and $reason == self::REASON_NOT_FOUND)
			parent::__construct('Looks like user has no lists.');

		elseif ($id === null and $reason == self::REASON_PRIVATE)
			parent::__construct('Looks like all of user\'s lists are private.');

		else
			parent::__construct('Invalid list.');
	}
}
