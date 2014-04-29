<?php
class IndexController
{
	public function indexAction()
	{
		$context = getContext();
		$context->allowIndexing = true;
		$context->viewName = 'index-index';

		if (Auth::isLoggedIn())
		{
			$lists = ListService::getByUserId(Auth::getLoggedInUser()->id);

			$url = \Chibi\Router::linkTo(['ListController', 'viewAction'], ['userName' => Auth::getLoggedInUser()->name]);

			ControllerHelper::forward($url);
		}
	}

	public function aboutAction()
	{
		$context = getContext();
		$context->allowIndexing = true;
		$context->viewName = 'index-about';
	}

	public function helpAction()
	{
		$context = getContext();
		$context->allowIndexing = true;
		$context->viewName = 'index-help';
	}

	public function apiDocumentationAction()
	{
		$context = getContext();
		$context->allowIndexing = true;
		$context->viewName = 'index-api-documentation';
	}

	public function exampleAction()
	{
		$url = \Chibi\Router::linkTo(['ListController', 'viewAction'], ['userName' => 'example']);
		ControllerHelper::forward($url);
	}
}
