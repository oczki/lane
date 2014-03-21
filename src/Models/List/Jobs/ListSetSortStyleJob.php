<?php
class ListSetSortStyleJob implements IJob
{
	private $listId;
	private $newSortStyle;

	public function __construct($listId, $newSortStyle)
	{
		$this->listId = $listId;
		$this->newSortStyle = $newSortStyle;
	}

	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->listId, $owner);

		$listEntity->content->sortStyle = $this->newSortStyle;

		ListService::saveOrUpdate($listEntity);
	}
}
