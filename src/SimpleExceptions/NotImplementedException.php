<?php
class NotImplementedException extends SimpleException
{
	public function __construct()
	{
		parent::__construct('Not implemented.');
	}
}
