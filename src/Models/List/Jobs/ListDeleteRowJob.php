<?php
class ListDeleteRowJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->arguments['list-id'], $owner);
		$pos = ListJobHelper::getRowPos($listEntity, $this->arguments['row-id']);

		self::delete($listEntity->content->rows, $pos);

		ListService::saveOrUpdate($listEntity);
	}

	private static function delete(&$subject, $pos)
	{
		array_splice($subject, $pos, 1);
	}
}
