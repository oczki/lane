<?php
class Validator
{
	protected $subject;
	protected $subjectName;

	public function __construct($subject, $subjectName)
	{
		$this->subject = $subject;
		$this->subjectName = $subjectName;
	}

	public function checkMinLength($minLength)
	{
		if (strlen($this->subject) < $minLength)
		{
			throw new ValidationException(
				sprintf('%s must be at least %s long.',
					ucfirst($this->subjectName),
					TextHelper::pluralize($minLength, 'character')));
		}
	}

	public function checkMaxLength($maxLength)
	{
		if (strlen($this->subject) > $maxLength)
		{
			throw new ValidationException(
				sprintf('%s must be at most %s long.',
					ucfirst($this->subjectName),
					TextHelper::pluralize($maxLength, 'character')));
		}
	}

	public function checkRegex($regex)
	{
		if (!preg_match($regex, $this->subject))
		{
			throw new ValidationException(
				sprintf('%s contains invalid characters.',
					ucfirst($this->subjectName)));
		}
	}

	public function checkEmail()
	{
		if (!preg_match(self::getEmailRegex(), $this->subject))
		{
			throw new ValidationException(
				sprintf('"%s" is not a valid e-mail address.',
					$this->subject));
		}
	}

	public static function getEmailRegex()
	{
		//simplified RFC 5322 as seen on http://www.regular-expressions.info/email.html
		return '/[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@' .
			'(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/';
	}
}
