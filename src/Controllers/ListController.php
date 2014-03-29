<?php
use Chibi\Database as Database;

class ListController
{
	private function preWork($userName = false)
	{
		ControllerHelper::attachUser($userName);
		ControllerHelper::attachLists();
		ControllerHelper::setLayout();
	}

	/**
	* @route /a/{userName}/add
	* @route /a/{userName}/add/
	* @validate userName [a-zA-Z0-9_-]+
	*/
	public function addAction($userName)
	{
		$this->preWork($userName);

		if (!$this->context->canEdit)
			throw new SimpleException('Cannot add new list.');

		if (!$this->context->isSubmit)
			return;

		try
		{
			$job = JobHelper::factory('list-add', [
				'new-list-name' => InputHelper::getPost('name'),
				'new-list-visibility' => boolval(InputHelper::getPost('visible'))]);

			JobExecutor::execute($job, $this->context->user);

			$lists = ListService::getByUserId($this->context->user->id);
			$newList = array_pop($lists);
		}
		catch (SimpleException $e)
		{
			Messenger::error($e->getMessage());
			return;
		}

		Messenger::success('List added successfully.');
		Bootstrap::forward(\Chibi\UrlHelper::route('list', 'view', [
			'userName' => $this->context->user->name,
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

		if ($this->context->isSubmit)
		{
			$this->context->viewName = 'messages';
			ControllerHelper::runJobExecutorForCurrentContext();

			Bootstrap::forward(\Chibi\UrlHelper::route('list', 'view', [
				'userName' => $this->context->user->name,
				'id' => $id]));
		}
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

			echo $list->content->customCss;
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
		ListService::setLastViewedList($list);
	}
}
