<?php
/**
* Sets new list sort style.
*
* @user-name: name of list owner
* @list-id: id of list
* @new-sort-style: array containing new sort style, compatible with jquery TableSorter syntax
*/
class ListSetSortStyleJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$list->content->sortStyle = $this->getArgument('new-sort-style');

		ListService::saveOrUpdate($list);
	}
}
