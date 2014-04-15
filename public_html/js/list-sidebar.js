$(function()
{
	var queue = QueueFactory();

	$('#sidebar-nav .add-list, #sidebar-nav .settings').click(function(e)
	{
		e.preventDefault();
		showPopup($(this).attr('href'));
	});

	if ($('#sidebar').attr('data-can-edit') == '1')
	{
		$('#sidebar a.list').each(function(i, listItem)
		{
			var dragger = $('<a>');
			dragger.attr('href', '#');
			dragger.addClass('dragger');
			dragger.append('<i class="icon icon-drag">');
			dragger.insertBefore(listItem);

			initMoveDragger(
				dragger,
				'li',

				function(dragger)
				{
				},

				function(dragger, isChanged)
				{
					if (!isChanged)
						return;
					var userName = dragger.parents('li').attr('data-user-name');
					var listId = dragger.parents('li').attr('data-list-id');
					var priority = dragger.parents('li').index() + 1;
					queue.push(new Job('set-list-position', {
						'user-name': userName,
						'list-id': listId,
						'new-position': priority}));
					queue.delayedFlush();
				});

		});
	}
});
