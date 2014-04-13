$(function()
{
	var queue = QueueFactory();
	var canEdit = $('#list').attr('data-can-edit') == '1';
	var listId = $('#list').attr('data-list-id');
	var userName = $('#list').attr('data-user-name');
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
						'user-name': userName,
						'list-id': listId,
						'column-id': columnId,
						'new-column-width': newWidth}));
					queue.delayedFlush();
				}
			}
		});
		$('.rc-handle').append('<i class="icon icon-drag"/></i>');
	}

	var urlRegex = new RegExp(/\[url(=([^\]]+))?\](.+?)\[\/url\]/g);
	var spanClassRegex = new RegExp(/\[([a-zA-Z0-9_-]+)\]((?:.(?!\[\1\]))*?)(\[\/\1\]|$)/g);
	var blockClassRegex = new RegExp(/\[(cell|row):([a-zA-Z0-9_-]+)\]([^\[]*)(\[\/\1(:\2)?\])?/g);
	var cellUpdated = function(tableCell, newText)
	{
		var html = newText;

		//order matters
		html = html.replace(/\\\]/g, '&#93;');
		html = html.replace(/\\\\/g, '&#92;');
		html = html.replace(/\\\[/g, '&#91;');

		html = html.replace(urlRegex, function(match, _, url, text)
		{
			if (!url)
				url = text;
			if (url.indexOf('://') == -1)
				url = 'http://' + url;
			return '<a class="span-url" href="' + url + '">' + text + '</a>';
		});

		html = html.replace(blockClassRegex, function(match, block, className, text)
		{
			return text;
		});

		while (html.match(spanClassRegex))
			html = html.replace(spanClassRegex, function(match, className, text)
			{
				return '<span class="span-' + className + '">' + text + '</span>';
			});

		tableCell.attr('data-orig-text', newText);
		tableCell.find('span.content-holder').html(html).attr('title', newText);
	};

	var rowUpdateStarted = function(tableRow)
	{
		tableRow.find('td').each(function(i, tableCellNode)
		{
			var tableCell = $(tableCellNode);
			if (tableCell.attr('class'))
				tableCell.attr('class', tableCell.attr('class').split(' ').filter(function(c) {
					return c.lastIndexOf('block-', 0) !== 0;
				}).join(' '));
		});
	};

	var rowUpdateFinished = function(tableRow)
	{
		tableRow.find('td').each(function(i, tableCellNode)
		{
			var tableCell = $(tableCellNode);
			var html = tableCell.attr('data-orig-text');
			while (match = blockClassRegex.exec(html))
			{
				var block = match[1];
				var className = match[2];
				var text = match[3];

				if (block == 'row')
					tableRow.find('td').addClass('block-' + className);
				if (block == 'cell')
					tableCell.addClass('block-' + className);
			}
		});
	};

	var startEdit = function(tableCell)
	{
		var tableRow = tableCell.parents('tr');
		tableRow.addClass('edit');
		tableCell.find('span.content-holder').hide();
		if (tableCell.find('input:visible').length > 0)
			return;

		var input = $('<input>');
		input.attr('type', 'text');
		input.val(tableCell.attr('data-orig-text'));
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
			tableCell.find('span.content-holder').fadeIn();
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
		var oldText = tableCell.find('span.content-holder').text();
		var text = tableCell.find('input[type=text]').val();
		var rowId = tableRow.attr('data-content-id');
		var columnId = listColumns[tableCell.index()].id;

		if (text != oldText)
		{
			queue.push(new Job('list-edit-cell', {
				'user-name': userName,
				'list-id': listId,
				'row-id': rowId,
				'column-id': columnId,
				'new-cell-text': text}));
			queue.delayedFlush();
		}
		rowUpdateStarted(tableRow);
		cellUpdated(tableCell, text);
		rowUpdateFinished(tableRow);
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
				'user-name': userName,
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
			'user-name': userName,
			'list-id': listId,
			'new-row-id': newRow.id,
			'new-row-content': newRow.content}));
		queue.delayedFlush();

		var tableRow = $('tfoot tr').clone();
		if ($('#list .fresh').length == 0)
			tableRow.addClass('fresh');
		tableRow.attr('data-content-id', newRow.id);
		tableRow.find('input[type=checkbox]').attr('id', 'row-' + newRow.id);
		tableRow.find('label[for]').attr('for', 'row-' + newRow.id);
		tableRow.find('td').attr('data-orig-text', '');
		$('#list tbody').append(tableRow);
		$('#list').trigger('addRows', [tableRow, false]);
		tableRow.find('.edit-content:eq(0)').click();
	});

	$('#list tbody tr').each(function(i, tableRowNode)
	{
		var tableRow = $(tableRowNode);
		tableRow.find('td').each(function(j, tableCellNode)
		{
			var tableCell = $(tableCellNode);
			tableCell.attr('data-orig-text', tableCell.find('span.content-holder').text());
		});
		rowUpdateStarted(tableRow);
		tableRow.find('td').each(function(j, tableCellNode)
		{
			var tableCell = $(tableCellNode);
			cellUpdated(tableCell, tableCell.attr('data-orig-text'));
		});
		rowUpdateFinished(tableRow);
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
