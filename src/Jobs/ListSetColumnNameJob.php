<?php
class ListSetColumnNameJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		ListService::validateColumnName($this->getArgument('new-column-name'));

		$pos = ListService::getColumnPos($list, $this->getArgument('column-id'));

		$list->content->columns[$pos]->name = $this->getArgument('new-column-name');

		ListService::saveOrUpdate($list);
	}
}
