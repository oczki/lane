<?php
class ListContent extends StrictPropertyObject
{
	public $columns = [];
	public $rows = [];
	public $customCss = '';
	public $useCustomCss = false;
	public $showRowIds = false;
	public $sortStyle;

	public $lastContentId = 0;
}
