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
		\Chibi\HeadersHelper::setCode(303);
		\Chibi\UrlHelper::forward($url);
		exit;
	}

	public static function getUptime()
	{
		return microtime(true) - \Chibi\Registry::getContext()->scriptStartTime;
	}

	public static function markReturn($linkText = null, $link = null)
	{
		$context = \Chibi\Registry::getContext();
		if (isset($context->returnLinkText))
			return;
		$context->returnLinkText = $linkText ?: 'Return to lane';
		$context->returnLink = $link ?: \Chibi\UrlHelper::route('index', 'index');
	}

	public function workWrapper($workCallback)
	{
		session_start();

		\Chibi\AssetViewDecorator::setStylesheetsFolder('/css');
		\Chibi\AssetViewDecorator::setScriptsFolder('/js');

		$this->config->chibi->baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/';

		$this->context->viewDecorators []= new \Chibi\AssetViewDecorator();
		$this->context->viewDecorators []= new \Chibi\PrettyPrintViewDecorator();
		$this->context->layoutName = isset($_GET['simple'])
			? 'layout-bare'
			: 'layout-logo';
		$this->context->allowIndexing = false;

		$this->context->isSubmit = $_SERVER['REQUEST_METHOD'] == 'POST';
		if (!Auth::isLoggedIn())
			Auth::loginFromCookie();

		try
		{
			try
			{
				$this->render($workCallback);
			}
			catch (\Chibi\UnhandledRouteException $e)
			{
				\Chibi\HeadersHelper::setCode(404);
				self::markReturn();
				throw new SimpleException('Page not found.');
			}
		}

		catch (SimpleException $e)
		{
			\Chibi\HeadersHelper::setCode(400);
			self::markReturn();
			Messenger::error($e->getMessage());
			$this->context->layoutName = 'layout-logo';
			$this->context->viewName = null;
			$this->render();
		}

		catch (Exception $e)
		{
			\Chibi\HeadersHelper::setCode(400);
			$this->context->exception = $e;
			$this->context->layoutName = 'layout-logo';
			$this->context->viewName = 'error-exception';
			$this->render();
			Logger::logException($e);
		}
	}
}
