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
		$job = new ListAddJob('New blank list', true);
		JobExecutor::execute($job, $user);

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
		$listEntity->lastUpdate = time();
		return ListDao::saveOrUpdate($listEntity);
	}

	public static function delete(ListEntity $listEntity)
	{
		return ListDao::delete($listEntity);
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
