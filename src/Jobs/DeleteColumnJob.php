<?php
/**
* Deletes one column from list.
*
* @param user-name name of list owner
* @param list-id   id of list
* @param column-id id of column to delete
*/
class DeleteColumnJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		if (count($list->content->columns) == 1)
			throw new ValidationException('Cannot delete last column.');

		$pos = ListService::getColumnPos($list, $this->getArgument('column-id'));

		self::delete($list->content->columns, $pos);

		$sum = 0;
		foreach ($list->content->columns as $i => $column)
			$sum += $column->width;
		$mul = 100 / max(0, $sum);

		foreach ($list->content->columns as $i => $column)
			$column->width *= $mul;

		foreach ($list->content->rows as $i => $row)
			self::delete($list->content->rows[$i]->content, $pos);

		ListService::saveOrUpdate($list);
	}

	private static function delete(&$subject, $pos)
	{
		array_splice($subject, $pos, 1);
	}
}
