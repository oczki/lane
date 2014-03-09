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

		$mul = count($listEntity->content->columns);
		$mul /= ($mul - 1);

		self::delete($listEntity->content->columns, $pos);

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
