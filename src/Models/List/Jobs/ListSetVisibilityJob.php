<?php
class ListSetVisibilityJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListService::getByUrlName($owner, $this->arguments['list-id']);

		$listEntity->visible = boolval($this->arguments['new-list-visibility']);

		ListService::saveOrUpdate($listEntity);
	}
}
