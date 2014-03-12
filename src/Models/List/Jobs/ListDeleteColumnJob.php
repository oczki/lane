<?php
class ListDeleteColumnJob implements IJob
{
	private $listId;
	private $columnId;

	public function __construct($listId, $columnId)
	{
		$this->listId = $listId;
		$this->columnId = $columnId;
	}

	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->listId, $owner);
		$pos = ListJobHelper::getColumnPos($listEntity, $this->columnId);

		self::delete($listEntity->content->columns, $pos);

		$sum = 0;
		foreach ($listEntity->content->columns as $i => $column)
			$sum += $column->width;
		$mul = 100 / max(0, $sum);

		foreach ($listEntity->content->columns as $i => $column)
			$column->width *= $mul;

		foreach ($listEntity->content->rows as $i => $row)
			self::delete($listEntity->content->rows[$i]->content, $pos);

		ListService::saveOrUpdate($listEntity);
	}

	private static function delete(&$subject, $pos)
	{
		array_splice($subject, $pos, 1);
	}
}
