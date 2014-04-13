<?php
/**
* Enabels or disables showing row IDs for given list.
*
* @user-name: name of list owner
* @list-id: id of list
* @new-list-row-ids-enabled: whether to enable row IDs or not
*/
class ListEnableRowIdsJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$list->content->showRowIds = boolval($this->getArgument('new-list-row-ids-enabled'));

		ListService::saveOrUpdate($list);
	}
}
