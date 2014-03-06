$(function()
{
	$('#add-new-list').click(function(e)
	{
		e.preventDefault();
		showPopup($(this).attr('href'));
	});
});
