function showPopup(url, cb)
{
	url = appendUrlParameter(url, 'simple');
	$.get(url, function(rawContent)
	{
		var content = $(rawContent);
		$('head').append(content.filter('link'));

		var elementsToAdd = content.filter('.ajax-wrapper');
		var popupDiv = $('<div class="popup"></div>');
		var coverDiv = $('<div class="cover"></div>');
		popupDiv.append(elementsToAdd);
		var popupWrapperDiv = popupDiv.wrap('<div class="popup-wrapper"></div>').parent();

		var elementsToAdd = [];
		elementsToAdd.push(coverDiv);
		elementsToAdd.push(popupWrapperDiv);

		$.each(elementsToAdd, function(i, el)
		{
			el.hide();
			$('body').append(el);
			el.fadeIn();
		});

		popupDiv.css('width', popupDiv.width() + 'px');

		popupDiv.find('a, input').eq(0).focus();

		popupWrapperDiv.bind('keydown', function(e)
		{
			if (e.keyCode == 27)
				closePopup(popupDiv);
		});

		$('*').bind('focusin', popupTabFix);

		if (typeof(cb) !== 'undefined')
		{
			cb(popupDiv);
		}
	});
}

function closePopup()
{
	$('.popup-wrapper:last').prevAll('.cover:first').fadeOut(function()
	{
		$(this).remove();
	});
	$('.popup-wrapper:last').fadeOut(function()
	{
		$(this).remove();
	});
	$('*').unbind('focusin', popupTabFix);
}

function popupTabFix(e)
{
	if ($(e.target).parents('.popup').length == 0)
		$('.popup a, .popup input').eq(0).focus();
}
