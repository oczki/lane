<?php
class ListAddRowJob extends ListInnerJob implements IJob
{
	private $row;

	public function __construct($listUrlName, array $row = [])
	{
		parent::__construct($listUrlName);
		$this->row = $row;
	}

	public function execute(UserEntity $entity)
	{
		parent::execute($entity);

		if (empty($this->row))
			$this->row = array_fill(0, count($this->listEntity->content->columns), '');

		if (count($this->row) != count($this->listEntity->content->columns))
			throw new Exception('Invalid column count.');

		$this->listEntity->content->rows []= $this->row;
		ListService::saveOrUpdate($this->listEntity);
	}
}
