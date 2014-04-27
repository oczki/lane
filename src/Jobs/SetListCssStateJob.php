<?php
/**
* Enables or disables user CSS for given list.
*
* @param user-name name of list owner
* @param list-id   id of list
* @param new-state whether to enable custom CSS (1) or not (0)
*/
class SetListCssStateJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$list->content->useCustomCss = boolval($this->getArgument('new-state'));

		ListService::saveOrUpdate($list);
	}
}
