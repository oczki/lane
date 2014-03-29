<?php
class ListSetCustomCssJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->arguments['list-id'], $owner);

		$listEntity->content->customCss = $this->arguments['new-list-custom-css'];

		ListService::saveOrUpdate($listEntity);
	}
}
