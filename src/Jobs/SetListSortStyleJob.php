<?php
/**
* Sets new list sort style.
*
* @param user-name name of list owner
* @param list-id   id of list
* @param new-style string representing new sort style (example: <code>[[0,0],[2,1]]</code> will sort by first column in
*                  ascending order, then by third column in descending order.)
*/
class SetListSortStyleJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$sortStyle = $this->getArgument('new-style');
		$sortStyle = json_decode($sortStyle);

		if (empty($sortStyle))
		{
			$sortStyle = null;
		}
		else
		{
			foreach ($sortStyle as &$sortDefinition)
			{
				if (!is_array($sortDefinition) or count($sortDefinition) != 2)
					throw new ValidationException('Invalid sort style.');

				$sortDefinition[0] = intval($sortDefinition[0]);
				$sortDefinition[1] = intval(boolval($sortDefinition[1]));
			}
			$sortStyle = json_encode($sortStyle);
		}

		$list->content->sortStyle = $sortStyle;

		ListService::saveOrUpdate($list);
	}
}
