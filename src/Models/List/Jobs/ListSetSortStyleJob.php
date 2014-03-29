<?php
class ListSetSortStyleJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->arguments['list-id'], $owner);

		$listEntity->content->sortStyle = $this->arguments['new-sort-style'];

		ListService::saveOrUpdate($listEntity);
	}
}
