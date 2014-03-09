<?php
class ListAddRowJob implements IJob
{
	private $listId;
	private $rowContent;

	public function __construct($listId, array $rowContent = [])
	{
		$this->listId = $listId;
		$this->rowContent = $rowContent;
	}

	public function execute(UserEntity $owner)
	{
		$listEntity = ListJobHelper::getList($this->listId, $owner);

		if (empty($this->rowContent))
			$this->rowContent = array_fill(0, count($listEntity->content->columns), '');

		if (count($this->rowContent) != count($listEntity->content->columns))
			throw new SimpleException('Invalid column count.');

		$row = new ListRow();
		$row->content = $this->rowContent;
		$row->id = ++$listEntity->content->lastContentId;

		$listEntity->content->rows []= $row;
		ListService::saveOrUpdate($listEntity);
	}
}
