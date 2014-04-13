<?php
/**
* Sets new column align.
*
* @user-name: name of list owner
* @list-id: id of list
* @column-id: id of column to change the align of
* @new-column-align: new colum nalign
*/
class ListSetColumnAlignJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		if (!in_array($this->getArgument('new-column-align'), ListService::getPossibleColumnAlign()))
			throw new SimpleException('Invalid column align: ' . $this->getArgument('new-column-align') . '.');

		$pos = ListService::getColumnPos($list, $this->getArgument('column-id'));

		$list->content->columns[$pos]->align = $this->getArgument('new-column-align');

		ListService::saveOrUpdate($list);
	}
}
