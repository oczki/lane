<?php
class ListEnableRowIdsJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListService::getByUrlName($owner, $this->arguments['list-id']);
		if (empty($listEntity))
			throw new InvalidListException($this->arguments['list-id']);

		$listEntity->content->showRowIds = $this->arguments['new-list-row-ids-enabled'];

		ListService::saveOrUpdate($listEntity);
	}
}
