<?php
/**
* Sets new list name. Note it doesn't change its ID. See fix-permalink to see how to deal with that.
*
* @param user-name name of list owner
* @param list-id   id of list
* @param new-name  new list name
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
