<?php
/**
* Retrieves all of user's lists. If authenticated, also shows private lists (otherwise only public ones are retrieved).
*
* @param user-name name of list owner
*/
class GetListsJob extends GenericUserJob
{
	public function requiresAuthentication()
	{
		return false;
	}

	public function execute()
	{
		$lists = $this->getLists();

		if (empty($lists))
			throw new InvalidListException(null, InvalidListException::REASON_PRIVATE);

		return array_map(function($list)
			{
				return [
					'list-name' => $list->name,
					'list-id' => $list->urlName
				];
			}, $lists);
	}
}
