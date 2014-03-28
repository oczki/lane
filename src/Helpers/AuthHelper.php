<?php
class AuthHelper
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
		$passwordHash = UserService::hashPassword($password);

		if ($remember)
		{
			setcookie('auth-name', $userName, time() + 60 * 60 * 24 * 30, '/');
			setcookie('auth-pass-hash', $passwordHash, time() + 60 * 60 * 24 * 30, '/');
		}

		$user = UserService::getByName($userName);
		if (empty($user))
			throw new ValidationException('User not found.');

		if ($passwordHash != $user->passHash)
			throw new ValidationException('Invalid password.');

		$_SESSION['logged-in'] = true;
		$_SESSION['user-id'] = $user->id;
	}
}
