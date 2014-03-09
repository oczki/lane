<?php
class ListShowRowIdsJob implements IJob
{
	private $listId;
	private $showRowIds;

	public function __construct($listId, $showRowIds)
	{
		$this->listId = $listId;
		$this->showRowIds = boolval($showRowIds);
	}

	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->listId, $owner);

		$listEntity->content->showRowIds = $this->showRowIds;

		ListService::saveOrUpdate($listEntity);
	}
}
