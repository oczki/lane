<?php
abstract class AbstractJob implements IJob
{
	private $arguments = [];

	public function __construct(array $arguments)
	{
		$this->setArguments($arguments);
	}

	public function setArguments(array $arguments)
	{
		$this->arguments = $arguments;
	}

	public function getArguments()
	{
		return $this->arguments;
	}

	public function getArgument($key)
	{
		if (!isset($this->arguments[$key]))
			throw new SimpleException('Error: must supply "' . $key . '" argument.');
		return $this->arguments[$key];
	}

	public function getName()
	{
		$name = get_called_class();
		$name = preg_replace('/Job$/', '', $name);
		return TextCaseConverter::convert($name,
			TextCaseConverter::UPPER_CAMEL_CASE,
			TextCaseConverter::SPINAL_CASE);
	}

	public abstract function execute();
}
