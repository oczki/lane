<?php
abstract class ListInnerJob implements IJob
{
	protected $listEntity;
	private $listUrlName;

	public function __construct($listUrlName)
	{
		$this->listUrlName = $listUrlName;
	}

	public function execute(UserEntity $user)
	{
		$this->listEntity = ListService::getByUrlName($user, $this->listUrlName);

		if (empty($this->listEntity))
			throw new SimpleException('List with this ID wasn\'t found.');

		if ($this->listEntity->userId != $user->id)
			throw new SimpleException('List owner ID doesn\'t match logged in user ID.');
	}
}
