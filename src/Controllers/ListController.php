<?php
class ListController
{
	public function workWrapper($cb)
	{
		$this->context->subLayoutName = 'layout-list';

		$cb();
	}

	/**
	* @route /u/{userName}
	* @route /u/{userName}/
	* @route /u/{userName}/{id}
	* @route /u/{userName}/{id}/
	* @validate userName [a-zA-Z0-9_-]+
	* @validate id [a-zA-Z0-9_-]+
	*/
	public function viewAction($userName = null, $id = null)
	{
		if ($userName === null)
		{
			if ($this->context->isLoggedIn)
				$user = $this->context->userLogged;
			else
				throw new SimpleException('No list to retrieve.');
		}
		else
		{
			$user = UserService::getByName($userName);
			if (!$user)
				throw new SimpleException('User doesn\'t exist.');
		}

		$this->context->user = $user;
		$this->context->lists = ListService::getByUserId($user->id);

		if ($id === null)
			$id = reset($this->context->lists)->uniqueId;

		$list = ListService::getByUniqueId($id);
		if (empty($list))
			throw new SimpleException('List with id = ' . $id . ' wasn\'t found.');

		$this->context->list = $list;
		$this->context->canEdit =
			$this->context->isLoggedIn and
			$list->userId == $this->context->userLogged->id;
	}
}
