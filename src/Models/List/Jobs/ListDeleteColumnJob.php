<?php
class ListDeleteColumnJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->arguments['list-id'], $owner);
		$pos = ListJobHelper::getColumnPos($listEntity, $this->arguments['column-id']);

		self::delete($listEntity->content->columns, $pos);

		$sum = 0;
		foreach ($listEntity->content->columns as $i => $column)
			$sum += $column->width;
		$mul = 100 / max(0, $sum);

		foreach ($listEntity->content->columns as $i => $column)
			$column->width *= $mul;

		foreach ($listEntity->content->rows as $i => $row)
			self::delete($listEntity->content->rows[$i]->content, $pos);

		ListService::saveOrUpdate($listEntity);
	}

	private static function delete(&$subject, $pos)
	{
		array_splice($subject, $pos, 1);
	}
}
