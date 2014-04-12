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
				throw new InvalidListException($listId);
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
	* @route /add
	* @route /add/
	*/
	public function addAction()
	{
		$this->prework();

		if (!ControllerHelper::canEditData($this->context->user))
			throw new UnprivilegedOperationException();

		if ($this->context->isSubmit)
		{
			$this->context->viewName = 'messages';

			$job = Api::jobFactory('list-add', [
				'new-list-name' => InputHelper::getPost('name'),
				'new-list-visibility' => boolval(InputHelper::getPost('visible'))]);

			$statuses = Api::run($job, $this->context->user);
			$newId = $statuses[0]['list-id'];

			Messenger::success('List added successfully.');
			Bootstrap::forward(\Chibi\UrlHelper::route('list', 'view', [
				'userName' => $this->context->user->name,
				'id' => $newId]));
		}
	}

	/**
	* @route /edit/{id}
	* @route /edit/{id}/
	* @validate id [^\/]+
	*/
	public function settingsAction($id)
	{
		$this->preWork(null, $id);

		if ($this->context->isSubmit)
		{
			$this->context->viewName = 'messages';
			Api::run(ApiController::getJobsFromInput(), $this->context->user);

			Messenger::success('List edited successfully.');
			Bootstrap::forward(\Chibi\UrlHelper::route('list', 'view', [
				'userName' => $this->context->user->name,
				'id' => $id]));
		}
	}

	/**
	* @route /css/{userName}/{id}
	* @route /css/{userName}/{id}/
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
				throw new InvalidListException(null, InvalidListException::REASON_PRIVATE);
		}

		$list = ListService::getByUrlName($this->context->user, $id);
		if (empty($list))
		{
			Bootstrap::markReturn(
				'Return to ' . $userName . '\'s lane',
				\Chibi\UrlHelper::route('list', 'view', ['userName' => $userName]));

			throw new InvalidListException($id);
		}

		if (!self::canShow($list))
		{
			Bootstrap::markReturn(
				'Return to ' . $userName . '\'s lane',
				\Chibi\UrlHelper::route('list', 'view', ['userName' => $userName]));

			throw new InvalidListException($id, InvalidListException::REASON_PRIVATE);
		}

		$this->context->list = $list;
		ListService::setLastViewedList($list);
	}

	/**
	* @route /import
	* @route /import/
	*/
	public function importAction()
	{
		$this->preWork();

		if (!ControllerHelper::canEditData($this->context->user))
			throw new UnprivilegedOperationException();

		if ($this->context->isSubmit)
		{
			$file = InputHelper::getFile('file');
			if ($file === null)
				throw new SimpleException('No file provided.');

			$jsonText = file_get_contents($file['tmp_name']);
			$list = ListService::unserialize($jsonText);
			$list->userId = $this->context->user->id;
			$list->priority = ListService::getNewPriority($this->context->user);
			$list->urlName = ListService::forgeUrlName($list);
			ListService::saveOrUpdate($list);

			$lists = ListService::getByUserId($this->context->user->id);
			$newList = array_pop($lists);

			Messenger::success('List imported successfully.');
			Bootstrap::forward(\Chibi\UrlHelper::route('list', 'view', [
				'userName' => $this->context->user->name,
				'id' => $newList->urlName]));
		}
	}

	/**
	* @route /export/{userName}
	* @route /export/{userName}/
	* @route /export/{userName}/{id}
	* @route /export/{userName}/{id}/
	* @validate userName [a-zA-Z0-9_-]+
	* @validate id [^\/]+
	*/
	public function exportAction($userName, $id = null)
	{
		$this->preWork($userName, $id);

		if ($id === null)
		{
			$outFileName = 'lane_export-' . date('Y-m-d_h-i-s') . '.zip';
			$zipPath = tempnam(sys_get_temp_dir(), 'lane-export');

			$lists = array_filter($this->context->lists, [__CLASS__, 'canShow']);
			if (empty($lists))
				throw new InvalidListException(null, InvalidListException::REASON_PRIVATE);

			$zip = new ZipArchive();
			if (!$zip->open($zipPath))
				throw new SimpleException('Failed to create ZIP archive.');

			foreach ($lists as $list)
				$zip->addFromString($list->urlName . '.json', ListService::serialize($list));

			$zip->close();

			\Chibi\HeadersHelper::set('Content-Type', 'application/zip');
			\Chibi\HeadersHelper::set('Content-Disposition', 'inline; filename="' . $outFileName . '"');
			\Chibi\HeadersHelper::set('Content-Transfer-Encoding', 'binary');
			readfile($zipPath);
			unlink($zipPath);
			exit;
		}
		else
		{
			if (!self::canShow($this->context->list))
				throw new UnprivilegedOperationException();
			$outFileName = $this->context->list->urlName . '.json';

			\Chibi\HeadersHelper::set('Content-Type', 'application/zip');
			\Chibi\HeadersHelper::set('Content-Disposition', 'inline; filename="' . $outFileName . '"');
			\Chibi\HeadersHelper::set('Content-Transfer-Encoding', 'binary');
			echo ListService::serialize($this->context->list);
			exit;
		}
	}
}
