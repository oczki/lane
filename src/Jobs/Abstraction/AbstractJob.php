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

	private function getDocComment()
	{
		$reflectionClass = new ReflectionClass(get_called_class());
		$docComment = $reflectionClass->getDocComment();
		return $docComment;
	}

	public function getArgumentsDescription()
	{
		preg_match_all('/^\s*\*[ \t]+@([a-zA-Z_-]+):?[ \t]+(.+)$/m', $this->getDocComment(), $matches);
		return array_combine($matches[1], $matches[2]);
	}

	public function getDescription()
	{
		preg_match_all('/^\s*\*[ \t]+(?![@#])(.+)/m', $this->getDocComment(), $matches);
		return implode(' ', $matches[1]);
	}

	public function getArguments()
	{
		return $this->arguments;
	}

	public function getArgument($key)
	{
		if (!isset($this->arguments[$key]))
			throw new SimpleException('Error: must supply "' . $key . '" argument.');
		if (!isset($this->getArgumentsDescription()[$key]))
			throw new SimpleException('Error: trying to use undocumented argument "' . $key . '".');
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
