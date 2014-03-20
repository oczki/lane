<?php
class ListDeleteRowJob implements IJob
{
	private $listId;
	private $rowId;

	public function __construct($listId, $rowId)
	{
		$this->listId = $listId;
		$this->rowId = $rowId;
	}

	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->listId, $owner);
		$pos = ListJobHelper::getRowPos($listEntity, $this->rowId);

		self::delete($listEntity->content->rows, $pos);

		ListService::saveOrUpdate($listEntity);
	}

	private static function delete(&$subject, $pos)
	{
		array_splice($subject, $pos, 1);
	}
}
