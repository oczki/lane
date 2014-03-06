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

	public static function forward($url)
	{
		\Chibi\UrlHelper::forward($url);
		exit;
	}

	public static function getUptime()
	{
		return microtime(true) - \Chibi\Registry::getContext()->scriptStartTime;
	}

	public function workWrapper($workCallback)
	{
		session_start();

		\Chibi\AssetViewDecorator::setStylesheetsFolder('/css');
		\Chibi\AssetViewDecorator::setScriptsFolder('/js');

		$this->context->viewDecorators []= new \Chibi\AssetViewDecorator();
		$this->context->viewDecorators []= new \Chibi\PrettyPrintViewDecorator();
		$this->context->layoutName = isset($_GET['simple'])
			? 'layout-simple'
			: 'layout-normal';
		$this->context->subLayoutName = 'layout-big';

		$this->context->isSubmit = $_SERVER['REQUEST_METHOD'] == 'POST';
		$this->context->isLoggedIn = isset($_SESSION['logged-in']);
		if ($this->context->isLoggedIn)
			$this->context->userLogged = UserService::getById($_SESSION['user-id']);
		else
			$this->context->userLogged = null;

		try
		{
			$this->render($workCallback);
		}
		catch (\Chibi\UnhandledRouteException $e)
		{
			Messenger::error('Error 404.');
			$this->context->viewName = null;
			$this->render();
		}
		catch (SimpleException $e)
		{
			Messenger::error($e->getMessage());
			$this->context->viewName = null;
			$this->render();
		}
		catch (Exception $e)
		{
			$this->context->exception = $e;
			$this->context->viewName = 'error-exception';
			$this->render();
		}
	}
}
