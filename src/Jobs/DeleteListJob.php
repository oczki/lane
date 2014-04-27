<?php
/**
* Deletes whole list. Should be used with caution.
*
* @param user-name name of list owner
* @param list-id   id of list
*/
class DeleteListJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();
		$user = $this->getUser();

		ListService::delete($list);

		$lists = ListService::getByUser($user);

		if (empty($lists))
		{
			$job = Api::jobFactory('add-list', [
				'user-name' => $user->name,
				'new-name' => 'New blank list',
				'new-visibility' => true]);

			Api::run($job);
		}
	}
}
