<?php
/**
* Sets new column width.
*
* @user-name: name of list owner
* @list-id: id of list
* @column-id: id of column to change width of
* @new-column-width: new column width
*/
class ListSetColumnWidthJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$pos = ListService::getColumnPos($list, $this->getArgument('column-id'));

		$list->content->columns[$pos]->width = floatval($this->getArgument('new-column-width'));

		$totalSum = 0;
		foreach ($list->content->columns as $otherPos => $column)
			$totalSum += $column->width;

		if ($totalSum > 100)
			foreach ($list->content->columns as $otherPos => $column)
				$column->width *= 100. / $totalSum;

		ListService::saveOrUpdate($list);
	}
}
