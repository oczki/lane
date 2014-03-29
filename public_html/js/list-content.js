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
				$('#list tr').removeClass('fresh');
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
					queue.push(new Job('list-set-column-width', {
						'list-id': listId,
						'column-id': columnId,
						'new-column-width': newWidth}));
					queue.delayedFlush();
				}
			}
		});
		$('.rc-handle').append('<i class="icon icon-drag"/></i>');
	}

	var startEdit = function(tableCell)
	{
		var tableRow = tableCell.parents('tr');
		tableRow.addClass('edit');
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

		input.one('blur', function(e)
		{
			var tableCell = $(this).parents('td');
			if (tableCell.hasClass('working'))
				return;

			e.preventDefault();
			doEdit(tableCell);
			cancelEdit(tableCell);

		});

		input.on('keydown', function(e)
		{
			var tableCell = $(this).parents('td');
			var tableRow = tableCell.parents('tr');

			if (tableCell.hasClass('working'))
				return;

			if (e.keyCode == 13)
			{
				input.off('blur');

				e.preventDefault();
				doEdit(tableCell);
				cancelEdit(tableCell);
			}

			else if (e.keyCode == 9)
			{
				input.off('blur');

				e.preventDefault();
				doEdit(tableCell);
				cancelEdit(tableCell);

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

			else if (e.keyCode == 27)
			{
				input.off('blur');

				e.preventDefault();
				cancelEdit(tableCell);
			}
		});
	};

	var cancelEdit = function(tableCell)
	{
		var tableRow = tableCell.parents('tr');
		tableRow.removeClass('edit');
		tableCell.find('.input-wrapper').fadeOut('fast', function()
		{
			tableCell.find('span').fadeIn();
			tableCell.find('.input-wrapper').remove();
			$('#list').trigger('updateCell', [tableCell, false]);
			tableCell.removeClass('working');
		});
	};

	var doEdit = function(tableCell)
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
			queue.push(new Job('list-edit-cell', {
				'list-id': listId,
				'row-id': rowId,
				'column-id': columnId,
				'new-cell-text': text}));
			queue.delayedFlush();
		}
		tableCell.find('span').text(text).attr('title', text);
	};

	$('#list tbody').on('click', '.edit-content', function(e)
	{
		e.preventDefault();
		var tableCell = $(this).parents('td');
		startEdit(tableCell);
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
			queue.push(new Job('list-delete-row', {
				'list-id': listId,
				'row-id': rowId}));

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

		queue.push(new Job('list-add-row', {
			'list-id': listId,
			'new-row-id': newRow.id}));
		queue.delayedFlush();

		var tableRow = $('tfoot tr').clone();
		if ($('#list .fresh').length == 0)
			tableRow.addClass('fresh');
		tableRow.attr('data-content-id', newRow.id);
		tableRow.find('input[type=checkbox]').attr('id', 'row-' + newRow.id);
		tableRow.find('label[for]').attr('for', 'row-' + newRow.id);
		$('#list tbody').append(tableRow);
		$('#list').trigger('addRows', [tableRow, false]);
		tableRow.find('.edit-content:eq(0)').click();
	});

	$('#search input').on('keydown', function(e)
	{
		if (e.keyCode == 13)
		{
			var texts = $(this).val().toLowerCase().split(/\s+/);
			e.preventDefault();
			$('#list tbody tr').each(function(i, tableRowNode)
			{
				var tableRow = $(tableRowNode);
				var shown = true;
				for (var i = 0; i < texts.length; i ++)
				{
					var text = texts[i];
					shown = shown && (tableRow.text().toLowerCase().indexOf(text) != -1);
				}
				if (shown)
					tableRow.css('display', 'table-row');
				else
					tableRow.css('display', 'none');
			});

			var shownRows = $('#list tbody tr:visible').length;
			var totalRows = $('#list tbody tr').length;
			var changeText = function()
			{
				$('#search-warning .shown-rows').text(shownRows);
				$('#search-warning .total-rows').text(totalRows);
			};
			if (shownRows < totalRows)
			{
				changeText();
				$('#search-warning').slideDown('fast');
			}
			else
			{
				$('#search-warning').slideUp('fast', changeText);
			}
		}
	});
});
