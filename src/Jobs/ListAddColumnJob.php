<?php
/**
* Adds new column to list.
*
* @user-name: name of list owner
* @list-id: id of list
* @new-column-name: name of the new column
* @new-column-id: content if of the new column
* @new-column-align: text align of the new column
*/
class ListAddColumnJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();
		ListService::validateColumnName($this->getArgument('new-column-name'));
		ListService::validateContentID($list, $this->getArgument('new-column-id'));

		if (!in_array($this->getArgument('new-column-align'), ListService::getPossibleColumnAlign()))
			throw new SimpleException('Invalid column align: ' . $this->getArgument('new-column-align') . '.');

		$mul = count($list->content->columns);
		$mul /= ($mul + 1);
		foreach ($list->content->columns as $column)
			$column->width *= $mul;

		$column = new ListColumn();
		$column->id = $this->getArgument('new-column-id');
		$column->name = $this->getArgument('new-column-name');
		$column->align = !empty($this->getArgument('new-column-align'))
			? $this->getArgument('new-column-align')
			: ListColumn::ALIGN_LEFT;
		$column->width = 100. / (count($list->content->columns) + 1);

		$list->content->lastContentId = $column->id;

		$list->content->columns []= $column;
		foreach ($list->content->rows as $row)
			$row->content = array_merge($row->content, ['']);

		ListService::saveOrUpdate($list);
	}
}
