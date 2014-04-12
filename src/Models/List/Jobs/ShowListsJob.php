<?php
class ShowListsJob extends AbstractJob
{
	public function execute(UserEntity $owner)
	{
		$lists = ListService::getByUser($owner);
		return array_map(function($list) { return ['list-name' => $list->name, 'list-id' => $list->urlName]; }, array_values($lists));
	}
}
