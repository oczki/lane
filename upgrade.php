<?php
require_once 'src/core.php';

$upgradesPath = \Chibi\Registry::getContext()->rootDir . DS . 'src' . DS . 'Upgrades';
$upgrades = glob($upgradesPath . DS . '*.php');
natcasesort($upgrades);

$upgradeClasses = \Chibi\ReflectionHelper::loadClasses($upgrades);

foreach ($upgradeClasses as $upgradeClass)
{
	preg_match('/(\d+)/', $upgradeClass, $matches);
	$upgradeNumber = intval($matches[0]);

	try
	{
		$executed = UpgradeService::isExecuted($upgradeNumber);
	}
	catch (Exception $e)
	{
		$executed = false;
	}

	if (!$executed)
	{
		$upgradeClass::execute();
		UpgradeService::markAsExecuted($upgradeNumber, true);
	}
}

