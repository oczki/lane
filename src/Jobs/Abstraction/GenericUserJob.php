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
}
