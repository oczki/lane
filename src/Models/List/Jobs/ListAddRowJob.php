<?php
class ListAddRowJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListService::getByUrlName($owner, $this->arguments['list-id']);
		if (empty($listEntity))
			throw new InvalidListException($this->arguments['list-id']);

		if (empty($this->arguments['row-content']))
			$this->arguments['new-row-content'] = array_fill(0, count($listEntity->content->columns), '');

		if ($this->arguments['new-row-id'] <= $listEntity->content->lastContentId)
			throw new SimpleException('Row ID already exists: ' . $this->arguments['new-row-id'] . '.');

		if (count($this->arguments['new-row-content']) != count($listEntity->content->columns))
			throw new SimpleException('Invalid column count.');

		$row = new ListRow();
		$row->id = $this->arguments['new-row-id'];
		$row->content = $this->arguments['new-row-content'];

		$listEntity->content->lastContentId = $row->id;

		$listEntity->content->rows []= $row;
		ListService::saveOrUpdate($listEntity);
	}
}
