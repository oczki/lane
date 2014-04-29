<?php
$scriptStartTime = microtime(true);
require_once '../src/core.php';

session_start();

$query = rtrim($_SERVER['REQUEST_URI'], '/');
$context = new StdClass;
$context->scriptStartTime = $scriptStartTime;
$context->query = $query;
$context->usingSimpleLayout = isset($_GET['simple']);
$context->layoutName = $context->usingSimpleLayout
	? 'layout-bare'
	: 'layout-logo';
$context->allowIndexing = false;

$context->isSubmit = $_SERVER['REQUEST_METHOD'] == 'POST';
if (!Auth::isLoggedIn())
	Auth::loginFromCookie();

\Chibi\Router::register(['IndexController', 'indexAction'], 'GET', '');
\Chibi\Router::register(['IndexController', 'helpAction'], 'GET', '/help');
\Chibi\Router::register(['IndexController', 'aboutAction'], 'GET', '/about');
\Chibi\Router::register(['IndexController', 'apiDocumentationAction'], 'GET', '/api-docs');

\Chibi\Router::register(['ApiController', 'runAction'], 'POST', '/api');

\Chibi\Router::register(['AuthController', 'loginAction'], 'GET', '/auth/login');
\Chibi\Router::register(['AuthController', 'loginAction'], 'POST', '/auth/login');
\Chibi\Router::register(['AuthController', 'logoutAction'], 'GET', '/auth/logout');
\Chibi\Router::register(['AuthController', 'logoutAction'], 'POST', '/auth/logout');
\Chibi\Router::register(['AuthController', 'resetPasswordAction'], 'POST', '/auth/reset-password');
\Chibi\Router::register(['AuthController', 'resetPasswordConfirmAction'], 'GET', '/auth/reset-password-confirm/{userName}/{token}', ['userName' => '[a-zA-Z0-9_-]+', 'token' => '[a-fA-F0-9]+']);
\Chibi\Router::register(['AuthController', 'registerAction'], 'GET', '/register');
\Chibi\Router::register(['AuthController', 'registerAction'], 'POST', '/register');

\Chibi\Router::register(['UserController', 'accountSettingsAction'], 'GET', '/settings');
\Chibi\Router::register(['UserController', 'accountSettingsAction'], 'POST', '/settings');
\Chibi\Router::register(['UserController', 'deleteAccountAction'], 'GET', '/delete-account');
\Chibi\Router::register(['UserController', 'deleteAccountAction'], 'POST', '/delete-account');

$listIdValidator = '[^\/]+';
$userNameValidator = '[a-zA-Z0-9_-]+';
\Chibi\Router::register(['ListController', 'viewAction'], 'GET', '/u/{userName}', ['userName' => $userNameValidator]);
\Chibi\Router::register(['ListController', 'viewAction'], 'GET', '/u/{userName}/{id}', ['userName' => $userNameValidator, 'id' => $listIdValidator]);
\Chibi\Router::register(['ListController', 'viewAction'], 'GET', '/u/{userName}/{id}/{guest}', ['userName' => $userNameValidator, 'id' => $listIdValidator, 'guest' => 'guest|']);
\Chibi\Router::register(['ListController', 'addAction'], 'GET', '/add');
\Chibi\Router::register(['ListController', 'addAction'], 'POST', '/add');
\Chibi\Router::register(['ListController', 'settingsAction'], 'GET', '/edit/{id}', ['id' => $listIdValidator]);
\Chibi\Router::register(['ListController', 'settingsAction'], 'POST', '/edit/{id}', ['id' => $listIdValidator]);
\Chibi\Router::register(['ListController', 'importAction'], 'GET', '/import');
\Chibi\Router::register(['ListController', 'importAction'], 'POST', '/import');
\Chibi\Router::register(['ListController', 'exportAction'], 'GET', '/export/{userName}', ['userName' => $userNameValidator]);
\Chibi\Router::register(['ListController', 'exportAction'], 'GET', '/export/{userName}/{id}', ['userName' => $userNameValidator, 'id' => $listIdValidator]);
\Chibi\Router::register(['ListController', 'customCssAction'], 'GET', '/css/{userName}/{id}', ['userName' => $userNameValidator, 'id' => $listIdValidator]);

function getContext()
{
	global $context;
	return $context;
}

function render()
{
	$context = getContext();
	\Chibi\View::render($context->layoutName, $context);
}

function getUptime()
{
	return microtime(true) - getContext()->scriptStartTime;
}

try
{
	try
	{
		\Chibi\Router::run($query);
		render();
	}
	catch (\Chibi\UnhandledRouteException $e)
	{
		\Chibi\Util\Headers::setCode(404);
		ControllerHelper::markReturn();
		throw new SimpleException('Page not found.');
	}
}

catch (SimpleException $e)
{
	\Chibi\Util\Headers::setCode(400);
	ControllerHelper::markReturn();
	Messenger::error($e->getMessage());
	$context->layoutName = 'layout-logo';
	$context->viewName = null;
	render();
}

catch (Exception $e)
{
	\Chibi\Util\Headers::setCode(400);
	$context->exception = $e;
	$context->layoutName = 'layout-logo';
	$context->viewName = 'error-exception';
	render();
	Logger::logException($e);
}
