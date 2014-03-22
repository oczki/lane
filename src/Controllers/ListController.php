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

		$this->context->allowIndexing = false;
		$this->context->layoutName = 'layout-bare';
		$this->context->user = $user;
		$this->context->lists = ListService::getByUserId($user->id);
		$this->context->canEdit =
			$this->context->isLoggedIn and
			$this->context->user->id == $this->context->userLogged->id;
	}

	/**
	* @route /a/{userName}/add
	* @route /a/{userName}/add/
	* @validate userName [a-zA-Z0-9_-]+
	*/
	public function addAction()
	{
		$this->preWork();

		if (!$this->context->canEdit)
			throw new SimpleException('Cannot add new list.');

		if (!$this->context->isSubmit)
			return;

		try
		{
			$name = InputHelper::getPost('name');
			$visible = boolval(InputHelper::getPost('visible'));

			$job = new ListAddJob($name, $visible);
			JobExecutor::execute($job, $this->context->userLogged);

			$lists = ListService::getByUserId($this->context->userLogged->id);
			$newList = array_pop($lists);
		}
		catch (SimpleException $e)
		{
			Messenger::error($e->getMessage());
			return;
		}

		Messenger::success('List added successfully.');
		Bootstrap::forward(\Chibi\UrlHelper::route('list', 'view', [
			'userName' => $this->context->userLogged->name,
			'id' => $newList->urlName]));
	}

	/**
	* @route /a/{userName}/{id}/settings
	* @route /a/{userName}/{id}/settings/
	* @validate userName [a-zA-Z0-9_-]+
	* @validate id [^\/]+
	*/
	public function settingsAction($userName, $id)
	{
		$this->preWork($userName);

		$list = ListService::getByUrlName($this->context->user, $id);
		if (empty($list))
			throw new SimpleException('List with id = ' . $id . ' wasn\'t found.');
		$this->context->list = $list;

		if (!$this->context->canEdit)
			throw new SimpleException('Cannot edit this list.');

		if (!$this->context->isSubmit)
			return;

		$this->execAction();
		Bootstrap::forward(\Chibi\UrlHelper::route('list', 'view', [
			'userName' => $this->context->userLogged->name,
			'id' => $id]));
	}

	/**
	* @route /a/{userName}/{id}/css
	* @route /a/{userName}/{id}/css/
	*/
	public function customCssAction($userName, $id = null)
	{
		\Chibi\HeadersHelper::set('Content-Type', 'text/css');

		try
		{
			$this->preWork($userName);

			$list = ListService::getByUrlName($this->context->user, $id);
			if (empty($list))
				throw new SimpleException('List with id = ' . $id . ' wasn\'t found.');

			echo $list->content->css;
		}
		catch (Exception $e)
		{
		}

		exit;
	}

	/**
	* @route /u/{userName}
	* @route /u/{userName}/
	* @route /u/{userName}/{id}
	* @route /u/{userName}/{id}/
	* @validate userName [a-zA-Z0-9_-]+
	* @validate id [^\/]+
	*/
	public function viewAction($userName, $id = null)
	{
		$this->preWork($userName);

		if ($id === null)
		{
			$id = null;
			foreach ($this->context->lists as $list)
			{
				if (ListService::canShow($list))
				{
					$id = $list->urlName;
					break;
				}
			}
			if (empty($id))
				throw new SimpleException('Looks like all of user\'s lists are private.');
		}

		$list = ListService::getByUrlName($this->context->user, $id);
		if (empty($list))
		{
			Bootstrap::markReturn(
				'Return to ' . $userName . '\'s lane',
				\Chibi\UrlHelper::route('list', 'view', ['userName' => $userName]));

			throw new SimpleException('List with id = ' . $id . ' wasn\'t found.');
		}

		if (!ListService::canShow($list))
		{
			Bootstrap::markReturn(
				'Return to ' . $userName . '\'s lane',
				\Chibi\UrlHelper::route('list', 'view', ['userName' => $userName]));

			throw new SimpleException('List with id = ' . $id . ' is not available for public.');
		}

		$this->context->list = $list;
	}

	/**
	* @route /exec
	* @route /exec/
	*/
	public function execAction()
	{
		$this->preWork();

		if (!$this->context->canEdit)
			throw new SimpleException('Cannot edit this list.');

		if (!$this->context->isSubmit)
			return;

		$jobs = [];
		$jobTexts = InputHelper::getPost('jobs');
		if ($jobTexts === null)
			$jobTexts = [];
		foreach ($jobTexts as $jobText)
		{
			$job = JobExecutor::parse($jobText);
			$jobs []= $job;
		}

		JobExecutor::execute($jobs, $this->context->userLogged);

		$this->context->viewName = 'messages';
		Messenger::success(count($jobs) . ' jobs executed successfully.');
	}
}
