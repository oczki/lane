<?php
class ListSetNameJob implements IJob
{
	private $listId;
	private $newName;

	public function __construct($listId, $newName)
	{
		$this->listId = $listId;
		$this->newName = $newName;
	}

	public function execute(UserEntity $owner)
	{
		ListJobHelper::validateListName($this->newName);

		$listEntity = ListJobHelper::getList($this->listId, $owner);

		$listEntity->name = $this->newName;
		//possibly change urlName here

		ListService::saveOrUpdate($listEntity);
	}
}
