<?php
class InputHelper
{
	public static function getPost($key)
	{
		return isset($_POST[$key])
			? $_POST[$key]
			: null;
	}
}
