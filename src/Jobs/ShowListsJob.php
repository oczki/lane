<?php
class ShowListsJob extends GenericUserJob
{
	public function execute()
	{
		$lists = ListService::getByUser($this->getUser());
		return array_map(function($list)
			{
				return [
					'list-name' => $list->name,
					'list-id' => $list->urlName
				];
			}, array_values($lists));
	}
}
