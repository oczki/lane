$(function()
{
	var queue = QueueFactory();
	var canEdit = $('#list').attr('data-can-edit') == '1';
	var listId = $('#list').attr('data-list-id');
	var listColumns = $.parseJSON($('#list').attr('data-list-columns'));
	var lastContentId = $('#list').attr('data-last-content-id');

	var sortStyle;
	try
	{
		sortStyle = JSON.parse($('#list').attr('data-sort-style'));
	}
	catch (err)
	{
		sortStyle = null;
	}
	$('#list')
		.tablesorter(sortStyle != null ? {sortList: sortStyle} : {})
		.bind('sortEnd', function(sorter)
			{
				$('#list').attr('data-sort-style', JSON.stringify(sorter.target.config.sortList));
			});

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

	$('#list tbody').on('mouseenter focusin', 'tr', function(e)
	{
		var tableRow = $(this);;
		tableRow.addClass('active');
	});

	$('#list tbody').on('mouseleave focusout', 'tr', function(e)
	{
		var tableRow = $(this);;
		if (!tableRow.hasClass('edit'))
			tableRow.removeClass('active');
		if (e.type == 'focusout')
			$('#list').data('last-focused-row', tableRow);
	});


	$('#list tbody').on('click', '.edit-content', function(e)
	{
		e.preventDefault();
		var tableCell = $(this).parents('td');
		var tableRow = tableCell.parents('tr');
		tableRow.addClass('active edit');
		tableCell.find('span').hide();
		if (tableCell.find('input:visible').length > 0)
			return;

		var input = $('<input>');
		input.attr('type', 'text');
		input.val(tableCell.find('span').text());
		tableCell.append(input.wrap('<div class="input-wrapper">').parent());
		input.focus();
		var inputNode = input.get(0);
		if (inputNode.createTextRange)
		{
			var NodeRange = inputNode.createTextRange();
			NodeRange.moveStart('character',inputNode.value.length);
			NodeRange.collapse();
			NodeRange.select();
		}
		else if (inputNode.selectionStart || inputNode.selectionStart == '0')
		{
			var elemLen = inputNode.value.length;
			inputNode.selectionStart = elemLen;
			inputNode.selectionEnd = elemLen;
			inputNode.focus();
		}
	});

	var cancelEdit = function(tableCell)
	{
		var tableRow = tableCell.parents('tr');
		tableRow.removeClass('edit');
		tableCell.find('.input-wrapper').fadeOut('fast', function()
		{
			tableCell.find('span').fadeIn();
			tableCell.find('.input-wrapper').remove();
			if (!tableRow.is(':hover') && tableRow.find(':focus').length == 0)
				tableRow.removeClass('active');
			$('#list').trigger('updateCell', [tableCell, false]);
			tableCell.removeClass('working');
		});
	}
	var editCellContent = function(tableCell)
	{
		if (tableCell.hasClass('working'))
			return;
		tableCell.addClass('working');
		var editLink = tableCell.find('.edit-link');
		var tableRow = tableCell.parents('tr');
		var oldText = tableCell.find('span').text();
		var text = tableCell.find('input[type=text]').val();
		var rowId = tableRow.attr('data-content-id');
		var columnId = listColumns[tableCell.index()].id;

		if (text != oldText)
		{
			queue.push(new Job('list-edit-cell', [listId, rowId, columnId, text]));
			queue.delayedFlush();
		}
		tableCell.find('span').text(text).attr('title', text);
		cancelEdit(tableCell);
	};
	$('#list tbody').on('blur', 'input[type=text]', function(e)
	{
		var tableCell = $(this).parents('td');
		e.preventDefault();
		editCellContent(tableCell);

	});
	$('#list tbody').on('keydown', 'input[type=text]', function(e)
	{
		var tableCell = $(this).parents('td');
		var tableRow = tableCell.parents('tr');

		if (e.keyCode == 9 || e.keyCode == 13)
		{
			e.preventDefault();
			editCellContent(tableCell);

			var target;
			if (!e.shiftKey)
			{
				target = (tableCell.next('td').find('.edit-content').length > 0)
					? tableCell.next('td')
					: tableRow.next('tr');

				target.find('.edit-content').first().click();
				if (target.length == 0)
					$('.add-row').click();
			}
			else
			{
				target = (tableCell.prev('td').find('.edit-content').length > 0)
					? tableCell.prev('td')
					: tableRow.prev('tr');

				target.find('.edit-content').last().click();
			}
		}

		if (e.keyCode == 27)
		{
			e.preventDefault();
			cancelEdit(tableCell);
		}
	});

	var refreshDeleteRowsButton = function(e)
	{
		$('.delete-rows').prop('disabled', $('#list input[type=checkbox]:checked').length == 0);
	};
	$('#list tbody').on('click', 'input[type=checkbox]', function(e)
	{
		refreshDeleteRowsButton(e);
	});
	refreshDeleteRowsButton();

	$('.delete-rows').click(function(e)
	{
		var tableRows = $('#list input[type=checkbox]:checked').parents('tr');
		tableRows.each(function(i, tableRowNode)
		{
			e.preventDefault();
			var tableRow = $(tableRowNode);
			var rowId = tableRow.attr('data-content-id');
			queue.push(new Job('list-delete-row', [listId, rowId]));
			tableRow.remove();
			$('#list').trigger('update');
		});
		queue.delayedFlush();
	});

	$('.add-row').click(function(e)
	{
		e.preventDefault();
		var newRow = {
			id: ++ lastContentId,
			content: new Array(listColumns.length).map(String.prototype.valueOf, '')
		};
		queue.push(new Job('list-add-row', [listId, newRow.id]));
		queue.delayedFlush();
		var tableRow = $('tfoot tr').clone();
		tableRow.attr('data-content-id', newRow.id);
		$('#list tbody').append(tableRow);
		$('#list').trigger('addRows', [tableRow, false]);
		tableRow.find('.edit-content:eq(0)').click();
	});
});
