<?php
class ListSetCustomCssJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListService::getByUrlName($owner, $this->arguments['list-id']);

		$listEntity->content->customCss = $this->arguments['new-list-custom-css'];

		ListService::saveOrUpdate($listEntity);
	}
}
