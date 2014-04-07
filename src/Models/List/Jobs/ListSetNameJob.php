<?php
class ListSetNameJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		ListService::validateListName($this->arguments['new-list-name']);

		$listEntity = ListService::getByUrlName($owner, $this->arguments['list-id']);

		$listEntity->name = $this->arguments['new-list-name'];

		ListService::saveOrUpdate($listEntity);
	}
}
