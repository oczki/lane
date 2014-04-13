<?php
class ListAddColumnJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$listEntity = ListService::getByUrlName($owner, $this->arguments['list-id']);
		if (empty($listEntity))
			throw new InvalidListException($this->arguments['list-id']);

		ListService::validateColumnName($this->arguments['new-column-name']);

		if ($this->arguments['new-column-id'] <= $listEntity->content->lastContentId)
			throw new SimpleException('Column ID already exists: ' . $this->arguments['new-column-id'] . '.');

		if (!in_array($this->arguments['new-column-align'], ListService::getPossibleColumnAlign()))
			throw new SimpleException('Invalid column align: ' . $this->arguments['new-column-align'] . '.');

		$listEntity->content->lastContentId = $this->arguments['new-column-id'];

		$mul = count($listEntity->content->columns);
		$mul /= ($mul + 1);
		foreach ($listEntity->content->columns as $column)
			$column->width *= $mul;

		$column = new ListColumn();
		$column->id = $this->arguments['new-column-id'];
		$column->name = $this->arguments['new-column-name'];
		$column->align = !empty($this->arguments['new-column-align'])
			? $this->arguments['new-column-align']
			: ListColumn::ALIGN_LEFT;
		$column->width = 100. / (count($listEntity->content->columns) + 1);

		$listEntity->content->columns []= $column;
		foreach ($listEntity->content->rows as $row)
			$row->content = array_merge($row->content, ['']);

		ListService::saveOrUpdate($listEntity);
	}
}
