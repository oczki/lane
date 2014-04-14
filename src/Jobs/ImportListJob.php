<?php
/**
* Imports one previously exported list, effectively creating a new list.
*
* @user-name: name of list owner
* @input-data: data of list to import
*/
class ImportListJob extends GenericUserJob
{
	public function execute()
	{
		$user = $this->getUser();

		$list = ListService::unserialize($this->getArgument('input-data'));
		$list->userId = $user->id;
		$list->priority = ListService::getNewPriority($user);
		$list->urlName = ListService::forgeUrlName($list);
		ListService::saveOrUpdate($list);

		return ['list-id' => $list->urlName];
	}
}
