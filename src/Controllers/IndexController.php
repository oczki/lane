<?php
class IndexController
{
	/**
	* @route /
	*/
	public function indexAction()
	{
		$this->context->allowIndexing = true;

		if ($this->context->isLoggedIn)
		{
			$lists = ListService::getByUserId($this->context->userLogged->id);

			$url = \Chibi\UrlHelper::route('list', 'view', ['userName' => $this->context->userLogged->name]);

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
		throw new NotImplementedException();
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
