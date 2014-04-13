<?php
/**
* Sets new list custom CSS.
*
* @user-name: name of list owner
* @list-id: id of list
* @new-content: new content of custom CSS
*/
class SetListCssContentJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$list->content->customCss = $this->getArgument('new-content');

		ListService::saveOrUpdate($list);
	}
}
