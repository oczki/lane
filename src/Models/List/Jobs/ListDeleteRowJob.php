<?php
class ListDeleteRowJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListService::getByUrlName($owner, $this->arguments['list-id']);
		if (empty($listEntity))
			throw new InvalidListException($this->arguments['list-id']);

		$pos = ListService::getRowPos($listEntity, $this->arguments['row-id']);

		self::delete($listEntity->content->rows, $pos);

		ListService::saveOrUpdate($listEntity);
	}

	private static function delete(&$subject, $pos)
	{
		array_splice($subject, $pos, 1);
	}
}
