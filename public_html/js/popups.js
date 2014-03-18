function showPopup(url, cb)
{
	url = appendUrlParameter(url, 'simple');
	$.get(url, function(rawContent)
	{
		var content = $(rawContent);
		var stylesheets = content.filter('link');
		var scripts = content.filter('script[src]');

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

			//download the scripts
			$.each(scripts, function(i, script)
			{
				$.getScript($(script).attr('src'));
			});

			//hide stuff
			coverDiv.hide();
			popupDiv.hide();

			//position the popup
			popupDiv.position({
				collision: 'fit',
				of: $(window),
				my: 'center center-10%',
				at: 'center center'});

			//show stuff
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

			//trap [tab] into popup
			$('*').bind('focusin', popupTabFix);

			//execute custom callback
			if (typeof(cb) !== 'undefined')
			{
				cb(popupDiv);
			}
		};

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
