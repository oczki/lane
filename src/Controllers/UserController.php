<?php
class UserController
{
	private function preWork()
	{
		ControllerHelper::attachUser();
	}

	public function accountSettingsAction()
	{
		$this->preWork();
		$context = getContext();
		$context->viewName = 'user-account-settings';

		if (!ApiHelper::canEdit($context->user))
			throw new UnprivilegedOperationException();

		if (!$context->isSubmit)
			return;

		$context->user->settings->showGuestsLastUpdate =
			boolval(InputHelper::getPost('show-guests-last-update'));

		$currentPassword = InputHelper::getPost('current-password');
		$currentPasswordHash = UserService::hashPassword($context->user, $currentPassword);
		$currentPasswordOk = $currentPasswordHash == $context->user->passHash;

		$newPassword1 = InputHelper::getPost('new-password1');
		$newPassword2 = InputHelper::getPost('new-password2');
		$newPasswordHash = UserService::hashPassword($context->user, $newPassword1);
		$newEmail = InputHelper::getPost('new-e-mail');

		if ($newPassword1)
		{
			if (!$currentPasswordOk)
				throw new ValidationException('Must supply valid current password.');

			if ($newPassword1 != $newPassword2)
				throw new ValidationException('Passwords must be the same.');

			$context->user->passHash = $newPasswordHash;
		}

		if ($newEmail and $newEmail != $context->user->email)
		{
			if (!$currentPasswordOk)
				throw new ValidationException('Must supply valid current password.');

			$validator = new Validator($newEmail, 'e-mail');
			$validator->checkEmail();

			$context->user->email = $newEmail;
		}

		UserService::saveOrUpdate($context->user);

		Messenger::success('Settings changed successfully.');

		$lastViewedList = ListService::getLastViewedList();
		ControllerHelper::forward(\Chibi\Router::linkTo(['ListController', 'viewAction'], [
			'userName' => ListService::getOwner($lastViewedList)->name,
			'id' => $lastViewedList->urlName]));
	}

	public function deleteAccountAction()
	{
		$this->preWork();
		$context = getContext();
		$context->viewName = 'messages';

		if (!ApiHelper::canEdit($context->user))
			throw new UnprivilegedOperationException();

		if (!$context->isSubmit)
			return;

		$currentPassword = InputHelper::getPost('current-password');
		$currentPasswordHash = UserService::hashPassword($context->user, $currentPassword);
		$currentPasswordOk = $currentPasswordHash == $context->user->passHash;

		if (!$currentPasswordOk)
			throw new ValidationException('Must supply valid current password.');

		UserService::delete($context->user);

		Auth::logout();
		Messenger::success('Account deleted.');
		ControllerHelper::forward('/');
	}
}
