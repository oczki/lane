<?php
class Assets
{
	public static function addScript($path)
	{
		\Chibi\Util\Assets::addScript(\Chibi\Util\Url::isAbsolute($path) ? $path : '/js/' . $path);
	}

	public static function addStylesheet($path)
	{
		\Chibi\Util\Assets::addStylesheet(\Chibi\Util\Url::isAbsolute($path) ? $path : '/css/' . $path);
	}

	public static function setTitle($title)
	{
		\Chibi\Util\Assets::setTitle($title);
	}
}
