<?php
class ListEditCellJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListService::getByUrlName($owner, $this->arguments['list-id']);
		if (empty($listEntity))
			throw new InvalidListException($this->arguments['list-id']);

		$rowPos = ListService::getRowPos($listEntity, $this->arguments['row-id']);
		$columnPos = ListService::getColumnPos($listEntity, $this->arguments['column-id']);

		$listEntity->content->rows[$rowPos]->content[$columnPos] = $this->arguments['new-cell-text'];

		ListService::saveOrUpdate($listEntity);
	}

	private static function delete(&$subject, $pos)
	{
		array_splice($subject, $pos, 1);
	}
}
