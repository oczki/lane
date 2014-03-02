<?php
use Chibi\Database as Database;

class ListController
{
	private function preWork($userName)
	{
		$user = UserService::getByName($userName);
		if (!$user)
			throw new SimpleException('User "' . $userName . '" doesn\'t exist.');

		$this->context->subLayoutName = 'layout-list';
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
	* @route /ml/{userName}
	* @route /ml/{userName}/
	* @validate userName [a-zA-Z0-9_-]+
	*/
	public function manageAction($userName = null)
	{
		$this->preWork($userName);

		if (!$this->context->canEdit)
			throw new SimpleException('Cannot edit this list.');

		if (!$this->context->isSubmit)
			return;

		Database::transaction(function()
		{
			Messenger::info(TextHelper::keepWhiteSpace(print_r($_POST, true)));
			throw new NotImplementedException();
		});

		$this->context->lists = ListService::getByUserId($this->context->user->id);
	}

	/**
	* @route /ml/{userName}/add
	* @route /ml/{userName}/add/
	* @validate userName [a-zA-Z0-9_-]+
	*/
	public function addAction($userName)
	{
		$this->preWork($userName);

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
		}
		catch (SimpleException $e)
		{
			Messenger::error($e->getMessage());
			return;
		}

		ListService::createNewList($this->context->user, $name, $visible);
		\Chibi\UrlHelper::forward('/');
	}
}
