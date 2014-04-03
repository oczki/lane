$(function()
{
	if ($('#account-settings').parents('.popup').length > 0)
	{
		$('#account-settings .cancel')
			.show()
			.click(function(e)
			{
				e.preventDefault();
				closePopup($(e.target).parents('.popup'));
			});
	}

	$('#account-settings .more').click(function(e)
	{
		e.preventDefault();
		$('#account-settings .danger').slideToggle();
	});

	$('#account-settings .danger').hide();

	$('#account-settings .delete-account').submit(function(e)
	{
		var text = 'Do you really want to delete your account? ' +
			'All of your lists will be gone forever. ' +
			'This operation cannot be undone!';

		if (!confirm(text))
		{
			e.preventDefault();
			e.stopPropagation();
		}
	});
});
