<?php
class IndexController
{
	/**
	* @route /
	*/
	public function indexAction()
	{
		if ($this->context->isLoggedIn)
		{
			$lists = ListService::getByUserId($this->context->userLogged->id);

			$url = \Chibi\UrlHelper::route('list', 'view', [
				'userName' => $this->context->userLogged->name,
				'id' => reset($lists)->uniqueId]);

			Bootstrap::forward($url);
		}
	}
}
