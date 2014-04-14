<?php
abstract class GenericUserJob extends AbstractJob
{
	public function requiresAuthentication()
	{
		return true;
	}

	public function getUser()
	{
		$user = UserService::getByName($this->getArgument('user-name'));
		if (!$user)
			throw new InvalidUserException($this->getArgument('user-name'));

		if ($this->requiresAuthentication())
		{
			$apiUser = Api::getApiUser();
			if (!$apiUser or $user->id != $apiUser->id)
				throw new UnprivilegedOperationException();
		}

		return $user;
	}

	public function getLists()
	{
		$lists = array_values(ListService::getByUser($this->getUser()));
		$lists = array_filter($lists, function($list)
		{
			return ApiHelper::canShowList($list);
		});
		return $lists;
	}
}
