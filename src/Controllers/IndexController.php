<?php
class IndexController
{
	/**
	* @route /
	*/
	public function indexAction()
	{
		$this->context->allowIndexing = true;

		if (Auth::isLoggedIn())
		{
			$lists = ListService::getByUserId(Auth::getLoggedInUser()->id);

			$url = \Chibi\UrlHelper::route('list', 'view', ['userName' => Auth::getLoggedInUser()->name]);

			Bootstrap::forward($url);
		}
	}

	/**
	* @route /about
	* @route /about/
	*/
	public function aboutAction()
	{
		$this->context->allowIndexing = true;
	}

	/**
	* @route /help
	* @route /help/
	*/
	public function helpAction()
	{
		$this->context->allowIndexing = true;
	}

	/**
	* @route /api-docs
	* @route /api-docs/
	*/
	public function apiDocumentationAction()
	{
		$this->context->allowIndexing = true;
	}

	/**
	* @route /example
	* @route /example/
	*/
	public function exampleAction()
	{
		$url = \Chibi\UrlHelper::route('list', 'view', ['userName' => 'test_subject']);
		Bootstrap::forward($url);
	}
}
