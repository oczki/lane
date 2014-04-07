<?php
class ListSetSortStyleJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListService::getByUrlName($owner, $this->arguments['list-id']);

		$listEntity->content->sortStyle = $this->arguments['new-sort-style'];

		ListService::saveOrUpdate($listEntity);
	}
}
