<?php
class ListSetColumnAlignJob implements IJob
{
	private $listId;
	private $columnId;
	private $newAlign;

	public function __construct($listId, $columnId, $newAlign)
	{
		$this->listId = $listId;
		$this->columnId = $columnId;
		$this->newAlign = $newAlign;
	}

	public function execute(UserEntity $owner)
	{
		if (!in_array($this->newAlign, ListService::getPossibleColumnAlign()))
			throw new SimpleException('Invalid column align: ' . $this->newAlign . '.');

		$listEntity = ListJobHelper::getList($this->listId, $owner);
		$pos = ListJobHelper::getColumnPos($listEntity, $this->columnId);

		$listEntity->content->columns[$pos]->align = $this->newAlign;

		ListService::saveOrUpdate($listEntity);
	}
}
