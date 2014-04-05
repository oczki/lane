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
		return self::getFilteredLists($filter);
	}

	public static function getByUrlName(UserEntity $owner, $urlName)
	{
		$filter = new ListFilter();
		$filter->userId = $owner->id;
		$filter->urlName = $urlName;
		$lists = self::getFilteredLists($filter);
		if (empty($lists))
			return null;
		$list = reset($lists);
		return $list;
	}

	public static function saveOrUpdate(ListEntity $listEntity)
	{
		$listEntity->lastUpdate = time();
		return ListDao::saveOrUpdate($listEntity);
	}

	public static function delete(ListEntity $listEntity)
	{
		return ListDao::delete($listEntity);
	}

	public static function getColumn(ListEntity $listEntity, $columnIndex)
	{
		return isset($listEntity->content->columns[$columnIndex])
			? $listEntity->content->columns[$columnIndex]
			: null;
	}

	public static function getColumns(ListEntity $listEntity)
	{
		return $listEntity->content->columns;
	}

	public static function getRows(ListEntity $listEntity)
	{
		return $listEntity->content->rows;
	}

	public static function getCells(ListRow $listRow)
	{
		return $listRow->content;
	}


	public static function getOwner(ListEntity $listEntity)
	{
		return UserService::getById($listEntity->userId);
	}

	public static function getColumnClasses(ListColumn $column)
	{
		$alignments = [];
		$alignments[ListColumn::ALIGN_LEFT] = 'col-left';
		$alignments[ListColumn::ALIGN_CENTER] = 'col-center';
		$alignments[ListColumn::ALIGN_RIGHT] = 'col-right';

		$classes = [];
		$classes []= $alignments[$column->align];

		return $classes;
	}

	public static function forgeUrlName(ListEntity $list)
	{
		$lists = self::getByUserId($list->userId);
		$lists = array_filter($lists, function($otherList) use ($list)
		{
			return $list->id != $otherList->id;
		});

		$baseUrlName = TextHelper::convertCase($list->name,
			TextHelper::BLANK_CASE,
			TextHelper::SNAKE_CASE);

		//very important - strip all insecure characters
		$baseUrlName = preg_replace('/\W/u', '_', $baseUrlName);

		$urlName = $baseUrlName;
		do
		{
			$index = 1;
			$found = true;
			foreach ($lists as $otherList)
			{
				if ($otherList->urlName == $urlName)
				{
					$urlName = $baseUrlName . $index;
					++ $index;
					$found = false;
				}
			}
		}
		while (!$found);

		return $urlName;
	}

	public static function getPossibleColumnAlign()
	{
		return
		[
			ListColumn::ALIGN_LEFT,
			ListColumn::ALIGN_CENTER,
			ListColumn::ALIGN_RIGHT,
		];
	}

	private static function checkColumnIndex(ListEntity $listEntity, &$index)
	{
		$index = intval($index);
		if ($index < 0 or $index >= count($listEntity->content->columns))
			throw new Exception('Invalid column index.');
	}

	private static function checkRowIndex(ListEntity $listEntity, &$index)
	{
		$index = intval($index);
		if ($index < 0 or $index >= count($listEntity->content->rows))
			throw new Exception('Invalid row index.');
	}

	public static function setLastViewedList(ListEntity $listEntity)
	{
		$_SESSION['last-viewed-list'] = $listEntity;
	}

	public static function getLastViewedList()
	{
		return isset($_SESSION['last-viewed-list'])
			? $_SESSION['last-viewed-list']
			: null;
	}

	public static function serialize(ListEntity $list)
	{
		$json = (array) $list;

		$illegalKeys = ['id', 'userId', 'urlName', 'priority', 'lastUpdate'];
		foreach ($illegalKeys as $key)
		{
			assert(array_key_exists($key, $json));
			unset($json[$key]);
		}
		return json_encode($json, JSON_PRETTY_PRINT |  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	}
}
