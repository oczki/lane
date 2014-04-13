<?php
/**
* Adds new row to list.
*
* @user-name: name of list owner
* @list-id: id of list
* @new-row-id: id of new row
* @new-row-content: array with row content
*/
class ListAddRowJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		if ($this->getArgument('new-row-id') <= $list->content->lastContentId)
			throw new SimpleException('Row ID already exists: ' . $this->getArgument('new-row-id') . '.');

		$row = new ListRow();
		$row->id = $this->getArgument('new-row-id');

		if (empty($this->getArgument('new-row-content')))
			$row->content = array_fill(0, count($list->content->columns), '');
		else
		{
			if (count($this->getArgument('new-row-content')) != count($list->content->columns))
				throw new SimpleException('Invalid column count.');
			$row->content = $this->getArgument('new-row-content');
		}

		$list->content->lastContentId = $row->id;

		$list->content->rows []= $row;
		ListService::saveOrUpdate($list);
	}
}
