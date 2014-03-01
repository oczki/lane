<?php
class ListService
{
	public static function getFilteredLists(ListFilter $listFilter)
	{
		return ListDao::getFilteredLists($listFilter);
	}

	public static function getByUserId($userId)
	{
		$filter = new ListFilter();
		$filter->userId = $userId;
		$lists = self::getFilteredLists($filter);
		return $lists;
	}

	public static function getByUniqueId($uniqueId)
	{
		$filter = new ListFilter();
		$filter->uniqueId = $uniqueId;
		$lists = self::getFilteredLists($filter);
		if (empty($lists))
			return null;
		$list = reset($lists);
		return $list;
	}

	public static function saveOrUpdate(ListEntity $listEntity)
	{
		return ListDao::saveOrUpdate($listEntity);
	}

	public static function delete(ListEntity $listEntity)
	{
		return ListDao::delete($listEntity);
	}

	public static function createNewList(UserEntity $owner, $title)
	{
		$lists = self::getByUserId($owner->id);
		$maxPriority = array_reduce($lists, function($max, $list)
		{
			if ($list->priority > $max)
				$max = $list->priority;
		}, 0);

		$alpha =
			'0123456789_-' .
			'abcdefghijklmnopqrstuvwxyz' .
			'ABCDEFGHJIKLMNOPQRSTUVWXYZ';

		$listEntity = new ListEntity();
		$listEntity->userId = $owner->id;
		$listEntity->name = $title;
		$listEntity->priority = $maxPriority + 1;
		$listEntity->uniqueId = TextHelper::randomString($alpha, 32);
		return ListDao::saveOrUpdate($listEntity);
	}
}
