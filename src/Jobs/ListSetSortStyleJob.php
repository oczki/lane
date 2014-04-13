<?php
class ListSetSortStyleJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$list->content->sortStyle = $this->getArgument('new-sort-style');

		ListService::saveOrUpdate($list);
	}
}
