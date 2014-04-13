<?php
/**
* Sets list visibility.
*
* @user-name: name of list owner
* @list-id: id of list
* @new-list-visibility: whether to show the list to the public or not
*/
class ListSetVisibilityJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$list->visible = boolval($this->getArgument('new-list-visibility'));

		ListService::saveOrUpdate($list);
	}
}
