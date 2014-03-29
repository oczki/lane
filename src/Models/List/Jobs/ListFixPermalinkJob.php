<?php
class ListFixPermalinkJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->arguments['list-id'], $owner);

		$listEntity->urlName = ListService::forgeUrlName($listEntity);

		ListService::saveOrUpdate($listEntity);
	}
}
