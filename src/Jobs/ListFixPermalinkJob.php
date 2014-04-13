<?php
class ListFixPermalinkJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$list->urlName = ListService::forgeUrlName($list);

		ListService::saveOrUpdate($list);
	}
}
