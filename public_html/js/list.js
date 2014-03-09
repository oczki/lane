$(function()
{
	var queue = new Queue();
	var listId = $('#list-id').val();

	$('#add-row').click(function()
	{
		queue.push(new Job('list-add-row', [listId]));
		queue.delayedFlush();
	});

	$('#list-management .add-list, #menu .login, #menu .register').click(function(e)
	{
		e.preventDefault();
		showPopup($(this).attr('href'));
	});

	$('#menu .logout').click(function(e)
	{
		e.preventDefault();
		$.post($(this).attr('href'), function()
		{
			window.location.reload();
		});
	});
});
