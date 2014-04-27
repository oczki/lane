<?php
/**
* Updates existing cell contents.
*
* @param user-name    name of list owner
* @param list-id     id of list
* @param column-id   id of column that contains given cell
* @param row-id      id of row that contains given cell
* @param new-content new cell contents
*/
class SetCellContentJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		ListService::validateCellContent($this->getArgument('new-content'));

		$rowPos = ListService::getRowPos($list, $this->getArgument('row-id'));
		$columnPos = ListService::getColumnPos($list, $this->getArgument('column-id'));

		$list->content->rows[$rowPos]->content[$columnPos] = $this->getArgument('new-content');

		ListService::saveOrUpdate($list);
	}

	private static function delete(&$subject, $pos)
	{
		array_splice($subject, $pos, 1);
	}
}
