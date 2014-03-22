<?php
class ListAddJob implements IJob
{
	private $name;
	private $visible;

	public function __construct(
		$name,
		$visible)
	{
		$this->name = $name;
		$this->visible = $visible;
	}

	public function execute(UserEntity $owner)
	{
		ListJobHelper::validateListName($this->name);

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
		$listEntity->name = $this->name;
		$listEntity->visible = $this->visible;
		$listEntity->content = new ListContent();

		$column1 = new ListColumn();
		$column1->name = 'Example column 1';
		$column1->width = 70;
		$column1->align = ListColumn::ALIGN_LEFT;
		$column1->id = ++$listEntity->content->lastContentId;

		$column2 = new ListColumn();
		$column2->name = 'Example column 2';
		$column2->width = 30;
		$column2->align = ListColumn::ALIGN_LEFT;
		$column2->id = ++$listEntity->content->lastContentId;

		$row = new ListRow();
		$row->content = ['Example data', ''];
		$row->id = ++$listEntity->content->lastContentId;

		$listEntity->content->columns []= $column1;
		$listEntity->content->columns []= $column2;
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
