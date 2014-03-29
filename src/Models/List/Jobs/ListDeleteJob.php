<?php
class ListDeleteJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->arguments['list-id'], $owner);

		ListService::delete($listEntity);
	}
}
