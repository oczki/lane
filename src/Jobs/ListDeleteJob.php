<?php
/**
* Deletes whole list. Should be used with caution.
*
* @user-name: name of list owner
* @list-id: id of list
*/
class ListDeleteJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();
		$user = $this->getUser();

		ListService::delete($list);

		$lists = ListService::getByUser($user);

		if (empty($lists))
		{
			$job = Api::jobFactory('list-add', [
				'user-name' => $user->name,
				'new-list-name' => 'New blank list',
				'new-list-visibility' => true]);

			Api::run($job);
		}
	}
}
