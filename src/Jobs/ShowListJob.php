<?php
class ShowListJob extends GenericListJob
{
	public function requiresAuthentication()
	{
		return false;
	}

	public function execute()
	{
		$list = $this->getList();
		if (!ApiHelper::canShowList($list))
			throw new InvalidListException($list->urlName, InvalidListException::REASON_PRIVATE);
		return $list;
	}
}
