<?php
class ListSetColumnPosJob implements IJob
{
	private $listId;
	private $columnId;
	private $newPos;

	public function __construct($listId, $columnId, $newPos)
	{
		$this->listId = $listId;
		$this->columnId = $columnId;
		$this->newPos = intval($newPos);
	}

	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->listId, $owner);

		$newPos = $this->newPos;
		if ($newPos < 0 or $newPos >= count($listEntity->content->columns))
			throw new SimpleException('Invalid column target position: ' . $newPos . '.');

		$oldPos = ListJobHelper::getColumnPos($listEntity, $this->columnId);

		self::swap($listEntity->content->columns, $oldPos, $newPos);

		foreach ($listEntity->content->rows as $i => $row)
			self::swap($listEntity->content->rows[$i]->content, $oldPos, $newPos);

		ListService::saveOrUpdate($listEntity);
	}

	private static function swap(&$subject, $oldPos, $newPos)
	{
		$entity = $subject[$oldPos];
		array_splice($subject, $oldPos, 1);
		array_splice($subject, $newPos, 0, [$entity]);
	}
}
