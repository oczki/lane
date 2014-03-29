<?php
class ListSetColumnNameJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		ListJobHelper::validateColumnName($this->arguments['new-column-name']);

		$listEntity = ListJobHelper::getList($this->arguments['list-id'], $owner);
		$pos = ListJobHelper::getColumnPos($listEntity, $this->arguments['column-id']);

		$listEntity->content->columns[$pos]->name = $this->arguments['new-column-name'];

		ListService::saveOrUpdate($listEntity);
	}
}
