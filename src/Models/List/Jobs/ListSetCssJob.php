<?php
class ListSetCssJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->arguments['list-id'], $owner);

		$listEntity->content->css = $this->arguments['new-list-css'];

		ListService::saveOrUpdate($listEntity);
	}
}
