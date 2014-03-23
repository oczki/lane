<?php
class ListService
{
	public static function getFilteredLists(ListFilter $listFilter)
	{
		return ListDao::getFilteredLists($listFilter);
	}

	public static function getByUserId($userId)
	{
		$filter = new ListFilter();
		$filter->userId = $userId;
		$lists = self::getFilteredLists($filter);
		if (!empty($lists))
			return $lists;

		$user = UserService::getById($userId);
		$job = new ListAddJob('New blank list', true);
		JobExecutor::execute($job, $user);

		$lists = self::getFilteredLists($filter);
		return $lists;
	}

	public static function getByUrlName(UserEntity $owner, $urlName)
	{
		$filter = new ListFilter();
		$filter->userId = $owner->id;
		$filter->urlName = $urlName;
		$lists = self::getFilteredLists($filter);
		if (empty($lists))
			return null;
		$list = reset($lists);
		return $list;
	}

	public static function saveOrUpdate(ListEntity $listEntity)
	{
		$listEntity->lastUpdate = time();
		return ListDao::saveOrUpdate($listEntity);
	}

	public static function delete(ListEntity $listEntity)
	{
		return ListDao::delete($listEntity);
	}

	public static function canShow(ListEntity $listEntity)
	{
		$context = \Chibi\Registry::getContext();
		if ($listEntity->visible)
			return true;

		return
			$context->isLoggedIn and
			$listEntity->userId == $context->userLogged->id;
	}

	public static function getColumnClasses(ListColumn $column)
	{
		$alignments = [];
		$alignments[ListColumn::ALIGN_LEFT] = 'col-left';
		$alignments[ListColumn::ALIGN_CENTER] = 'col-center';
		$alignments[ListColumn::ALIGN_RIGHT] = 'col-right';

		$classes = [];
		$classes []= $alignments[$column->align];

		return $classes;
	}

	public static function getPossibleColumnAlign()
	{
		return
		[
			ListColumn::ALIGN_LEFT,
			ListColumn::ALIGN_CENTER,
			ListColumn::ALIGN_RIGHT,
		];
	}

	private static function checkColumnIndex(ListEntity $listEntity, &$index)
	{
		$index = intval($index);
		if ($index < 0 or $index >= count($listEntity->content->columns))
			throw new Exception('Invalid column index.');
	}

	private static function checkRowIndex(ListEntity $listEntity, &$index)
	{
		$index = intval($index);
		if ($index < 0 or $index >= count($listEntity->content->rows))
			throw new Exception('Invalid row index.');
	}
}
