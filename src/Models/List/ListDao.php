<?php
use Chibi\Sql;
use Chibi\Database as Database;

class ListDao
{
	public static function getFilteredLists(ListFilter $listFilter)
	{
		$stmt = new Sql\SelectStatement();
		$stmt->setColumn('list.*');
		$stmt->setTable('list');
		$stmt->setCriterion(new Sql\ConjunctionFunctor());

		if ($listFilter->id != null)
		{
			$stmt->getCriterion()->add(
				new Sql\EqualsFunctor('id', new Sql\Binding($listFilter->id)));
		}

		if ($listFilter->userId !== null)
		{
			$stmt->getCriterion()->add(
				new Sql\EqualsFunctor('user_id', new Sql\Binding($listFilter->userId)));
		}

		if ($listFilter->urlName !== null)
		{
			$stmt->getCriterion()->add(
				new Sql\EqualsFunctor('url_name', new Sql\Binding($listFilter->urlName)));
		}

		$stmt->setOrderBy('priority', Sql\SelectStatement::ORDER_ASC);

		$rows = Database::fetchAll($stmt);
		$listEntities = DaoHelper::transformEntities('ListEntity', $rows);

		foreach ($rows as $row)
		{
			$list = &$listEntities[$row['id']];
			$list->content = self::deserializeContent($list->content);
			unset($list);
		}

		return $listEntities;
	}

	public static function saveOrUpdate(ListEntity $list)
	{
		$list->content = self::serializeContent($list->content);
		try
		{
			$ret = DaoHelper::saveOrUpdate('list', $list);
		}
		finally
		{
			$list->content = self::deserializeContent($list->content);
		}
		return $ret;
	}

	public static function delete(ListEntity $list)
	{
		return DaoHelper::delete('list', $list);
	}

	private static function serializeContent($content)
	{
		return gzdeflate(serialize($content));
	}

	private static function deserializeContent($content)
	{
		if (!$content)
			return null;
		return unserialize(gzinflate($content));
	}
}
