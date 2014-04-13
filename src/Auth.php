<?php
class Auth
{
	public static function logout()
	{
		unset($_SESSION['logged-in']);
		unset($_SESSION['user-id']);

		setcookie('auth-name', '', 0, '/');
		setcookie('auth-pass-hash', '', 0, '/');
	}

	public static function login($userName, $password, $remember)
	{
		$user = UserService::getByName($userName);
		if (empty($user))
			throw new ValidationException('User not found.');

		$passwordHash = UserService::hashPassword($user, $password);
		if ($passwordHash != $user->passHash)
			throw new ValidationException('Invalid password.');

		if ($remember)
		{
			setcookie('auth-name', $userName, time() + 60 * 60 * 24 * 30, '/');
			setcookie('auth-pass-hash', $passwordHash, time() + 60 * 60 * 24 * 30, '/');
		}

		$_SESSION['logged-in'] = true;
		$_SESSION['user-id'] = $user->id;

		return $user;
	}

	public static function isLoggedIn()
	{
		return isset($_SESSION['logged-in']);
	}

	public static function getLoggedInUser()
	{
		return UserService::getById($_SESSION['user-id']);
	}

	public static function loginFromCookie()
	{
		if (Auth::isLoggedIn())
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

		return Auth::isLoggedIn();
	}
}
