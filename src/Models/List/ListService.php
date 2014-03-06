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
		if (!empty($lists))
			return $lists;

		$user = UserService::getById($userId);
		$list = self::createNewList($user, 'New blank list', true);
		return [$list];
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
		$listEntity->lastUpdate = time();
		return ListDao::saveOrUpdate($listEntity);
	}

	public static function delete(ListEntity $listEntity)
	{
		return ListDao::delete($listEntity);
	}

	public static function createNewList(UserEntity $owner, $title, $visible)
	{
		$filter = new ListFilter();
		$filter->userId = $owner->id;
		$lists = self::getFilteredLists($filter);

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
		$listEntity->visible = true;
		$listEntity->content = new ListContent();

		$column1 = new ListColumn();
		$column1->name = 'Example column 1';
		$column1->width = 70;
		$column1->align = ListColumn::ALIGN_LEFT;

		$column2 = new ListColumn();
		$column2->name = 'Example column 2';
		$column2->width = 30;
		$column2->align = ListColumn::ALIGN_LEFT;

		self::addColumn($listEntity, $column1);
		self::addColumn($listEntity, $column2);

		return self::saveOrUpdate($listEntity);
	}

	public static function addColumn(ListEntity $listEntity, ListColumn $listColumn)
	{
		$listEntity->content->columns []= $listColumn;
		return count($listEntity->content->columns) - 1;
	}

	public static function removeColumn(ListEntity $listEntity, $index)
	{
		self::checkColumnIndex($listEntity, $index);

		$listEntity->content->columns = array_splice($listEntity->content->columns, $index, 1);
		foreach ($listEntity->content->rows as $i => $row)
			$listEntity->content->rows[$i] = array_splice($row, $index, 1);
	}

	public static function addRow(ListEntity $listEntity, array $newRow = [])
	{
		if (empty($newRow))
			$newRow = array_fill(0, count($listEntity->content->columns), '');

		if (count($newRow) != count($listEntity->content->columns))
			throw new Exception('Invalid column count.');

		$listEntity->content->rows []= $newRow;
		return count($listEntity->content->rows) - 1;
	}

	public static function removeRow(ListEntity $listEntity, $index)
	{
		self::checkRowIndex($listEntity, $index);

		$listEntity->content->rows = array_splice($listEntity->content->rows, $i, 1);
	}

	public static function setCell(ListEntity $listEntity, $rowIndex, $columnIndex, $content)
	{
		self::checkRowIndex($listEntity, $rowIndex);
		self::checkColumnIndex($listEntity, $columnIndex);
		$listEntity->content->rows[$rowIndex][$columnIndex] = $content;
	}

	public static function swapColumns(ListEntity $listEntity, $columnIndex1, $columnIndex2)
	{
		self::checkColumnIndex($listEntity, $columnIndex1);
		self::checkColumnIndex($listEntity, $columnIndex2);

		$listEntity->content->columns = array_splice(
			$listEntity->content->columns,
			$columnIndex1,
			1,
			[$listEntity->content->columns[$columnIndex2]]);

		foreach ($listEntity->content->rows as $i => $row)
		{
			$listEntity->content->rows[$i] = array_splice(
				$row,
				$columnIndex1,
				1,
				[$row[$columnIndex2]]);
		}
	}

	public static function getRows(ListEntity $listEntity)
	{
		return $listEntity->content->rows;
	}

	public static function getColumns(ListEntity $listEntity)
	{
		return $listEntity->content->columns;
	}

	public static function getColumnModifiers(ListEntity $listEntity, $columnIndex)
	{
		self::checkColumnIndex($listEntity, $columnIndex);

		$listColumn = $listEntity->content->columns[$columnIndex];
		$modifiers = [];

		if ($listColumn->align == ListColumn::ALIGN_LEFT)
			$modifiers []= 'col-left';

		elseif ($listColumn->align == ListColumn::ALIGN_CENTER)
			$modifiers []= 'col-center';

		elseif ($listColumn->align == ListColumn::ALIGH_RIGHT)
			$modifiers []= 'col-right';

		return $modifiers;
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
}
