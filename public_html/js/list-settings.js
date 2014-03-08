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
	cell.addClass('delete');
	var deleteLink = $('<a>');
	deleteLink.attr('href', '#');
	deleteLink.addClass('delete-column');
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
		newColumn = {
			align: 'left',
			id: ++ lastContentId
		};
		var row = createColumnTableRow(newColumn);
		$('#list-settings table tbody').append(row);
		row.find('div.animate-me').hide().slideDown('fast', function()
		{
			row.find('input:first').focus();
		});
	});


	// showing custom css
	$('#list-settings .basic-settings .custom-css').click(showHideCustomCss);
	showHideCustomCss();
});
