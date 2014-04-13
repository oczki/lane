<?php
/**
* Adds new row to list.
*
* @user-name: name of list owner
* @list-id: id of list
* @new-id: id of new row
* @new-content: array with row content
*/
class AddRowJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();
		ListService::validateContentID($list, $this->getArgument('new-id'));

		$row = new ListRow();
		$row->id = $this->getArgument('new-id');

		if (empty($this->getArgument('new-content')))
		{
			$row->content = array_fill(0, count($list->content->columns), '');
		}
		else
		{
			if (count($this->getArgument('new-content')) != count($list->content->columns))
				throw new SimpleException('Invalid column count.');

			foreach ($this->getArgument('new-content') as $cellContent)
				ListService::validateCellContent($cellContent);

			$row->content = $this->getArgument('new-content');
		}

		$list->content->lastContentId = $row->id;

		$list->content->rows []= $row;
		ListService::saveOrUpdate($list);
	}
}
