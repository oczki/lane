<?php
/**
* Enables or disables showing row IDs for given list.
*
* @user-name: name of list owner
* @list-id: id of list
* @new-state: whether to enable row IDs (1) or not (0)
*/
class SetListRowIdsStateJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$list->content->showRowIds = boolval($this->getArgument('new-state'));

		ListService::saveOrUpdate($list);
	}
}
