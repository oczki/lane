<?php
class ApiHelper
{
	public static function canEdit($someUser)
	{
		$context = \Chibi\Registry::getContext();
		$apiUser = Api::getApiUser();

		return
			$apiUser and
			$someUser and
			$someUser->id == $apiUser->id;
	}

	public static function canShowList($someList)
	{
		return $someList->visible or self::canEdit(ListService::getOwner($someList));
	}
}
