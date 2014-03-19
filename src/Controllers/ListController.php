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
	* @route /a/{userName}/add
	* @route /a/{userName}/add/
	* @validate userName [a-zA-Z0-9_-]+
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

			$job = new ListAddJob($name, $visible);
			JobExecutor::execute($job, $this->context->userLogged);
		}
		catch (SimpleException $e)
		{
			Messenger::error($e->getMessage());
			return;
		}

		Messenger::success('List added successfully.');
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

		throw new NotImplementedException();
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
			{
				Messenger::error('Looks like all of user\'s lists are private.');
				return;
			}
		}

		$list = ListService::getByUrlName($this->context->user, $id);
		if (empty($list))
		{
			Messenger::error('List with id = ' . $id . ' wasn\'t found.');
			return;
		}

		if (!ListService::canShow($list))
		{
			Messenger::error('List with id = ' . $id . ' is not available for public.');
			return;
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
