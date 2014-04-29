<?php
define('DS', DIRECTORY_SEPARATOR);
$rootDir = __DIR__ . DS . '..' . DS;

//basic include calls, autoloader init
require_once $rootDir . 'lib' . DS . 'chibi-core' . DS . 'include.php';
require_once $rootDir . 'lib' . DS . 'TextCaseConverter' . DS . 'TextCaseConverter.php';
require_once $rootDir . 'lib' . DS . 'EmailObfuscator' . DS . 'EmailObfuscator.php';
require_once $rootDir . 'src' . DS . 'Message.php';
\Chibi\AutoLoader::registerFilesystem($rootDir . 'lib' . DS . 'chibi-sql');
\Chibi\AutoLoader::registerFilesystem(__DIR__);

//load config manually
$configPaths =
[
	$rootDir . DS . 'data' . DS . 'config.ini',
	$rootDir . DS . 'data' . DS . 'local.ini',
];
$config = new \Chibi\Config();
foreach ($configPaths as $path)
	if (file_exists($path))
		$config->loadIni($path);
$config->rootDir = $rootDir;

function getConfig()
{
	global $config;
	return $config;
}

\Chibi\Database::connect('sqlite', $rootDir . DS . 'data' . DS . 'db.sqlite', null, null);
