<?php
class ListDeleteJob implements IJob
{
	private $listId;

	public function __construct($listId)
	{
		$this->listId = $listId;
	}

	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->listId, $owner);

		ListService::delete($listEntity);
	}
}
