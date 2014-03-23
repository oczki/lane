$(function()
{
	$('#login, #register').click(function(e)
	{
		e.preventDefault();
		var url = $(this).attr('href');
		showPopup(url);
	});
});
