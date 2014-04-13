<?php
class ListEditCellJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$rowPos = ListService::getRowPos($list, $this->getArgument('row-id'));
		$columnPos = ListService::getColumnPos($list, $this->getArgument('column-id'));

		$list->content->rows[$rowPos]->content[$columnPos] = $this->getArgument('new-cell-text');

		ListService::saveOrUpdate($list);
	}

	private static function delete(&$subject, $pos)
	{
		array_splice($subject, $pos, 1);
	}
}
