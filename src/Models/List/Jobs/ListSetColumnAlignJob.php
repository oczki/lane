<?php
class ListSetColumnAlignJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		if (!in_array($this->arguments['new-column-align'], ListService::getPossibleColumnAlign()))
			throw new SimpleException('Invalid column align: ' . $this->arguments['new-column-align'] . '.');

		$listEntity = ListService::getByUrlName($owner, $this->arguments['list-id']);
		if (empty($listEntity))
			throw new InvalidListException($this->arguments['list-id']);

		$pos = ListService::getColumnPos($listEntity, $this->arguments['column-id']);

		$listEntity->content->columns[$pos]->align = $this->arguments['new-column-align'];

		ListService::saveOrUpdate($listEntity);
	}
}
