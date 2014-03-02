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
			\Chibi\UrlHelper::forward(\Chibi\UrlHelper::route('list', 'view',
				['userName' => $this->context->userLogged->name]));
		}
	}
}
