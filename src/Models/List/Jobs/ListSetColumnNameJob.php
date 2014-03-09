<?php
class ListSetColumnNameJob implements IJob
{
	private $listId;
	private $columnId;
	private $newName;

	public function __construct($listId, $columnId, $newName)
	{
		$this->listId = $listId;
		$this->columnId = $columnId;
		$this->newName = $newName;
	}

	public function execute(UserEntity $owner)
	{
		ListJobHelper::validateColumnName($this->newName);

		$listEntity = ListJobHelper::getList($this->listId, $owner);
		$pos = ListJobHelper::getColumnPos($listEntity, $this->columnId);

		$listEntity->content->columns[$pos]->name = $this->newName;

		ListService::saveOrUpdate($listEntity);
	}
}
