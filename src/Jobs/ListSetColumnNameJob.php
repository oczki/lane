<?php
/**
* Sets new column name.
*
* @user-name: name of list owner
* @list-id: id of list
* @column-id: id of column to change name of
* @new-column-name: new column name
*/
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
