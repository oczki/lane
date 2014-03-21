<?php
class ListContent extends StrictPropertyObject
{
	public $columns = [];
	public $rows = [];
	public $css = '';
	public $showRowIds = false;
	public $sortStyle;

	public $lastContentId = 0;
}
