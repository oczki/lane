<?php
class InputHelper
{
	public static function getPost($key)
	{
		return isset($_POST[$key])
			? $_POST[$key]
			: null;
	}

	public static function getFile($key)
	{
		return isset($_FILES[$key])
			? $_FILES[$key]
			: null;
	}
}
