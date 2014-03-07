<?php
interface IJob
{
	static function getName();
	static function getArgumentCount();
	function execute();
}
