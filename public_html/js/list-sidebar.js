$(function()
{
	$('#list-management .add-list, #list-management .settings').click(function(e)
	{
		e.preventDefault();
		showPopup($(this).attr('href'));
	});
});
