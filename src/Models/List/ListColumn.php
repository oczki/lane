<?php
class ListColumn extends StrictPropertyObject
{
	public $id;
	public $name;
	public $width;
	public $align;

	const ALIGN_LEFT = 'left';
	const ALIGN_CENTER = 'center';
	const ALIGN_RIGHT = 'right';
}
