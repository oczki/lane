<?php
use Chibi\Database as Database;

class ListController
{
	private function preWork($userName = false, $listId = null)
	{
		ControllerHelper::attachUser($userName);
		ControllerHelper::attachLists($userName);
		$context = getContext();
		if ($listId !== null)
		{
			$context->list = ListService::getByUrlName($context->user, $listId);
			if (empty($context->list))
				throw new InvalidListException($listId);
		}
	}

	public function addAction()
	{
		$this->prework();
		$context = getContext();
		$context->viewName = 'list-add';

		if (!ApiHelper::canEdit($context->user))
			throw new UnprivilegedOperationException();

		if (!$context->isSubmit)
			return;

		$context->viewName = 'messages';

		$job = Api::jobFactory('add-list', [
			'user-name' => $context->user->name,
			'new-name' => InputHelper::getPost('name'),
			'new-visibility' => boolval(InputHelper::getPost('visible'))]);

		$statuses = Api::run($job);
		$newId = $statuses[0]['list-id'];

		Messenger::success('List added successfully.');
		ControllerHelper::forward(\Chibi\Router::linkTo(['ListController', 'viewAction'], [
			'userName' => $context->user->name,
			'id' => $newId]));
	}

	public function settingsAction($id)
	{
		$this->preWork(null, $id);
		$context = getContext();
		$context->viewName = 'list-settings';

		if (!$context->isSubmit)
			return;

		$context->viewName = 'messages';
		Api::run(ApiController::getJobsFromInput());

		Messenger::success('List edited successfully.');
		ControllerHelper::forward(\Chibi\Router::linkTo(['ListController', 'viewAction'], [
			'userName' => $context->user->name,
			'id' => $id]));
	}

	public function customCssAction($userName, $id = null)
	{
		\Chibi\Util\Headers::set('Content-Type', 'text/css');
		$context = getContext();

		try
		{
			$this->preWork($userName, $id);

			echo $context->list->content->customCss;
		}
		catch (Exception $e)
		{
		}

		exit;
	}

	public function viewAction($userName, $id = null, $guest = false)
	{
		if (!empty($guest))
			Auth::temporaryLogout();

		$this->preWork($userName);
		$context = getContext();
		$context->layoutName = 'layout-list';
		$context->viewName = 'list-view';
		$context->canEdit = ApiHelper::canEdit($context->user);

		if ($id === null)
		{
			if (empty($context->lists))
				throw new InvalidListException(null, InvalidListException::REASON_PRIVATE);
			$list = reset($context->lists);
			$id = $list->id;
		}
		else
		{
			$list = ListService::getByUrlName($context->user, $id);
			if (empty($list))
			{
				ControllerHelper::markReturn(
					'Return to ' . $userName . '\'s lane',
					\Chibi\Router::linkTo(['ListController', 'viewAction'], ['userName' => $userName]));

				throw new InvalidListException($id);
			}
		}

		if (!ApiHelper::canShowList($list))
		{
			ControllerHelper::markReturn(
				'Return to ' . $userName . '\'s lane',
				\Chibi\Router::linkTo(['ListController', 'viewAction'], ['userName' => $userName]));

			throw new InvalidListException($id, InvalidListException::REASON_PRIVATE);
		}

		$context->list = $list;
		ListService::setLastViewedList($list);
	}

	public function importAction()
	{
		$this->preWork();
		$context = getContext();
		$context->viewName = 'list-import';

		if (!ApiHelper::canEdit($context->user))
			throw new UnprivilegedOperationException();

		if (!$context->isSubmit)
			return;

		$file = InputHelper::getFile('file');
		if (!$file or !$file['tmp_name'])
			throw new SimpleException('No file provided.');

		$jsonText = file_get_contents($file['tmp_name']);
		$importJob = Api::jobFactory('import-list', [
			'user-name' => $context->user->name,
			'input-data' => $jsonText]);

		$statuses = Api::run($importJob);
		$newId = $statuses[0]['list-id'];

		Messenger::success('List imported successfully.');
		ControllerHelper::forward(\Chibi\Router::linkTo(['ListController', 'viewAction'], [
			'userName' => $context->user->name,
			'id' => $newId]));
	}

	public function exportAction($userName, $id = null)
	{
		$this->preWork($userName, $id);
		$context = getContext();

		if ($id === null)
		{
			$outFileName = 'lane_export-' . date('Y-m-d_h-i-s') . '.zip';

			$exportJob = Api::jobFactory('export-lists', [
				'user-name' => $context->user->name]);
			$statuses = Api::run($exportJob);
			$outputData = base64_decode($statuses[0]['output-data']);

			\Chibi\Util\Headers::set('Content-Type', 'application/zip');
			\Chibi\Util\Headers::set('Content-Disposition', 'inline; filename="' . $outFileName . '"');
			\Chibi\Util\Headers::set('Content-Transfer-Encoding', 'binary');
			echo $outputData;
			exit;
		}
		else
		{
			$outFileName = $context->list->urlName . '.json';

			$exportJob = Api::jobFactory('export-list', [
				'user-name' => $context->user->name,
				'list-id' => $id]);
			$statuses = Api::run($exportJob);
			$outputData = $statuses[0]['output-data'];

			\Chibi\Util\Headers::set('Content-Type', 'application/zip');
			\Chibi\Util\Headers::set('Content-Disposition', 'inline; filename="' . $outFileName . '"');
			\Chibi\Util\Headers::set('Content-Transfer-Encoding', 'binary');
			echo $outputData;
			exit;
		}
	}
}
