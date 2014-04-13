<?php
class ShowListJob extends GenericListJob
{
	public function execute()
	{
		return $this->getList();
	}
}
