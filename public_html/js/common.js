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
	var sep = url.indexOf('?') != - 1
		? '&'
		: '?';
	url += sep;
	url += key;
	if (typeof(value) !== 'undefined')
		url += '=' + value;
	return url;
}

$(function()
{
	$('body').on('submit', 'form', function(e)
	{
		e.preventDefault();

		var form = $(this);
		var url = appendUrlParameter(form.attr('action'), 'simple');
		var data = form.serialize();

		var target = form.closest('.ajax-wrapper');
		if (target.length == 0)
			target = form;

		var send = function()
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
						window.location.reload();
					}
					else
					{
						alert(content.find('.message').text());
					}
					e.preventDefault();
				},
			});
		}

		var messages = target.find('.message');
		if (messages.length > 0)
			messages.slideUp().fadeOut('fast', send);
		else
			send();
	});
});
