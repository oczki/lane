<?php
class ListController
{
	public function workWrapper($cb)
	{
		assert($this->context->isLoggedIn);
		$this->context->subLayoutName = 'layout-list';
		$this->context->lists = ListService::getByUserId($this->context->user->id);
		if (empty($this->context->lists))
		{
			ListService::createNewList($this->context->user, 'New blank list');
			$this->context->lists = ListService::getByUserId($this->context->user->id);
		}

		$cb();
	}

	/**
	* @route /list/view
	* @route /list/view/{id}
	* @validate id [a-zA-Z0-9_-]+
	*/
	public function viewAction($id = null)
	{
		if ($id === null)
			$id = reset($this->context->lists)->uniqueId;

		$list = ListService::getByUniqueId($id);
		if (empty($list))
		{
			Messenger::error('List with id = ' . $id . ' wasn\'t found.');
			return;
		}

		$this->context->list = $list;
	}
}
