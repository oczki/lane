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
		tableCell.attr('class', $('#list thead th').eq(i).attr('class'));

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

		var checkbox = $('<input>');
		checkbox.attr('type', 'checkbox');
		checkbox.data('content-id', data.id);

		tableCell.append(checkbox);

		tableRow.append(tableCell);
	}

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

	if (canEdit)
	{
		$('#list').resizableColumns({
			resizeFromBody: false,
			store: {
				convert: function(elementId)
				{
					return elementId.substring(elementId.indexOf('-') + 1);
				},
				get: function(elementId)
				{
					return null;
				},
				set: function(elementId, newWidth)
				{
					var columnId = this.convert(elementId);
					queue.push(new Job('list-set-column-width', [listId, columnId, newWidth]));
					queue.delayedFlush();
				}
			}
		});
		$('.rc-handle').append('<i class="icon icon-drag"/></i>');
	}

	$('#list').on('click', '.edit-content', function(e)
	{
		e.preventDefault();
		var tableCell = $(this).parents('td');
		var tableRow = tableCell.parents('tr');
		if (tableRow.data('working'))
			return;
		if (tableRow.find('input[type=edit]:visible').length > 0)
			return;
		tableRow.addClass('active edit');
		tableCell.find('span').hide();

		var input = $('<input>');
		input.attr('type', 'text');
		input.val(tableCell.find('span').text());
		tableCell.append(input.wrap('<div class="input-wrapper">').parent());
		input.hide().fadeIn('fast').focus();
	});

	var editCellContent = function(e)
	{
		var editLink = $(e.target);
		var tableCell = $(e.target).parents('td');
		var tableRow = tableCell.parents('tr');
		var text = tableCell.find('input[type=text]').val();
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
	$('#list').on('blur', 'input[type=text]', editCellContent);
	$('#list').on('keypress', 'input[type=text]', function(e)
	{
		if (e.keyCode == 13)
			editCellContent(e);
	});

	var refreshDeleteRowsButton = function(e)
	{
		$('#delete-rows').prop('disabled', $('#list input[type=checkbox]:checked').length == 0);
	};
	$('#list').on('click', 'input[type=checkbox]', function(e)
	{
		refreshDeleteRowsButton(e);
	});
	refreshDeleteRowsButton();

	$('#delete-rows').click(function(e)
	{
		var tableRows = $('#list input[type=checkbox]:checked').parents('tr');
		tableRows.each(function(i, tableRowNode)
		{
			e.preventDefault();
			var tableRow = $(tableRowNode);
			var rowId = tableRow.data('content-id');
			if (tableRow.data('working'))
				return;
			tableRow.data('working', true);
			queue.push(new Job('list-delete-row', [listId, rowId]));
			tableRow.remove();
		});
		queue.delayedFlush();
	});

	$('#add-row').click(function(e)
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
		tableRow.find('.edit-content:eq(0)').click();
	});
});
