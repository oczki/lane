<?php
class ListEnableRowIdsJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->arguments['list-id'], $owner);

		$listEntity->content->showRowIds = $this->arguments['new-list-row-ids-enabled'];

		ListService::saveOrUpdate($listEntity);
	}
}
