function createColumnTableRow(data)
{
	var row = $('<tr></tr>');
	row.data('data-id', data.id);

	var cell = $('<td>');
	var dragger = $('<a>');
	dragger.attr('href', '#');
	dragger.addClass('dragger');
	dragger.append('<i class="icon icon-drag">');
	cell.append(dragger);
	row.append(cell);

	var cell = $('<td>');
	cell.addClass('text');
	var nameInput = $('<input>');
	nameInput.attr('type', 'text');
	nameInput.attr('name', 'columns[' + data.id + '][name]');
	nameInput.attr('value', data.name);
	cell.append(nameInput);
	row.append(cell);

	var cell = $('<td>');
	cell.addClass('align');
	$.each(['left', 'center', 'right'], function(j, alignment)
	{
		var radioInput = $('<input>');
		radioInput.attr('type', 'radio');
		radioInput.attr('name', 'columns[' + data.id + '][align]');
		radioInput.attr('value', alignment);
		radioInput.prop('checked', data.align == alignment);
		radioInput.attr('id', 'align-' + data.id + '-' + alignment);
		var label = $('<label>');
		label.addClass('icon icon-align-' + alignment);
		label.attr('for', radioInput.attr('id'));

		cell.append(radioInput);
		cell.append(label);
	});
	row.append(cell);

	var cell = $('<td>');
	cell.addClass('delete-column');
	var deleteLink = $('<a>');
	deleteLink.attr('href', '#');
	deleteLink.append('<i class="icon icon-delete">');
	cell.append(deleteLink);
	row.append(cell);

	row.find('td').wrapInner('<div class="animate-me">');

	return row;
}

function showHideCustomCss(e)
{
	if ($('#list-settings .basic-settings .custom-css').is(':checked'))
		$('#list-settings .custom-css-edit').slideDown();
	else
		$('#list-settings .custom-css-edit').slideUp();
}

$(function()
{
	//data load
	var listId = $('#list-settings').attr('data-list-id');
	var listColumns = $.parseJSON($('#list-settings').attr('data-list-columns'));
	var lastContentId = $('#list-settings').attr('data-last-content-id');
	for (var i in listColumns)
	{
		var row = createColumnTableRow(listColumns[i]);
		$('#list-settings table tbody').append(row);
	}


	//cancelling
	$('#list-settings .cancel').click(function(e)
	{
		e.preventDefault();
		closePopup($(e.target).parents('.popup'));
	});


	//dragging
	function listColumnDragger(e)
	{
		var target = e.data;
		while (e.pageY < target.offset().top && target.prev('tr').length > 0)
			target.insertBefore(target.prev('tr'));
		while (e.pageY > target.offset().top + target.height() && target.next('tr').length > 0)
			target.insertAfter(target.next('tr'));
	}
	$('#list-settings').on('mousedown', '.dragger', function(e)
	{
		e.preventDefault();
		var target = $(e.target).parents('tr');
		target.addClass('dragging');

		$('body')
			.addClass('dragging')
			.on('mousemove', target, listColumnDragger)
			.one('mouseup', function(e)
		{
			$('body').removeClass('dragging')
			target.removeClass('dragging');
			e.preventDefault();
			$('body').off('mousemove', listColumnDragger);
		});
	});


	//deleting the column
	$('#list-settings').on('click', '.delete-column', function(e)
	{
		e.preventDefault();

		if ($(this).parents('table').find('tbody tr').length == 1)
		{
			alert('Cannot delete last column.');
			return;
		}

		$(e.target).parents('tr').find('div.animate-me').slideUp(function()
		{
			$(this).parents('tr').remove();
		});
	});


	//adding new column
	$('#list-settings .add-column').click(function(e)
	{
		var newColumn = {
			align: 'left',
			id: ++ lastContentId
		};
		var tableRow = createColumnTableRow(newColumn);
		$('#list-settings table tbody').append(tableRow);
		tableRow.find('div.animate-me').hide().slideDown('fast', function()
		{
			tableRow.find('input:first').focus();
		});
	});


	//saving current sort
	$('#list-settings .save-sort').click(function(e)
	{
		e.preventDefault();

		alert('Not implemented yet.');
	});


	//deleting list
	$('#list-settings .delete').click(function(e)
	{
		e.preventDefault();

		var text = 'Do you really want to delete list "' +
			$('#list-settings .basic-settings [name=name]').val() +
			'"? This operation cannot be undone!';
		if (confirm(text))
		{
			var url = $('#list-settings').attr('action');
			var data = {jobs: [new Job('list-delete', [listId])]};
			sendAjax(url, data, function()
			{
				window.location.href = '/';
			});
		}
	});


	//form submit
	$('#list-settings').submit(function(e)
	{
		e.preventDefault();

		//construct previous column map (before changes)
		var previousColumns = {};
		for (var i in listColumns)
		{
			previousColumns[listColumns[i].id] = listColumns[i];
			previousColumns[listColumns[i].id].priority = i;
		}

		//construct current column map (after changes)
		var currentColumns = {};
		$('#list-settings tbody tr').each(function(i, row)
		{
			var row = $(row);
			var currentColumn =
			{
				id: row.data('data-id'),
				name: row.find('input[type=text]').val(),
				align: row.find('input[type=radio]:checked').val(),
				priority: i,
			};
			currentColumns[currentColumn.id] = currentColumn;
		});

		//construct job queue from above data
		var jobs = [];

		//process old columns
		$.each(previousColumns, function(i, previousColumn)
		{
			if (!(previousColumn.id in currentColumns))
			{
				//delete removed columns
				jobs.push(new Job('list-delete-column', [listId, previousColumn.id]));
			}
			else
			{
				//update old columns if necessary
				var currentColumn = currentColumns[previousColumn.id];

				if (previousColumn.name != currentColumn.name)
				{
					jobs.push(new Job('list-set-column-name', [
						listId,
						currentColumn.id,
						currentColumn.name]));
				}

				if (previousColumn.align != currentColumn.align)
				{
					jobs.push(new Job('list-set-column-align', [
						listId,
						currentColumn.id,
						currentColumn.align]));
				}
			}
		});

		//add new columns
		$.each(currentColumns, function(i, currentColumn)
		{
			if (currentColumn.id in previousColumns)
				return;

			jobs.push(new Job('list-add-column', [
				listId,
				currentColumn.id,
				currentColumn.name,
				currentColumn.align]));
		});

		//set order to all columns now that they were removed and added
		$.each(currentColumns, function(i, currentColumn)
		{
			jobs.push(new Job('list-set-column-pos', [
				listId,
				currentColumn.id,
				currentColumn.priority]));
		});

		//set other stuff
		jobs.push(new Job('list-set-name', [
			listId,
			$(this).find('.basic-settings [name=name]').val()]));

		jobs.push(new Job('list-set-visibility', [
			listId,
			$(this).find('.basic-settings [name=visibility]').is(':checked') ? 1 : 0]));

		jobs.push(new Job('list-show-row-ids', [
			listId,
			$(this).find('.basic-settings [name=row-ids]').is(':checked') ? 1 : 0]));

		jobs.push(new Job('list-set-css', [
			listId,
			$(this).find('.custom-css-edit textarea').val()]));


		$(this).data('additional-data', {jobs: jobs});
	});


	// showing custom css
	$('#list-settings .basic-settings .custom-css').click(showHideCustomCss);
	showHideCustomCss();
});