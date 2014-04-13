<?php
interface IJob
{
	public function __construct(array $arguments);

	public function getArgument($key);

	public function getName();

	public function getApiUser();

	public function execute();
}
