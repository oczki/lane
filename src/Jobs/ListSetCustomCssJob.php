<?php
/**
* Sets new list custom CSS.
*
* @user-name: name of list owner
* @list-id: id of list
* @new-list-custom-css: new content of custom CSS
*/
class ListSetCustomCssJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$list->content->customCss = $this->getArgument('new-list-custom-css');

		ListService::saveOrUpdate($list);
	}
}
