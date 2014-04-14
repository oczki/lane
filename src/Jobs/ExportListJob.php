<?php
/**
* Exports one list to JSON format. If authenticated, exports the list even if
* it's invisible to public (otherwise returns an error).
*
* @user-name: name of list owner
* @list-id: id of list
*/
class ExportListJob extends GenericListJob
{
	public function requiresAuthentication()
	{
		return false;
	}

	public function execute()
	{
		$list = $this->getList();
		$data = ListService::serialize($list);

		return [
			'list-id' => $list->urlName,
			'output-data' => $data
		];
	}
}
