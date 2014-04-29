<?php
class AuthController
{
	public function loginAction()
	{
		$context = getContext();
		$context->viewName = 'auth-login';

		if (!$context->isSubmit)
			return;

		$name = InputHelper::getPost('name');
		$pass = InputHelper::getPost('pass');
		$remember = boolval(InputHelper::getPost('remember'));

		Auth::login($name, $pass, $remember);

		Messenger::success('Logged in.');
		ControllerHelper::forward('/');
	}

	public function logoutAction()
	{
		Auth::logout();
		Messenger::success('Logged out.');
		ControllerHelper::forward('/');
	}

	public function resetPasswordAction()
	{
		$email = InputHelper::getPost('e-mail');
		$validator = new Validator($email, 'e-mail');
		$validator->checkEmail();

		$user = UserService::getByEmail($email);
		if (empty($user))
			throw new SimpleException('No such e-mail.');

		$user->settings->passwordResetToken = md5(microtime(true) . mt_rand());
		UserService::saveOrUpdate($user);

		$link = \Chibi\Router::linkTo(['AuthController', 'resetPasswordConfirmAction'], [
			'userName' => $user->name,
			'token' => $user->settings->passwordResetToken]);

		$subject = 'Lane password reset';
		$body = 'Someone (probably you) requested to reset your password on Lane. ' .
			'To continue, click on the following link:' . PHP_EOL . PHP_EOL .
			$link . PHP_EOL . PHP_EOL .
			'If you didn\'t request this reset, please ignore this message.' . PHP_EOL . PHP_EOL .
			'---' . PHP_EOL . PHP_EOL .
			'Lane (list and nothing else) - ' . \Chibi\Router::linkTo(['IndexController', 'indexAction']);

		$mail = getConfig()->mail->userBot . '@' . getConfig()->mail->domain;
		$headers = 'From: "Lane bot" <' . $mail . '>';

		mail($user->email, $subject, $body, $headers);

		Messenger::success('An e-mail with link to reset your password was sent.');
		ControllerHelper::forward(\Chibi\Router::linkTo(['AuthController', 'loginAction']));
	}

	public function resetPasswordConfirmAction($userName, $token)
	{
		$context = getContext();
		$context->viewName = 'messages';

		$user = UserService::getByName($userName);
		if (empty($user))
			throw new InvalidUserException($userName);

		if ($user->settings->passwordResetToken != $token)
			throw new SimpleException('Invalid token.');

		$newPassword = str_split('0123456789abcdefghijklmnopqrstuvwxyz');
		shuffle($newPassword);
		$newPassword = implode('', $newPassword);
		$newPassword = substr($newPassword, 0, 8);

		$user->settings->passwordResetToken = null;
		$user->passHash = UserService::hashPassword($user, $newPassword);
		UserService::saveOrUpdate($user);

		ControllerHelper::markReturn('Log in now', \Chibi\Router::linkTo(['AuthController', 'loginAction']));
		Messenger::success('Your new password: ' . $newPassword);
	}

	public function registerAction()
	{
		$context = getContext();
		$context->viewName = 'auth-register';

		if (!$context->isSubmit)
			return;

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

		$user = new UserEntity();
		$user->name = $name;
		$user->email = $email;
		$user->settings = new UserSettings();
		$user->settings->showGuestsLastUpdate = true;
		$user->passHash = UserService::hashPassword($user, $pass);
		UserService::saveOrUpdate($user);

		$list = new ListEntity();
		$list->priority = 1;
		$list->userId = $user->id;
		$list->name = 'Getting started';
		$list->visible = true;
		$list->content = new ListContent();

		$column1 = new ListColumn();
		$column1->name = 'First column';
		$column1->width = 43;
		$column1->align = ListColumn::ALIGN_LEFT;
		$column1->id = ++$list->content->lastContentId;

		$column2 = new ListColumn();
		$column2->name = 'Second column';
		$column2->width = 37;
		$column2->align = ListColumn::ALIGN_LEFT;
		$column2->id = ++$list->content->lastContentId;

		$column3 = new ListColumn();
		$column3->name = '3rd one';
		$column3->width = 20;
		$column3->align = ListColumn::ALIGN_LEFT;
		$column3->id = ++$list->content->lastContentId;

		$list->content->columns []= $column1;
		$list->content->columns []= $column2;
		$list->content->columns []= $column3;

		$rows =
		[
			['[b]Welcome to lane![/b] This is your first list.', '', ''],
			['Point here and click the blue icon →', 'to edit cell\'s contents.', 'Easy, right?'],
			['', '', ''],
			['Add new rows using the button below,', 'or use keyboard shortcuts:', ''],
			['', '\[Enter] - save changes', ''],
			['', '\[Tab] - save, go to next cell', ''],
			['', '\[Shift+Tab] - save, prev cell', ''],
			['', '\[Esc] - discard changes', ''],
			['', '', ''],
			['Add new lists, or edit the current one', '', ''],
			['using the menu to the left.', '', ''],
			['', '', ''],
			['Click on headers to sort columns.', 'You can also [orange]resize them.', '[orange](Try it!)'],
			['', 'Or... [blue]make your sort default.', ''],
			['', 'Or even [red]reorder the lists.', ''],
			['', '', ''],
			['[url=' . \Chibi\Router::linkTo(['IndexController', 'helpAction']) . ']Click here[/url] to read more about editing.', '', ''],
			['', '', ''],
			['[row:green][row:b]The rest is all up to you.', 'Start by creating a new list.', ''],
		];

		foreach ($rows as $rowContent)
		{
			$row = new ListRow();
			$row->content = $rowContent;
			$row->id = ++$list->content->lastContentId;
			$list->content->rows []= $row;
		}

		$list->urlName = ListService::forgeUrlName($list);
		ListService::saveOrUpdate($list);

		$_SESSION['logged-in'] = true;
		$_SESSION['user-id'] = $user->id;

		Messenger::success('Registration successful');
		ControllerHelper::forward('/');
	}
}
