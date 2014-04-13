<?php
class ListService
{
	public static function getFilteredLists(ListFilter $listFilter)
	{
		return ListDao::getFilteredLists($listFilter);
	}

	public static function getByUser(UserEntity $user)
	{
		return self::getByUserId($user->id);
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

	public static function saveOrUpdate(ListEntity $list)
	{
		$list->lastUpdate = time();
		return ListDao::saveOrUpdate($list);
	}

	public static function delete(ListEntity $list)
	{
		return ListDao::delete($list);
	}

	public static function getColumn(ListEntity $list, $columnIndex)
	{
		return isset($list->content->columns[$columnIndex])
			? $list->content->columns[$columnIndex]
			: null;
	}

	public static function getColumns(ListEntity $list)
	{
		return $list->content->columns;
	}

	public static function getRows(ListEntity $list)
	{
		return $list->content->rows;
	}

	public static function getCells(ListRow $listRow)
	{
		return $listRow->content;
	}


	public static function getOwner(ListEntity $list)
	{
		return UserService::getById($list->userId);
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

		$baseUrlName = TextCaseConverter::convert($list->name,
			TextCaseConverter::BLANK_CASE,
			TextCaseConverter::SNAKE_CASE);

		//very important - strip all insecure characters
		$baseUrlName = preg_replace('/\W/u', '_', $baseUrlName);

		$index = 1;
		$urlName = $baseUrlName;
		do
		{
			$continue = false;
			foreach ($lists as $otherList)
			{
				if ($otherList->urlName == $urlName)
				{
					$urlName = $baseUrlName . $index;
					++ $index;
					$continue = true;
					break;
				}
			}
		}
		while ($continue);

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

	private static function checkColumnIndex(ListEntity $list, &$index)
	{
		$index = intval($index);
		if ($index < 0 or $index >= count($list->content->columns))
			throw new Exception('Invalid column index.');
	}

	private static function checkRowIndex(ListEntity $list, &$index)
	{
		$index = intval($index);
		if ($index < 0 or $index >= count($list->content->rows))
			throw new Exception('Invalid row index.');
	}

	public static function setLastViewedList(ListEntity $list)
	{
		$_SESSION['last-viewed-list'] = $list;
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

	public static function unserialize($jsonText)
	{
		try
		{
			$json = json_decode($jsonText);

			$list = new ListEntity();
			$list->name = $json->name;
			$list->visible = $json->visible;
			$list->content = new ListContent();

			$list->content->customCss = $json->content->customCss;
			$list->content->useCustomCss = $json->content->useCustomCss;
			$list->content->showRowIds = $json->content->showRowIds;
			$list->content->sortStyle = $json->content->sortStyle;

			foreach ($json->content->columns as $jsonColumn)
			{
				$column = new ListColumn();
				$column->name = $jsonColumn->name;
				$column->width = $jsonColumn->width;
				$column->align = $jsonColumn->align;
				$column->id = ++$list->content->lastContentId;
				$list->content->columns []= $column;
			}

			foreach ($json->content->rows as $jsonRow)
			{
				$row = new ListRow();
				$row->content = $jsonRow->content;
				$row->id = ++$list->content->lastContentId;
				$list->content->rows []= $row;
			}

			return $list;
		}
		catch (Exception $e)
		{
			throw new SimpleException('Error while decoding imported list (' . $e->getMessage() . '). Is this valid list?');
		}
	}

	public static function getNewPriority(UserEntity $owner)
	{
		$allListEntities = array_values(self::getByUser($owner));

		$maxPriority = array_reduce($allListEntities, function($max, $list)
		{
			return $list->priority > $max
				? $list->priority
				: $max;
		}, 0);

		return $maxPriority + 1;
	}

	public static function getColumnPos(ListEntity $list, $columnId)
	{
		foreach ($list->content->columns as $i => $column)
			if ($column->id == $columnId)
				return $i;

		throw new SimpleException('Invalid column ID: ' . $columnId . '.');
	}

	public static function getRowPos(ListEntity $list, $rowId)
	{
		foreach ($list->content->rows as $i => $row)
			if ($row->id == $rowId)
				return $i;

		throw new SimpleException('Invalid row ID: ' . $rowId . '.');
	}

	public static function validateListName($name)
	{
		$validator = new Validator($name, 'list name');
		$validator->checkMinLength(1);
		$validator->checkMaxLength(20);
	}

	public static function validateColumnName($name)
	{
		$validator = new Validator($name, 'column name');
		$validator->checkMinLength(1);
		$validator->checkMaxLength(100);
	}

	public static function validateCellContent($content)
	{
		$validator = new Validator($content, 'cell text');
		$validator->checkMaxLength(200);
	}

	public static function validateContentId(ListEntity $list, $id)
	{
		$id = intval($id);
		if ($id <= $list->content->lastContentId)
			throw new ValidationException('Invalid content ID or it was already used.');
	}
}
