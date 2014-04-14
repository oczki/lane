<?php
/**
* Exports all lists in JSON format packed into single ZIP file, base64-encoded.
* If authenticated, also exports private lists (otherwise only public ones are
* exported).
*
* @user-name: name of list owner
*/
class ExportListsJob extends GenericUserJob
{
	public function requiresAuthentication()
	{
		return false;
	}

	public function execute()
	{
		$lists = $this->getLists();

		$zipPath = tempnam(sys_get_temp_dir(), 'lane-export');

		if (empty($lists))
			throw new InvalidListException(null, InvalidListException::REASON_PRIVATE);

		$zip = new ZipArchive();
		if (!$zip->open($zipPath))
			throw new SimpleException('Failed to create ZIP archive.');

		foreach ($lists as $list)
			$zip->addFromString($list->urlName . '.json', ListService::serialize($list));

		$zip->close();
		$outputData = file_get_contents($zipPath);
		unlink($zipPath);

		return [
			'output-data' => base64_encode($outputData)
		];
	}
}
