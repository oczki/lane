<?php
/**
* Retrieves whole user's list. If authenticated, shows the list even if it's invisible to public.
*
* @param user-name name of list owner
* @param list-id   id of list
*/
class GetListJob extends GenericListJob
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
