$(function()
{
	$('#account-settings .cancel').click(function(e)
	{
		e.preventDefault();
		closePopup($(e.target).parents('.popup'));
	});

	$('#account-settings .more').click(function(e)
	{
		e.preventDefault();
		$('#account-settings .danger').slideToggle();
	});

	$('#account-settings .danger').hide();
});
