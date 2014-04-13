<?php
interface IJob
{
	public function __construct(array $arguments);

	public function getArguments();

	public function getArgument($key);

	public function getName();

	public function execute();
}
