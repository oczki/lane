<?php
abstract class ListJobHelper
{
	public static function getList($listId, UserEntity $owner)
	{
		$listEntity = ListService::getByUrlName($owner, $listId);

		if (empty($listEntity))
			throw new SimpleException('Invalid list ID: ' . $listId . '.');

		if ($listEntity->userId != $owner->id)
			throw new SimpleException('List owner ID doesn\'t match logged in user ID.');

		return $listEntity;
	}

	public static function getColumnPos(ListEntity $listEntity, $columnId)
	{
		foreach ($listEntity->content->columns as $i => $column)
			if ($column->id == $columnId)
				return $i;

		throw new SimpleException('Invalid column ID: ' . $columnId . '.');
	}

	public static function getRowPos(ListEntity $listEntity, $rowId)
	{
		foreach ($listEntity->content->rows as $i => $row)
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
}
