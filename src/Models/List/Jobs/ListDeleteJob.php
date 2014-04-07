<?php
class ListDeleteJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListService::getByUrlName($owner, $this->arguments['list-id']);

		ListService::delete($listEntity);
	}
}
