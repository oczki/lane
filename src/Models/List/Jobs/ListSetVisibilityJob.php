<?php
class ListSetVisibilityJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->arguments['list-id'], $owner);

		$listEntity->visible = boolval($this->arguments['new-list-visibility']);

		ListService::saveOrUpdate($listEntity);
	}
}
