function enableExitConfirmation(message)
{
	if (typeof(message) === 'undefined')
		message = 'There are unsaved changes.';

	$(window).bind('beforeunload', function(e)
	{
		return message;
	});
}

function disableExitConfirmation()
{
	$(window).unbind('beforeunload');
}

function appendUrlParameter(url, key, value)
{
	url = url.replace(new RegExp('[?&]' + key + '(=[^?&]+|(?!>[?&])|$)', 'g'), '');
	var sep = url.indexOf('?') != - 1
		? '&'
		: '?';
	url += sep;
	url += key;
	if (typeof(value) !== 'undefined')
		url += '=' + value;
	return url;
}

function sendAjax(url, data, successFunc, errorFunc)
{
	$.ajax(
	{
		type: 'POST',
		url: url,
		data: data,
		success: function(rawContent)
		{
			var content = $(rawContent);
			if (content.find('.success').length > 0)
			{
				if (typeof(successFunc) !== 'undefined')
					successFunc(content);
				else
					window.location = content.filter('meta[data-current-url]').attr('data-current-url');
			}
			else
			{
				if (typeof(errorFunc) !== 'undefined')
					errorFunc(content);
				else
					alert(content.find('.message').text());
			}
		},
	});
}

$(function()
{
	$('body').on('submit', 'form', function(e)
	{
		e.preventDefault();

		var form = $(this);
		var url = appendUrlParameter(form.attr('action'), 'simple');
		var data = form.serialize();
		var additionalData = form.data('additional-data');
		if (typeof(additionalData) !== 'undefined')
			data += '&' + $.param(additionalData);

		var target = form.closest('.content-wrapper');
		if (target.length == 0)
			target = form;

		var send = function()
		{
			sendAjax(url, data, form.data('success-callback'), form.data('error-callback'));
		}

		var messages = target.find('.message');
		if (messages.length > 0)
			messages.slideUp().fadeOut('fast', send);
		else
			send();
	});
});

//dragging
function dragHandler(e)
{
	var target = e.data;
	while (e.pageY < target.offset().top && target.prev().length > 0)
	{
		target.data('changed', true);
		target.insertBefore(target.prev());
	}
	while (e.pageY > target.offset().top + target.height() && target.next().length > 0)
	{
		target.data('changed', true);
		target.insertAfter(target.next());
	}
}

function initDragger(dragger, parentElement, dragFinishCallback)
{
	$(dragger).mousedown(function(e)
	{
		e.preventDefault();
		var target = $(e.target).parents(parentElement);
		target.addClass('dragging');
		target.data('changed', false);

		$('body')
			.addClass('dragging')
			.on('mousemove', target, dragHandler)
			.one('mouseup', function(e)
		{
			e.preventDefault();
			target.removeClass('dragging');
			$('body').removeClass('dragging')
			$('body').off('mousemove', dragHandler);
			if (typeof(dragFinishCallback) !== 'undefined')
				dragFinishCallback(dragger, target.data('changed'));
		});
	}).click(function(e)
	{
		e.preventDefault();
	});
}
