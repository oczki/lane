<?php
class ListAddColumnJob implements IJob
{
	private $listId;
	private $columnId;
	private $name;
	private $align;

	public function __construct($listId, $columnId, $name, $align)
	{
		$this->listId = $listId;
		$this->columnId = $columnId;
		$this->name = $name;
		$this->align = $align;
	}

	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->listId, $owner);

		ListJobHelper::validateColumnName($this->name);

		if ($this->columnId <= $listEntity->content->lastContentId)
			throw new SimpleException('Column ID already exists: ' . $this->columnId . '.');

		if (!in_array($this->align, ListService::getPossibleColumnAlign()))
			throw new SimpleException('Invalid column align: ' . $this->align . '.');

		$listEntity->content->lastContentId = $this->columnId;

		$mul = count($listEntity->content->columns);
		$mul /= ($mul + 1);
		foreach ($listEntity->content->columns as $column)
			$column->width *= $mul;

		$column = new ListColumn();
		$column->id = $this->columnId;
		$column->name = $this->name;
		$column->align = empty($this->align) ? ListColumn::ALIGN_LEFT : $this->align;
		$column->width = 100. / (count($listEntity->content->columns) + 1);

		$listEntity->content->columns []= $column;
		foreach ($listEntity->content->rows as $row)
			$row->content = array_merge($row->content, ['']);

		ListService::saveOrUpdate($listEntity);
	}
}
