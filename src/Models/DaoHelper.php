<?php
use Chibi\Sql;
use Chibi\Database as Database;

class DaoHelper
{
	public static function saveOrUpdate($table, AbstractEntity $entity)
	{
		Database::transaction(function() use ($table, $entity)
		{
			$array = self::untransformEntity($entity);
			$update = false;
			if (isset($entity->id))
			{
				$stmt = new SqlSelectStatement();
				$stmt->setCriterion(new Sql\EqualsFunctor('id', new Sql\Binding($entity->id)));
				$stmt->setTable($table);
				if (Database::fetchOne($stmt))
					$update = true;
			}

			if ($update)
			{
				$stmt = new Sql\UpdateStatement();
				$stmt->setCriterion(new Sql\EqualsFunctor('id', new Sql\Binding($entity->id)));
			}
			else
				$stmt = new Sql\InsertStatement();

			$stmt->setTable($table);

			foreach ($array as $key => $val)
				$stmt->setColumn($key, new Sql\Binding($val));

			Database::exec($stmt);
		});
	}

	protected static function untransformEntity(AbstractEntity $entity)
	{
		$rawEntity = [];
		foreach ($entity as $key => $val)
		{
			$keyKC = TextHelper::convertCase($key, TextHelper::CAMEL_CASE, TextHelper::SNAKE_CASE);
			$rawEntity[$keyKC] = $val;
		}
		return $rawEntity;
	}

	public static function transformEntity($desiredClassName, $rawEntity)
	{
		$entity = new $desiredClassName;
		foreach ($rawEntity as $key => $val)
		{
			$keyCC = TextHelper::convertCase($key, TextHelper::SNAKE_CASE, TextHelper::LOWER_CAMEL_CASE);
			$entity->$keyCC = $val;
		}
		return $entity;
	}

	public static function transformEntities($desiredClassName, array $rawEntities)
	{
		$entities = [];
		foreach ($rawEntities as $rawEntity)
			$entities []= self::transformEntity($desiredClassName, $rawEntity);
		return $entities;
	}
}
