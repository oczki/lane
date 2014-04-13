<?php
/**
* Sets new list name. Note it doesn't change its ID. See fix-permalink to see
* how to deal with that.
*
* @user-name: name of list owner
* @list-id: id of list
* @new-name: new list name
*/
class SetListNameJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		ListService::validateListName($this->getArgument('new-name'));

		$list->name = $this->getArgument('new-name');

		ListService::saveOrUpdate($list);
	}
}
