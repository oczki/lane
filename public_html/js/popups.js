function showPopup(url, cb)
{
	url = appendUrlParameter(url, 'simple');
	$.get(url, function(rawContent)
	{
		var content = $(rawContent);

		var showFunc = function()
		{
			var elementsToAdd = content.filter('.ajax-wrapper');
			var popupDiv = $('<div class="popup"></div>');
			var coverDiv = $('<div class="cover"></div>');
			var closeLink = $('<a class="popup-close" href="#"><i class="icon icon-close"></i></a>');
			popupDiv.append(elementsToAdd);
			popupDiv.prepend(closeLink);

			closeLink.click(function(e)
			{
				e.preventDefault();
				closePopup(popupDiv);
			});

			//get stuff into dom
			$('body').append(coverDiv);
			$('body').append(popupDiv);

			coverDiv.hide();
			popupDiv.hide();

			popupDiv.position({
				collision: 'fit',
				of: $('body'),
				my: 'center center+15%',
				at: 'center center'});

			coverDiv.fadeIn();
			popupDiv.fadeIn();

			//focus first input or link
			popupDiv.find('a:not(.popup-close), input').eq(0).focus();

			//bind escape key
			popupDiv.bind('keydown', function(e)
			{
				if (e.keyCode == 27)
					closePopup(popupDiv);
			});

			$('*').bind('focusin', popupTabFix);

			if (typeof(cb) !== 'undefined')
			{
				cb(popupDiv);
			}
		};

		var stylesheets = content.filter('link');
		if (stylesheets.length > 0)
		{
			stylesheets.load(showFunc);
			$('head').append(stylesheets);
		}
		else
		{
			showFunc();
		}
	});
}

function closePopup()
{
	$('.popup:last').prevAll('.cover:first').fadeOut(function()
	{
		$(this).remove();
	});
	$('.popup:last').fadeOut(function()
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
