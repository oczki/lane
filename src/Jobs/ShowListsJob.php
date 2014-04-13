<?php
/**
* Retrieves all of user's lists. If authenticated, shows also lists invisible to public.
*
* @user-name: name of list owner
*/
class ShowListsJob extends GenericUserJob
{
	public function requiresAuthentication()
	{
		return false;
	}

	public function getLists()
	{
		$lists = array_values(ListService::getByUser($this->getUser()));
		$lists = array_filter($lists, function($list)
		{
			return ApiHelper::canShowList($list);
		});
		return $lists;
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
