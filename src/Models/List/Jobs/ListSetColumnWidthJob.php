<?php
class ListSetColumnWidthJob implements IJob
{
	private $listId;
	private $columnId;
	private $newWidth;

	public function __construct($listId, $columnId, $newWidth)
	{
		$this->listId = $listId;
		$this->columnId = $columnId;
		$this->newWidth = $newWidth;
	}

	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->listId, $owner);
		$pos = ListJobHelper::getColumnPos($listEntity, $this->columnId);

		$listEntity->content->columns[$pos]->width = $this->newWidth;

		$totalSum = 0;
		foreach ($listEntity->content->columns as $otherPos => $column)
			$totalSum += $column->width;
		if ($totalSum > 100)
			foreach ($listEntity->content->columns as $otherPos => $column)
				$column->width *= 100. / $totalSum;

		ListService::saveOrUpdate($listEntity);
	}
}

