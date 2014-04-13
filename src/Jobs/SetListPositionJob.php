<?php
/**
* Sets new list position (used for swapping lists).
*
* @user-name: name of list owner
* @list-id: id of list
* @new-position: integer specifying desired list position
*/
class SetListPositionJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$allLists = array_values(ListService::getByUser($this->getUser()));

		$newIndex = intval($this->getArgument('new-position')) - 1;
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
