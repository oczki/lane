<?php
/**
* Sets new column name.
*
* @param user-name name of list owner
* @param list-id   id of list
* @param column-id id of column to change name of
* @param new-name  new column name
*/
class SetColumnNameJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		ListService::validateColumnName($this->getArgument('new-name'));

		$pos = ListService::getColumnPos($list, $this->getArgument('column-id'));

		$list->content->columns[$pos]->name = $this->getArgument('new-name');

		ListService::saveOrUpdate($list);
	}
}
