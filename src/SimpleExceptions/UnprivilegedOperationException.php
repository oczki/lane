<?php
class UnprivilegedOperationException extends SimpleException
{
	public function __construct()
	{
		parent::__construct('This operation is not permitted.');
	}
}
