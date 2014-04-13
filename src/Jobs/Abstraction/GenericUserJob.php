<?php
abstract class GenericUserJob extends AbstractJob
{
	public function getUser()
	{
		$user = UserService::getByName($this->getArgument('user-name'));
		if (!$user)
			throw new InvalidUserException($this->getArgument('user-name'));
		return $user;
	}
}
