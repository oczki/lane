<?php
class AuthController
{
	/**
	* @route /auth/login
	*/
	public function loginAction()
	{
		if (!$this->context->isSubmit)
			return;

		$name = InputHelper::getPost('name');
		$pass = InputHelper::getPost('pass');

		$passHash = UserService::hashPassword($pass);

		try
		{
			$user = UserService::getByName($name);
			if (empty($user))
				throw new ValidationException('User not found.');

			if ($passHash != $user->passHash)
				throw new ValidationException('Invalid password.');
		}
		catch (SimpleException $e)
		{
			Messenger::error($e->getMessage());
			return;
		}

		$_SESSION['logged-in'] = true;
		$_SESSION['user-id'] = $user->id;
		\Chibi\UrlHelper::forward('/');
	}

	/**
	* @route /auth/logout
	*/
	public function logoutAction()
	{
		unset($_SESSION['logged-in']);
		unset($_SESSION['user-id']);
		\Chibi\UrlHelper::forward('/');
	}

	/**
	* @route /auth/register
	*/
	public function registerAction()
	{
		if (!$this->context->isSubmit)
			return;

		try
		{
			$name = InputHelper::getPost('name');
			$pass1 = InputHelper::getPost('pass1');
			$pass2 = InputHelper::getPost('pass2');
			$email = InputHelper::getPost('e-mail');

			$user = UserService::getByName($name);
			if (!empty($user))
				throw new ValidationException('User with given name already exists.');

			if ($pass1 != $pass2)
				throw new ValidationException('Passwords must be the same.');
			$pass = $pass1;
			$passHash = UserService::hashPassword($pass);

			$validator = new Validator($pass, 'password');
			$validator->checkMinLength(1);

			$validator = new Validator($name, 'user name');
			$validator->checkMinLength(1);
			$validator->checkMaxLength(20);
			$validator->checkRegex('/^[a-zA-Z0-9_-]+$/');

			if (!empty($email))
			{
				$validator = new Validator($email, 'e-mail');
				$validator->checkEmail();
			}
		}
		catch (SimpleException $e)
		{
			Messenger::error($e->getMessage());
			return;
		}

		$user = new UserEntity();
		$user->name = $name;
		$user->passHash = $passHash;
		$user->email = $email;
		UserService::saveOrUpdate($user);

		$_SESSION['logged-in'] = true;
		$_SESSION['user-id'] = $user->id;
		\Chibi\UrlHelper::forward('/');
	}
}
