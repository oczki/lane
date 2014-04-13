<?php
/**
* Retrieves whole user's list. If authenticated, shows also lists invisible to public.
*
* @user-name: name of list owner
* @list-id: id of list
*/
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
