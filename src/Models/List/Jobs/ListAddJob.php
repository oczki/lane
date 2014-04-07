<?php
class ListAddJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		ListService::validateListName($this->arguments['new-list-name']);

		$listEntity = new ListEntity();
		$listEntity->priority = ListService::getNewPriority($owner);
		$listEntity->userId = $owner->id;
		$listEntity->name = $this->arguments['new-list-name'];
		$listEntity->visible = $this->arguments['new-list-visibility'];
		$listEntity->content = new ListContent();

		$column1 = new ListColumn();
		$column1->name = 'First column';
		$column1->width = 45;
		$column1->align = ListColumn::ALIGN_LEFT;
		$column1->id = ++$listEntity->content->lastContentId;

		$column2 = new ListColumn();
		$column2->name = 'Second column';
		$column2->width = 23;
		$column2->align = ListColumn::ALIGN_LEFT;
		$column2->id = ++$listEntity->content->lastContentId;

		$column3 = new ListColumn();
		$column3->name = 'Centered column';
		$column3->width = 32;
		$column3->align = ListColumn::ALIGN_CENTER;
		$column3->id = ++$listEntity->content->lastContentId;

		$row = new ListRow();
		$row->content = ['Point here and click the blue icon to edit.', '', ''];
		$row->id = ++$listEntity->content->lastContentId;

		$listEntity->content->columns []= $column1;
		$listEntity->content->columns []= $column2;
		$listEntity->content->columns []= $column3;
		$listEntity->content->rows []= $row;

		$listEntity->urlName = ListService::forgeUrlName($listEntity);

		return ListService::saveOrUpdate($listEntity);
	}
}
