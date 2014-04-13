<?php
/**
* Enabels or disables user CSS for given list.
*
* @user-name: name of list owner
* @list-id: id of list
* @new-list-custom-css-enabled: whether to enable custom CSS or not
*/
class ListEnableCustomCssJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$list->content->useCustomCss = $this->getArgument('new-list-custom-css-enabled');

		ListService::saveOrUpdate($list);
	}
}
