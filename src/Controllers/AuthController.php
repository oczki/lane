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

		if (InputHelper::getPost('remember'))
		{
			setcookie('auth-name', $name, time() + 60 * 60 * 24 * 30, '/');
			setcookie('auth-pass-hash', $passHash, time() + 60 * 60 * 24 * 30, '/');
		}

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

		Messenger::success('Logged in.');
		Bootstrap::forward('/');
	}

	/**
	* @route /auth/logout
	*/
	public function logoutAction()
	{
		$this->context->viewName = null;

		if (!$this->context->isSubmit)
			return;

		unset($_SESSION['logged-in']);
		unset($_SESSION['user-id']);

		setcookie('auth-name', '', 0, '/');
		setcookie('auth-pass-hash', '', 0, '/');

		Messenger::success('Logged out.');
		Bootstrap::forward('/');
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

		Messenger::success('Registration successful');
		Bootstrap::forward('/');
	}

	public static function isLoggedIn()
	{
		return isset($_SESSION['logged-in']);
	}

	public static function tryAutoLogin()
	{
		if (self::isLoggedIn())
			return true;

		if (!isset($_COOKIE['auth-name']))
			return false;
		$name = $_COOKIE['auth-name'];

		if (!isset($_COOKIE['auth-pass-hash']))
			return false;
		$passHash = $_COOKIE['auth-pass-hash'];

		$user = UserService::getByName($name);
		if (empty($user))
			return false;

		if ($passHash != $user->passHash)
			return false;

		$_SESSION['logged-in'] = true;
		$_SESSION['user-id'] = $user->id;

		return self::isLoggedIn();
	}
}
