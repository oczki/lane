function createRowTableRow(data, canEdit)
{
	var row = $('<tr>');
	row.data('content-id', data.id);

	$.each(data.content, function(i, cellText)
	{
		var cell = $('<td>');

		var span = $('<span>');
		span.addClass('content');
		span.text(cellText);

		cell.append(span);

		if (canEdit)
		{
			var input = $('<input>');
			input.attr('type', 'text');
			input.val(cellText);

			var editLink = $('<a>');
			editLink.attr('href', '#');
			editLink.addClass('edit-content');
			editLink.append('<i class="icon icon-edit">');

			cell.append(input);
			cell.append(editLink);
		}

		row.append(cell);
	});

	if (canEdit)
	{
		var cell = $('<td>');
		cell.addClass('row-ops');

		var deleteLink = $('<a>');
		deleteLink.attr('href', '#');
		deleteLink.addClass('delete-row');
		deleteLink.append('<i class="icon icon-delete">');

		cell.append(deleteLink);

		row.append(cell);
	}

	row.find('td').wrapInner('<div class="animate-me">');

	return row;
}

$(function()
{
	var queue = new Queue();
	var canEdit = $('#list').attr('data-can-edit') == '1';
	var listId = $('#list').attr('data-list-id');
	var listRows = $.parseJSON($('#list').attr('data-list-rows'));
	var listColumns = $.parseJSON($('#list').attr('data-list-columns'));
	var lastContentId = $('#list').attr('data-last-content-id');
	for (var i in listRows)
	{
		var tableRow = createRowTableRow(listRows[i], canEdit);
		$('#list tbody').append(tableRow);
	}

	$('#main').on('click', '.delete-row', function()
	{
		var tableRow = $(this).parents('tr');
		var contentId = tableRow.data('content-id');
		queue.push(new Job('list-delete-row', [listId, contentId]));
		queue.delayedFlush();
		tableRow.find('div.animate-me').slideUp('fast', function()
		{
			tableRow.remove();
		});
	});

	$('#main').on('click', '#add-row input', function()
	{
		var newRow = {
			id: ++ lastContentId,
			content: new Array(listColumns.length).map(String.prototype.valueOf, '')
		};
		queue.push(new Job('list-add-row', [listId, newRow.id]));
		queue.delayedFlush();
		var tableRow = createRowTableRow(newRow, canEdit);
		$('#list tbody').append(tableRow);
		tableRow.find('div.animate-me').hide().slideDown('fast', function()
		{
			//todo: show input and focus it
		});
	});

	$('#list-management .add-list, #list-management .settings, #menu .login, #menu .register').click(function(e)
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
