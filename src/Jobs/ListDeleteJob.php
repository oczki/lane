<?php
class ListDeleteJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		ListService::delete($list);
	}
}
