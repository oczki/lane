<?php
/**
* Adds new column to list.
*
* @user-name: name of list owner
* @list-id: id of list
* @new-name: name of the new column
* @new-id: content id of the new column
* @new-align: text alignment of the new column (left, right or center)
*/
class AddColumnJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();
		ListService::validateColumnName($this->getArgument('new-name'));
		ListService::validateContentID($list, $this->getArgument('new-id'));

		if (!in_array($this->getArgument('new-align'), ListService::getPossibleColumnAlign()))
			throw new SimpleException('Invalid column align: ' . $this->getArgument('new-align') . '.');

		$mul = count($list->content->columns);
		$mul /= ($mul + 1);
		foreach ($list->content->columns as $column)
			$column->width *= $mul;

		$column = new ListColumn();
		$column->id = $this->getArgument('new-id');
		$column->name = $this->getArgument('new-name');
		$column->align = !empty($this->getArgument('new-align'))
			? $this->getArgument('new-align')
			: ListColumn::ALIGN_LEFT;
		$column->width = 100. / (count($list->content->columns) + 1);

		$list->content->lastContentId = $column->id;

		$list->content->columns []= $column;
		foreach ($list->content->rows as $row)
			$row->content = array_merge($row->content, ['']);

		ListService::saveOrUpdate($list);
	}
}
