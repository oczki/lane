function createRowTableRow(data, canEdit)
{
	var tableRow = $('<tr>');
	tableRow.data('content-id', data.id);

	tableRow.bind('mouseenter focusin', function(e)
	{
		tableRow.addClass('active');
	});

	tableRow.bind('mouseleave focusout', function(e)
	{
		if (!tableRow.hasClass('edit'))
			tableRow.removeClass('active');
		if (e.type == 'focusout')
			$('#list').data('last-focused-row', tableRow);
	});

	$.each(data.content, function(i, cellText)
	{
		var tableCell = $('<td>');

		if (canEdit)
		{
			var editLink = $('<a>');
			editLink.attr('href', '#');
			editLink.addClass('edit-content');
			editLink.append('<i class="icon icon-edit">');
			tableCell.append(editLink);
		}

		var span = $('<span>');
		span.addClass('content');
		span.text(cellText);
		span.attr('title', cellText);
		tableCell.append(span);

		tableRow.append(tableCell);
	});

	if (canEdit)
	{
		var tableCell = $('<td>');
		tableCell.addClass('row-ops');

		var deleteLink = $('<a>');
		deleteLink.attr('href', '#');
		deleteLink.addClass('delete-row');
		deleteLink.append('<i class="icon icon-delete">');

		tableCell.append(deleteLink);

		tableRow.append(tableCell);
	}

	tableRow.find('td').wrapInner('<div class="animate-me">');

	return tableRow;
}

$(function()
{
	var queue = QueueFactory();
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

	$('#main').on('click', '#list .edit-content', function(e)
	{
		e.preventDefault();
		var tableCell = $(this).parents('td');
		var tableRow = tableCell.parents('tr');
		if (tableRow.data('working'))
			return;
		if (tableRow.find('input:visible').length > 0)
			return;
		tableRow.addClass('active edit');
		tableCell.find('span').hide();

		var input = $('<input>');
		input.attr('type', 'text');
		input.val(tableCell.find('span').text());
		tableCell.find('.animate-me').append(input.wrap('<div class="input-wrapper">').parent());
		input.hide().fadeIn('fast').focus();
	});

	var editCellContent = function(e)
	{
		var editLink = $(e.target);
		var tableCell = $(e.target).parents('td');
		var tableRow = tableCell.parents('tr');
		var text = tableCell.find('input').val();
		var rowId = tableRow.data('content-id');
		var columnId = listColumns[tableCell.index()].id;
		if (tableRow.data('working'))
			return;
		tableRow.data('working', true);

		queue.push(new Job('list-edit-cell', [listId, rowId, columnId, text]));
		queue.delayedFlush();
		tableCell.find('.input-wrapper').fadeOut('fast', function()
		{
			tableCell.find('span').text(text);
			tableCell.find('span').fadeIn();
			tableCell.find('.input-wrapper').remove();
			tableRow.removeClass('edit');
			if (!tableRow.is(':hover') && tableRow.find(':focus').length == 0)
				tableRow.removeClass('active');
			tableRow.data('working', false);
		});
	};
	$('#list').on('blur', 'input', editCellContent);
	$('#list').on('keypress', 'input', function(e)
	{
		if (e.keyCode == 13)
			editCellContent(e);
	});

	$('#list').on('click', '.delete-row', function(e)
	{
		e.preventDefault();
		var tableRow = $(this).parents('tr');
		var rowId = tableRow.data('content-id');
		if (tableRow.data('working'))
			return;
		tableRow.data('working', true);
		queue.push(new Job('list-delete-row', [listId, rowId]));
		queue.delayedFlush();
		tableRow.find('div.animate-me').slideUp('fast', function()
		{
			tableRow.remove();
		});
	});

	$('#add-row input').click(function(e)
	{
		e.preventDefault();
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
			tableRow.find('.edit-content:eq(0)').click();
		});
	});
});
