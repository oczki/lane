<?php
class ListSetPriorityJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$allLists = array_values(ListService::getByUser($this->getUser()));

		$newIndex = intval($this->getArgument('new-list-priority')) - 1;
		$previousIndex = null;
		foreach ($allLists as $index => $otherList)
			if ($otherList->urlName == $list->urlName)
				$previousIndex = $index;

		$priorities = range(1, count($allLists));
		array_splice($priorities, $newIndex, 1);
		array_splice($priorities, $previousIndex, 0, $newIndex + 1);

		foreach ($allLists as $i => $list)
		{
			$list->priority = $priorities[$i];
			ListService::saveOrUpdate($list);
		}
	}
}
