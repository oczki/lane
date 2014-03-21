<?php
require_once 'src/core.php';

function download($source, $destination = null)
{
	echo 'Downloading: ' . $source . '...' . PHP_EOL;
	flush();

	if ($destination !== null and file_exists($destination))
		return file_get_contents($destination);

	$content = file_get_contents($source);
	if ($destination !== null)
	{
		$dir = dirname($destination);
		if (!file_exists($dir))
			mkdir($dir, 0755, true);

		file_put_contents($destination, $content);
	}
	return $content;
}

//jQuery resizableColumns
download(
	'https://raw.githubusercontent.com/dobtco/jquery-resizable-columns/gh-pages/dist/jquery.resizableColumns.min.js',
	'public_html' . DS . 'js' . DS . 'jquery.resizableColumns.min.js');

download(
	'https://raw.githubusercontent.com/dobtco/jquery-resizable-columns/gh-pages/css/jquery.resizableColumns.css',
	'public_html' . DS . 'css' . DS . 'jquery.resizableColumns.css');

//jQuery Tablesorter
download(
	'https://raw.githubusercontent.com/Mottie/tablesorter/master/js/jquery.tablesorter.min.js',
	'public_html' . DS . 'js' . DS . 'jquery.tablesorter.min.js');
