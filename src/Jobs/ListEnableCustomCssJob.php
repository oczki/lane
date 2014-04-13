<?php
class ListEnableCustomCssJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$list->content->useCustomCss = $this->getArgument('new-list-custom-css-enabled');

		ListService::saveOrUpdate($list);
	}
}
