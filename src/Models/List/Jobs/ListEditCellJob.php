<?php
class ListEditCellJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->arguments['list-id'], $owner);
		$rowPos = ListJobHelper::getRowPos($listEntity, $this->arguments['row-id']);
		$columnPos = ListJobHelper::getColumnPos($listEntity, $this->arguments['column-id']);

		$listEntity->content->rows[$rowPos]->content[$columnPos] = $this->arguments['new-cell-text'];

		ListService::saveOrUpdate($listEntity);
	}

	private static function delete(&$subject, $pos)
	{
		array_splice($subject, $pos, 1);
	}
}
