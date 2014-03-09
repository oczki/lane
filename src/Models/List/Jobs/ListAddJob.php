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

	public function execute(UserEntity $user)
	{
		ListJobHelper::validateListName($this->name);

		$filter = new ListFilter();
		$filter->userId = $user->id;
		$lists = ListService::getFilteredLists($filter);

		$maxPriority = array_reduce($lists, function($max, $list)
		{
			if ($list->priority > $max)
				$max = $list->priority;
		}, 0);

		$listEntity = new ListEntity();
		$listEntity->priority = $maxPriority + 1;
		$listEntity->userId = $user->id;
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

		$listEntity->content->columns []= $column1;
		$listEntity->content->columns []= $column2;

		$baseUrlName = TextHelper::convertCase($listEntity->name,
			TextHelper::BLANK_CASE,
			TextHelper::SNAKE_CASE);
		self::forgeUrlName($user, $listEntity, $baseUrlName);

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
