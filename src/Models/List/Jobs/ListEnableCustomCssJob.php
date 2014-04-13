<?php
class ListEnableCustomCssJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListService::getByUrlName($owner, $this->arguments['list-id']);
		if (empty($listEntity))
			throw new InvalidListException($this->arguments['list-id']);

		$listEntity->content->useCustomCss = $this->arguments['new-list-custom-css-enabled'];

		ListService::saveOrUpdate($listEntity);
	}
}
