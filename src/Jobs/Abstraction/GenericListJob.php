<?php
abstract class GenericListJob extends GenericUserJob
{
	public function getList()
	{
		$user = $this->getUser();

		$list = ListService::getByUrlName($user, $this->getArgument('list-id'));
		if (empty($list))
			throw new InvalidListException($this->getArgument('list-id'));

		return $list;
	}
}
