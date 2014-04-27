<?php
class Auth
{
	protected static $temporaryLogout = false;

	public static function logout()
	{
		unset($_SESSION['logged-in']);
		unset($_SESSION['user-id']);

		setcookie('auth-name', '', 0, '/');
		setcookie('auth-pass-hash', '', 0, '/');
	}

	public static function temporaryLogout()
	{
		self::$temporaryLogout = true;
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
		return !self::isTemporarilyLoggedOut() and isset($_SESSION['logged-in']);
	}

	public static function isTemporarilyLoggedOut()
	{
		return self::$temporaryLogout;
	}

	public static function getLoggedInUser()
	{
		return self::isLoggedIn()
			? UserService::getById($_SESSION['user-id'])
			: null;
	}

	public static function loginFromDigest()
	{
		$realm = 'lane';

		if (empty($_SERVER['PHP_AUTH_DIGEST']))
		{
			\Chibi\HeadersHelper::setCode(401);
			\Chibi\HeadersHelper::set('WWW-Authenticate', 'Digest realm="' . $realm . '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($realm) . '"');

			return null;
		}

		$needed_parts = array_flip([
			'nonce',
			'nc',
			'cnonce',
			'qop',
			'username',
			'uri',
			'response',
		]);
		$data = [];
		$keys = implode('|', array_keys($needed_parts));

		preg_match_all(
			'@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@',
			$_SERVER['PHP_AUTH_DIGEST'],
			$matches,
			PREG_SET_ORDER);

		foreach ($matches as $m)
		{
			$data[$m[1]] = $m[3] ? $m[3] : $m[4];
			unset($needed_parts[$m[1]]);
		}

		if (!empty($needed_parts))
			throw new SimpleException('Authorization error');

		$user = UserService::getByName($data['username']);
		if (!$user)
			return null;

		// generate the valid response
		$a1 = $user->passHash;
		$a2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $data['uri']);
		$valid_response = md5(implode(':', [$a1, $data['nonce'], $data['nc'], $data['cnonce'], $data['qop'], $a2]));

		if ($data['response'] != $valid_response)
			return null;

		$_SESSION['logged-in'] = true;
		$_SESSION['user-id'] = $user->id;

		return $user;
	}

	public static function loginFromCookie()
	{
		if (self::isLoggedIn())
			return self::getLoggedInUser();

		if (!isset($_COOKIE['auth-name']))
			return null;
		$name = $_COOKIE['auth-name'];

		if (!isset($_COOKIE['auth-pass-hash']))
			return null;
		$passHash = $_COOKIE['auth-pass-hash'];

		$user = UserService::getByName($name);
		if (empty($user))
			return null;

		if ($passHash != $user->passHash)
			return null;

		$_SESSION['logged-in'] = true;
		$_SESSION['user-id'] = $user->id;

		return $user;
	}
}
