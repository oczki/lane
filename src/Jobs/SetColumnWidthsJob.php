<?php
/**
* Sets new column widths. All supplied widths must add up to 100%.
*
* @param user-name  name of list owner
* @param list-id    id of list
* @param new-widths string representing new column widths (example: <code>[60,20,20]</code> will make first column 60%
*                   wide and other two columns 20% wide.)
*/
class SetColumnWidthsJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$columns = ListService::getColumns($list);
		$newWidths = json_decode($this->getArgument('new-widths'));
		if (count($newWidths) != count($columns))
			throw new SimpleException('New widths array size mismatches expected column count.');

		foreach ($columns as $pos => $column)
			$column->width = floatval($newWidths[$pos]);

		$totalSum = array_sum(array_map(function($column)
		{
			return $column->width;
		}, $columns));

		if (round($totalSum, 2) != 100)
			throw new SimpleException('New widths don\'t add up to 100%.');

		ListService::saveOrUpdate($list);
	}
}
