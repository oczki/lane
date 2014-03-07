<?php
abstract class ListInnerJob implements IJob
{
	protected $listEntity;

	public function __construct($listUniqueId)
	{
		$this->listEntity = ListService::getByUniqueId($listUniqueId);
	}

	public function execute(UserEntity $user)
	{
		if (empty($this->listEntity))
			throw new SimpleException('List with this ID wasn\'t found.');

		if ($this->listEntity->userId != $user->id)
			throw new SimpleException('List owner ID doesn\'t match logged in user ID.');
	}
}
