<?php
class ListEnableCustomCssJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->arguments['list-id'], $owner);

		$listEntity->content->useCustomCss = $this->arguments['new-list-custom-css-enabled'];

		ListService::saveOrUpdate($listEntity);
	}
}
