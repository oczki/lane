<?php
use Chibi\Database as Database;

class ListController
{
	private function preWork($userName = false, $listId = null)
	{
		ControllerHelper::attachUser($userName);
		ControllerHelper::attachLists($userName);
		if ($listId !== null)
		{
			$this->context->list = ListService::getByUrlName($this->context->user, $listId);
			if (empty($this->context->list))
				throw new SimpleException('List with id = ' . $listId . ' wasn\'t found.');
		}
	}

	public static function canShow(ListEntity $listEntity)
	{
		$context = \Chibi\Registry::getContext();
		if ($listEntity->visible)
			return true;

		$owner = UserService::getById($listEntity->userId);
		return ControllerHelper::canEditData($owner);
	}

	/**
	* @route /a/{userName}/add
	* @route /a/{userName}/add/
	* @validate userName [a-zA-Z0-9_-]+
	*/
	public function addAction($userName)
	{
		$this->preWork($userName);

		if ($this->context->isSubmit)
		{
			$job = JobHelper::factory('list-add', [
				'new-list-name' => InputHelper::getPost('name'),
				'new-list-visibility' => boolval(InputHelper::getPost('visible'))]);

			ControllerHelper::executeJobsSafely([$job], $this->context->user);

			$lists = ListService::getByUserId($this->context->user->id);
			$newList = array_pop($lists);

			Messenger::success('List added successfully.');
			Bootstrap::forward(\Chibi\UrlHelper::route('list', 'view', [
				'userName' => $this->context->user->name,
				'id' => $newList->urlName]));
		}
	}

	/**
	* @route /a/{userName}/{id}/settings
	* @route /a/{userName}/{id}/settings/
	* @validate userName [a-zA-Z0-9_-]+
	* @validate id [^\/]+
	*/
	public function settingsAction($userName, $id)
	{
		$this->preWork($userName, $id);

		if ($this->context->isSubmit)
		{
			$this->context->viewName = 'messages';
			ControllerHelper::executeJobsSafely(ControllerHelper::getJobsFromInput(), $this->context->user);

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
			$this->preWork($userName, $id);

			echo $this->context->list->content->customCss;
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
	* @route /u/{userName}/{id}/{guest}
	* @route /u/{userName}/{id}/{guest}/
	* @validate userName [a-zA-Z0-9_-]+
	* @validate id [^\/]+
	* @validate guest guest|
	*/
	public function viewAction($userName, $id = null, $guest = false)
	{
		$this->preWork($userName);
		$this->context->layoutName = 'layout-bare';

		if (!empty($guest))
			ControllerHelper::revokePrivileges($this->context->user);

		$this->context->canEdit = ControllerHelper::canEditData($this->context->user);

		if ($id === null)
		{
			$id = null;
			foreach ($this->context->lists as $list)
			{
				if (self::canShow($list))
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

		if (!self::canShow($list))
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
