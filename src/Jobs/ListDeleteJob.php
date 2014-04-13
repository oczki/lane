<?php
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
