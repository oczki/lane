<?php
class ListFixPermalinkJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListService::getByUrlName($owner, $this->arguments['list-id']);
		if (empty($listEntity))
			throw new InvalidListException($this->arguments['list-id']);

		$listEntity->urlName = ListService::forgeUrlName($listEntity);

		ListService::saveOrUpdate($listEntity);
	}
}
