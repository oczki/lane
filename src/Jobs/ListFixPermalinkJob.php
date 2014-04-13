<?php
/**
* Fixes list ID so that it reflects new list name. Upon renaming, list ID isn't changed in order not to break pending jobs. Should be used with caution.
*
* @user-name: name of list owner
* @list-id: id of list
*/
class ListFixPermalinkJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$list->urlName = ListService::forgeUrlName($list);

		ListService::saveOrUpdate($list);
	}
}
