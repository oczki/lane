<?php
class StrictPropertyObject
{
	public function __set($key, $value)
	{
		throw new Exception(sprintf('Property %s::%s does not exist.',
			get_called_class(),
			$key));
	}

	public function __get($key)
	{
		throw new Exception(sprintf('Property %s::%s does not exist.',
			get_called_class(),
			$key));
	}
}
