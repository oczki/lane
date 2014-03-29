<?php
class ListAddJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		ListJobHelper::validateListName($this->arguments['new-list-name']);

		$allListEntities = array_values(ListJobHelper::getLists($owner));

		$maxPriority = array_reduce($allListEntities, function($max, $listEntity)
		{
			return $listEntity->priority > $max
				? $listEntity->priority
				: $max;
		}, 0);

		$listEntity = new ListEntity();
		$listEntity->priority = $maxPriority + 1;
		$listEntity->userId = $owner->id;
		$listEntity->name = $this->arguments['new-list-name'];
		$listEntity->visible = $this->arguments['new-list-visibility'];
		$listEntity->content = new ListContent();

		$column1 = new ListColumn();
		$column1->name = 'First column';
		$column1->width = 45;
		$column1->align = ListColumn::ALIGN_LEFT;
		$column1->id = ++$listEntity->content->lastContentId;

		$column2 = new ListColumn();
		$column2->name = 'Second column';
		$column2->width = 23;
		$column2->align = ListColumn::ALIGN_LEFT;
		$column2->id = ++$listEntity->content->lastContentId;

		$column3 = new ListColumn();
		$column3->name = 'Centered column';
		$column3->width = 32;
		$column3->align = ListColumn::ALIGN_CENTER;
		$column3->id = ++$listEntity->content->lastContentId;

		$row = new ListRow();
		$row->content = ['Point here and click the blue icon to edit.', '', ''];
		$row->id = ++$listEntity->content->lastContentId;

		$listEntity->content->columns []= $column1;
		$listEntity->content->columns []= $column2;
		$listEntity->content->columns []= $column3;
		$listEntity->content->rows []= $row;

		$baseUrlName = TextHelper::convertCase($listEntity->name,
			TextHelper::BLANK_CASE,
			TextHelper::SNAKE_CASE);
		self::forgeUrlName($owner, $listEntity, $baseUrlName);

		return ListService::saveOrUpdate($listEntity);
	}

	public static function forgeUrlName(
		UserEntity $owner,
		ListEntity $listEntity,
		$baseUrlName)
	{
		$filter = new ListFilter();
		$filter->userId = $owner->id;
		$lists = ListService::getFilteredLists($filter);

		//very important - strip all insecure characters
		$baseUrlName = preg_replace('/\W/u', '_', $baseUrlName);

		$listEntity->urlName = $baseUrlName;
		do
		{
			$index = 1;
			$found = true;
			foreach ($lists as $otherList)
			{
				if ($otherList->urlName == $listEntity->urlName)
				{
					$listEntity->urlName = $baseUrlName . $index;
					++ $index;
					$found = false;
				}
			}
		}
		while (!$found);
	}
}
