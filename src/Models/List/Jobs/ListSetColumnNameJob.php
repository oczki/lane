<?php
class ListSetColumnNameJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		ListService::validateColumnName($this->arguments['new-column-name']);

		$listEntity = ListService::getByUrlName($owner, $this->arguments['list-id']);
		if (empty($listEntity))
			throw new InvalidListException($this->arguments['list-id']);

		$pos = ListService::getColumnPos($listEntity, $this->arguments['column-id']);

		$listEntity->content->columns[$pos]->name = $this->arguments['new-column-name'];

		ListService::saveOrUpdate($listEntity);
	}
}
