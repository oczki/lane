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

		if ($listFilter->uniqueId !== null)
		{
			$stmt->getCriterion()->add(
				new Sql\EqualsFunctor('unique_id', new Sql\Binding($listFilter->uniqueId)));
		}

		$stmt->setOrderBy('priority', Sql\SelectStatement::ORDER_ASC);

		return DaoHelper::transformEntities('ListEntity', Database::fetchAll($stmt));
	}

	public static function saveOrUpdate(ListEntity $listEntity)
	{
		return DaoHelper::saveOrUpdate('list', $listEntity);
	}

	public static function delete(ListEntity $listEntity)
	{
		return DaoHelper::delete($listEntity);
	}
}
