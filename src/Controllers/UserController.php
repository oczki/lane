<?php
class UserController
{
	private function preWork()
	{
		ControllerHelper::attachUser();
	}

	/**
	* @route /settings
	* @route /settings/
	*/
	public function accountSettingsAction()
	{
		$this->preWork();

		if (!ApiHelper::canEdit($this->context->user))
			throw new UnprivilegedOperationException();

		if (!$this->context->isSubmit)
			return;

		$this->context->user->settings->showGuestsLastUpdate =
			boolval(InputHelper::getPost('show-guests-last-update'));

		$currentPassword = InputHelper::getPost('current-password');
		$currentPasswordHash = UserService::hashPassword($this->context->user, $currentPassword);
		$currentPasswordOk = $currentPasswordHash == $this->context->user->passHash;

		$newPassword1 = InputHelper::getPost('new-password1');
		$newPassword2 = InputHelper::getPost('new-password2');
		$newPasswordHash = UserService::hashPassword($this->context->user, $newPassword1);
		$newEmail = InputHelper::getPost('new-e-mail');

		if ($newPassword1)
		{
			if (!$currentPasswordOk)
				throw new ValidationException('Must supply valid current password.');

			if ($newPassword1 != $newPassword2)
				throw new ValidationException('Passwords must be the same.');

			$this->context->user->passHash = $newPasswordHash;
		}

		if ($newEmail and $newEmail != $this->context->user->email)
		{
			if (!$currentPasswordOk)
				throw new ValidationException('Must supply valid current password.');

			$validator = new Validator($newEmail, 'e-mail');
			$validator->checkEmail();

			$this->context->user->email = $newEmail;
		}

		UserService::saveOrUpdate($this->context->user);

		Messenger::success('Settings changed successfully.');

		$lastViewedList = ListService::getLastViewedList();
		Bootstrap::forward(\Chibi\UrlHelper::route('list', 'view', [
			'userName' => ListService::getOwner($lastViewedList)->name,
			'id' => $lastViewedList->urlName]));
	}

	/**
	* @route /delete-account
	* @route /delete-account/
	*/
	public function deleteAccountAction()
	{
		$this->context->viewName = 'messages';
		$this->preWork();

		if (!ApiHelper::canEdit($this->context->user))
			throw new UnprivilegedOperationException();

		if (!$this->context->isSubmit)
			return;

		$currentPassword = InputHelper::getPost('current-password');
		$currentPasswordHash = UserService::hashPassword($this->context->user, $currentPassword);
		$currentPasswordOk = $currentPasswordHash == $this->context->user->passHash;

		if (!$currentPasswordOk)
			throw new ValidationException('Must supply valid current password.');

		UserService::delete($this->context->user);

		Auth::logout();
		Messenger::success('Account deleted.');
		Bootstrap::forward('/');
	}
}
