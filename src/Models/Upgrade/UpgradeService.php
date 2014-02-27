<?php
class UpgradeService
{
	public static function isExecuted($upgradeNumber)
	{
		return UpgradeDao::isExecuted($upgradeNumber);
	}

	public static function markAsExecuted($upgradeNumber, $executed)
	{
		UpgradeDao::markAsExecuted($upgradeNumber, $executed);
	}
}
