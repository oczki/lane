<?php
class ListEnableRowIdsJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$list->content->showRowIds = $this->getArgument('new-list-row-ids-enabled');

		ListService::saveOrUpdate($list);
	}
}
