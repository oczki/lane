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

		$validator = new Validator($this->name, 'list name');
		$validator->checkMinLength(1);
		$validator->checkMaxLength(20);

		$this->visible = $visible;
	}

	public function execute(UserEntity $user)
	{
		$filter = new ListFilter();
		$filter->userId = $user->id;
		$lists = ListService::getFilteredLists($filter);

		$maxPriority = array_reduce($lists, function($max, $list)
		{
			if ($list->priority > $max)
				$max = $list->priority;
		}, 0);

		$alpha =
			'0123456789_-' .
			'abcdefghijklmnopqrstuvwxyz' .
			'ABCDEFGHJIKLMNOPQRSTUVWXYZ';

		$listEntity = new ListEntity();
		$listEntity->uniqueId = TextHelper::randomString($alpha, 32);
		$listEntity->priority = $maxPriority + 1;
		$listEntity->userId = $user->id;
		$listEntity->name = $this->name;
		$listEntity->visible = $this->visible;
		$listEntity->content = new ListContent();

		$column1 = new ListColumn();
		$column1->name = 'Example column 1';
		$column1->width = 70;
		$column1->align = ListColumn::ALIGN_LEFT;

		$column2 = new ListColumn();
		$column2->name = 'Example column 2';
		$column2->width = 30;
		$column2->align = ListColumn::ALIGN_LEFT;

		$listEntity->content->columns []= $column1;
		$listEntity->content->columns []= $column2;

		return ListService::saveOrUpdate($listEntity);
	}
}
