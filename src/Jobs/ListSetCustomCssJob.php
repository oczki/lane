<?php
class ListSetCustomCssJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$list->content->customCss = $this->getArgument('new-list-custom-css');

		ListService::saveOrUpdate($list);
	}
}
