<?php
class ListAddRowJob implements IJob
{
	private $listId;
	private $rowId;
	private $rowContent;

	public function __construct($listId, $rowId, array $rowContent = [])
	{
		$this->listId = $listId;
		$this->rowId = $rowId;
		$this->rowContent = $rowContent;
	}

	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->listId, $owner);

		if (empty($this->rowContent))
			$this->rowContent = array_fill(0, count($listEntity->content->columns), '');

		if ($this->rowId <= $listEntity->content->lastContentId)
			throw new SimpleException('Row ID already exists: ' . $this->columnId . '.');

		if (count($this->rowContent) != count($listEntity->content->columns))
			throw new SimpleException('Invalid column count.');

		$row = new ListRow();
		$row->id = $this->rowId;
		$row->content = $this->rowContent;

		$listEntity->content->lastContentId = $row->id;

		$listEntity->content->rows []= $row;
		ListService::saveOrUpdate($listEntity);
	}
}
