<?php
/**
* Sets new list sort style.
*
* @user-name: name of list owner
* @list-id: id of list
* @new-sort-style: array containing new sort style, compatible with jquery TableSorter syntax
*/
class ListSetSortStyleJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$sortStyle = $this->getArgument('new-sort-style');
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
