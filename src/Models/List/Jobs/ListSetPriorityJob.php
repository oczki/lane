<?php
class ListSetPriorityJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$allListEntities = array_values(ListService::getByUser($owner));
		$listEntity = ListService::getByUrlName($owner, $this->arguments['list-id']);
		if (empty($listEntity))
			throw new InvalidListException($this->arguments['list-id']);

		$newIndex = intval($this->arguments['new-list-priority']) - 1;
		$previousIndex = null;
		foreach ($allListEntities as $index => $otherListEntity)
			if ($otherListEntity->urlName == $listEntity->urlName)
				$previousIndex = $index;

		$priorities = range(1, count($allListEntities));
		array_splice($priorities, $newIndex, 1);
		array_splice($priorities, $previousIndex, 0, $newIndex + 1);

		foreach ($allListEntities as $i => $listEntity)
		{
			$listEntity->priority = $priorities[$i];
			ListService::saveOrUpdate($listEntity);
		}
	}
}
