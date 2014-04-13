<?php
/**
* Adds new list.
*
* @user-name: name of list owner
* @new-list-name: name of the new list
* @new-list-visibility: whether new list should be visible or not
*/
class ListAddJob extends GenericUserJob
{
	public function execute()
	{
		$user = $this->getUser();

		ListService::validateListName($this->getArgument('new-list-name'));

		$list = new ListEntity();
		$list->priority = ListService::getNewPriority($user);
		$list->userId = $user->id;
		$list->name = $this->getArgument('new-list-name');
		$list->visible = $this->getArgument('new-list-visibility');
		$list->content = new ListContent();

		$column1 = new ListColumn();
		$column1->name = 'First column';
		$column1->width = 45;
		$column1->align = ListColumn::ALIGN_LEFT;
		$column1->id = ++$list->content->lastContentId;

		$column2 = new ListColumn();
		$column2->name = 'Second column';
		$column2->width = 23;
		$column2->align = ListColumn::ALIGN_LEFT;
		$column2->id = ++$list->content->lastContentId;

		$column3 = new ListColumn();
		$column3->name = 'Centered column';
		$column3->width = 32;
		$column3->align = ListColumn::ALIGN_CENTER;
		$column3->id = ++$list->content->lastContentId;

		$row = new ListRow();
		$row->content = ['Point here and click the blue icon to edit.', '', ''];
		$row->id = ++$list->content->lastContentId;

		$list->content->columns []= $column1;
		$list->content->columns []= $column2;
		$list->content->columns []= $column3;
		$list->content->rows []= $row;

		$list->urlName = ListService::forgeUrlName($list);

		ListService::saveOrUpdate($list);

		return ['list-id' => $list->urlName];
	}
}
