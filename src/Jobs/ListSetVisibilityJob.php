<?php
class ListSetVisibilityJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$list->visible = boolval($this->getArgument('new-list-visibility'));

		ListService::saveOrUpdate($list);
	}
}
