<?php
class Bootstrap
{
	public function render($callback = null)
	{
		if ($callback !== null)
			$callback();
		else
			(new \Chibi\View())->renderFile($this->context->layoutName);
	}

	public function workWrapper($workCallback)
	{
		session_start();

		\Chibi\AssetViewDecorator::setStylesheetsFolder('/css');
		\Chibi\AssetViewDecorator::setScriptsFolder('/js');

		$this->context->viewDecorators []= new \Chibi\AssetViewDecorator();
		$this->context->viewDecorators []= new \Chibi\PrettyPrintViewDecorator();
		$this->context->layoutName = 'layout-normal';

		$this->context->isSubmit = $_SERVER['REQUEST_METHOD'] == 'POST';
		$this->context->isLoggedIn = isset($_SESSION['logged-in']);
		if ($this->context->isLoggedIn)
			$this->context->user = UserService::getById($_SESSION['user-id']);
		else
			$this->context->user = null;

		try
		{
			$this->render($workCallback);
		}
		catch (Exception $e)
		{
			$this->context->exception = $e;
			$this->context->viewName = 'error-exception';
			$this->render();
		}
	}
}
