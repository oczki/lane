<?php
class ListEditCellJob implements IJob
{
	private $listId;
	private $rowId;
	private $columnId;
	private $newText;

	public function __construct($listId, $rowId, $columnId, $newText)
	{
		$this->listId = $listId;
		$this->rowId = $rowId;
		$this->columnId = $columnId;
		$this->newText = $newText;
	}

	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->listId, $owner);
		$rowPos = ListJobHelper::getRowPos($listEntity, $this->rowId);
		$columnPos = ListJobHelper::getColumnPos($listEntity, $this->columnId);

		$listEntity->content->rows[$rowPos]->content[$columnPos] = $this->newText;

		ListService::saveOrUpdate($listEntity);
	}

	private static function delete(&$subject, $pos)
	{
		array_splice($subject, $pos, 1);
	}
}

