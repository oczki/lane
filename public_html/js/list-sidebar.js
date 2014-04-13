$(function()
{
	var queue = QueueFactory();

	$('#list-management .add-list, #list-management .settings').click(function(e)
	{
		e.preventDefault();
		showPopup($(this).attr('href'));
	});

	if ($('#sidebar').attr('data-can-edit') == '1')
	{
		$('#sidebar #lists a.list').each(function(i, listItem)
		{
			var dragger = $('<a>');
			dragger.attr('href', '#');
			dragger.addClass('dragger');
			dragger.append('<i class="icon icon-drag">');
			dragger.insertBefore(listItem);
			initDragger(dragger, 'li', function(dragger, isChanged)
			{
				if (!isChanged)
					return;
				var userName = dragger.parents('li').attr('data-user-name');
				var listId = dragger.parents('li').attr('data-list-id');
				var priority = dragger.parents('li').index() + 1;
				queue.push(new Job('list-set-priority', {
					'user-name': userName,
					'list-id': listId,
					'new-list-priority': priority}));
				queue.delayedFlush();
			});
		});
	}
});
