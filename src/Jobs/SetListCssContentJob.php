<?php
/**
* Sets new list custom CSS.
*
* @param user-name   name of list owner
* @param list-id     id of list
* @param new-content new content of custom CSS
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
