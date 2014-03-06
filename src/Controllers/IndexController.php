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
			Bootstrap::forward(\Chibi\UrlHelper::route('list', 'view',
				['userName' => $this->context->userLogged->name]));
		}
	}
}
