<?php
class ListSetCssJob implements IJob
{
	private $listId;
	private $newCss;

	public function __construct($listId, $newCss)
	{
		$this->listId = $listId;
		$this->newCss = $newCss;
	}

	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->listId, $owner);

		$listEntity->content->css = $this->newCss;

		ListService::saveOrUpdate($listEntity);
	}
}
