<?php
class ListSetColumnWidthJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListService::getByUrlName($owner, $this->arguments['list-id']);
		if (empty($listEntity))
			throw new InvalidListException($this->arguments['list-id']);

		$pos = ListService::getColumnPos($listEntity, $this->arguments['column-id']);

		$listEntity->content->columns[$pos]->width = floatval($this->arguments['new-column-width']);

		$totalSum = 0;
		foreach ($listEntity->content->columns as $otherPos => $column)
			$totalSum += $column->width;
		if ($totalSum > 100)
			foreach ($listEntity->content->columns as $otherPos => $column)
				$column->width *= 100. / $totalSum;

		ListService::saveOrUpdate($listEntity);
	}
}
