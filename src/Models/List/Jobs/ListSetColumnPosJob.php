<?php
class ListSetColumnPosJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->arguments['list-id'], $owner);

		$newPos = intval($this->arguments['new-column-pos']);
		if ($newPos < 0 or $newPos >= count($listEntity->content->columns))
			throw new SimpleException('Invalid column target position: ' . $newPos . '.');

		$oldPos = ListJobHelper::getColumnPos($listEntity, $this->arguments['column-id']);

		self::swap($listEntity->content->columns, $oldPos, $newPos);

		foreach ($listEntity->content->rows as $i => $row)
			self::swap($listEntity->content->rows[$i]->content, $oldPos, $newPos);

		ListService::saveOrUpdate($listEntity);
	}

	private static function swap(&$subject, $oldPos, $newPos)
	{
		$entity = $subject[$oldPos];
		array_splice($subject, $oldPos, 1);
		array_splice($subject, $newPos, 0, [$entity]);
	}
}
