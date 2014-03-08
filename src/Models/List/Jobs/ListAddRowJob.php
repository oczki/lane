<?php
class ListAddRowJob extends ListInnerJob implements IJob
{
	private $rowContent;

	public function __construct($listUrlName, array $rowContent = [])
	{
		parent::__construct($listUrlName);
		$this->rowContent = $rowContent;
	}

	public function execute(UserEntity $entity)
	{
		parent::execute($entity);

		if (empty($this->rowContent))
			$this->rowContent = array_fill(0, count($this->listEntity->content->columns), '');

		if (count($this->rowContent) != count($this->listEntity->content->columns))
			throw new Exception('Invalid column count.');

		$row = new ListRow();
		$row->content = $this->rowContent;

		$this->listEntity->content->rows []= $row;
		ListService::saveOrUpdate($this->listEntity);
	}
}
