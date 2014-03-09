<?php
class ListSetVisibilityJob implements IJob
{
	private $listId;
	private $newVisibility;

	public function __construct($listId, $newVisibility)
	{
		$this->listId = $listId;
		$this->newVisibility = boolval($newVisibility);
	}

	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->listId, $owner);

		$listEntity->visible = $this->newVisibility;

		ListService::saveOrUpdate($listEntity);
	}
}
