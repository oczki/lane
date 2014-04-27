<?php
/**
* Deletes one row from list.
*
* @param user-name name of list owner
* @param list-id   id of list
* @param row-id    id of row to delete
*/
class DeleteRowJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$pos = ListService::getRowPos($list, $this->getArgument('row-id'));

		self::delete($list->content->rows, $pos);

		ListService::saveOrUpdate($list);
	}

	private static function delete(&$subject, $pos)
	{
		array_splice($subject, $pos, 1);
	}
}
