<?php
class ListSetNameJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		ListService::validateListName($this->getArgument('new-list-name'));

		$list->name = $this->getArgument('new-list-name');

		ListService::saveOrUpdate($list);
	}
}
