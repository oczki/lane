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

	/**
	* @route /add
	* @route /add/
	*/
	public function addAction()
	{
		$this->prework();

		if (!ApiHelper::canEdit($this->context->user))
			throw new UnprivilegedOperationException();

		if ($this->context->isSubmit)
		{
			$this->context->viewName = 'messages';

			$job = Api::jobFactory('add-list', [
				'user-name' => $this->context->user->name,
				'new-name' => InputHelper::getPost('name'),
				'new-visibility' => boolval(InputHelper::getPost('visible'))]);

			$statuses = Api::run($job);
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
			Api::run(ApiController::getJobsFromInput());

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
		if (!empty($guest))
			Auth::temporaryLogout();

		$this->preWork($userName);
		$this->context->layoutName = 'layout-bare';

		$this->context->canEdit = ApiHelper::canEdit($this->context->user);

		if ($id === null)
		{
			if (empty($this->context->lists))
				throw new InvalidListException(null, InvalidListException::REASON_PRIVATE);
			$list = reset($this->context->lists);
			$id = $list->id;
		}
		else
		{
			$list = ListService::getByUrlName($this->context->user, $id);
			if (empty($list))
			{
				Bootstrap::markReturn(
					'Return to ' . $userName . '\'s lane',
					\Chibi\UrlHelper::route('list', 'view', ['userName' => $userName]));

				throw new InvalidListException($id);
			}
		}

		if (!ApiHelper::canShowList($list))
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

		if (!ApiHelper::canEdit($this->context->user))
			throw new UnprivilegedOperationException();

		if ($this->context->isSubmit)
		{
			$file = InputHelper::getFile('file');
			if (!$file or !$file['tmp_name'])
				throw new SimpleException('No file provided.');

			$jsonText = file_get_contents($file['tmp_name']);
			$importJob = Api::jobFactory('import-list', [
				'user-name' => $this->context->user->name,
				'input-data' => $jsonText]);

			$statuses = Api::run($importJob);
			$newId = $statuses[0]['list-id'];

			Messenger::success('List imported successfully.');
			Bootstrap::forward(\Chibi\UrlHelper::route('list', 'view', [
				'userName' => $this->context->user->name,
				'id' => $newId]));
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

			$exportJob = Api::jobFactory('export-lists', [
				'user-name' => $this->context->user->name]);
			$statuses = Api::run($exportJob);
			$outputData = base64_decode($statuses[0]['output-data']);

			\Chibi\HeadersHelper::set('Content-Type', 'application/zip');
			\Chibi\HeadersHelper::set('Content-Disposition', 'inline; filename="' . $outFileName . '"');
			\Chibi\HeadersHelper::set('Content-Transfer-Encoding', 'binary');
			echo $outputData;
			exit;
		}
		else
		{
			$outFileName = $this->context->list->urlName . '.json';

			$exportJob = Api::jobFactory('export-list', [
				'user-name' => $this->context->user->name,
				'list-id' => $id]);
			$statuses = Api::run($exportJob);
			$outputData = $statuses[0]['output-data'];

			\Chibi\HeadersHelper::set('Content-Type', 'application/zip');
			\Chibi\HeadersHelper::set('Content-Disposition', 'inline; filename="' . $outFileName . '"');
			\Chibi\HeadersHelper::set('Content-Transfer-Encoding', 'binary');
			echo $outputData;
			exit;
		}
	}
}
