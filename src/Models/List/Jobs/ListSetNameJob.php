<?php
class ListSetNameJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		ListJobHelper::validateListName($this->arguments['new-list-name']);

		$listEntity = ListJobHelper::getList($this->arguments['list-id'], $owner);

		$listEntity->name = $this->arguments['new-list-name'];
		//possibly change urlName here

		ListService::saveOrUpdate($listEntity);
	}
}
