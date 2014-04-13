<?php
/**
* Sets new list name. Note: doesn't change its ID.
*
* @user-name: name of list owner
* @list-id: id of list
* @new-list-name: new list name
*/
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
