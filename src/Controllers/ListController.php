<?php
use Chibi\Database as Database;

class ListController
{
	private function preWork($userName = false)
	{
		if ($userName)
			$user = UserService::getByName($userName);
		elseif ($this->context->isLoggedIn and $this->context->userLogged)
			$user = $this->context->userLogged;

		if (!$user)
			throw new SimpleException('User "' . $userName . '" doesn\'t exist.');

		$this->context->user = $user;
		$this->context->lists = ListService::getByUserId($user->id);
		$this->context->canEdit =
			$this->context->isLoggedIn and
			$this->context->user->id == $this->context->userLogged->id;
	}

	/**
	* @route /u/{userName}
	* @route /u/{userName}/
	* @route /u/{userName}/{id}
	* @route /u/{userName}/{id}/
	* @validate userName [a-zA-Z0-9_-]+
	* @validate id [a-zA-Z0-9_-]+
	*/
	public function viewAction($userName, $id = null)
	{
		$this->preWork($userName);

		if ($id === null)
			$id = reset($this->context->lists)->uniqueId;

		$list = ListService::getByUniqueId($id);
		if (empty($list))
			throw new SimpleException('List with id = ' . $id . ' wasn\'t found.');

		$this->context->list = $list;
	}

	/**
	* @route /exec/add
	* @route /exec/add/
	*/
	public function addAction()
	{
		$this->preWork();

		if (!$this->context->canEdit)
			throw new SimpleException('Cannot edit this list.');

		if (!$this->context->isSubmit)
			return;

		try
		{
			$name = InputHelper::getPost('name');
			$visible = boolval(InputHelper::getPost('visible'));

			$validator = new Validator($name, 'list name');
			$validator->checkMinLength(1);
			$validator->checkMaxLength(20);

			ListService::createNewList($this->context->userLogged, $name, $visible);
		}
		catch (SimpleException $e)
		{
			Messenger::error($e->getMessage());
			return;
		}

		Messenger::success('List added successfully.');
	}
}
