function showMenu()
{
	$('#menu').addClass('active');
}

function hideMenu()
{
	$('#menu').removeClass('active');
}

function toggleMenu()
{
	if (!$('#menu').hasClass('active'))
		showMenu();
	else
		hideMenu();
}

$(function()
{
	$('html').click(function()
	{
		hideMenu();
	});
	$('#menu').click(function(e)
	{
		e.stopPropagation();
	});
	$('#menu-trigger a')
		.focus(function(e)
		{
				e.preventDefault();
				showMenu();
		})
		.mousedown(function(e)
		{
			e.preventDefault();
			toggleMenu();
		})
		.click(function(e)
		{
			e.preventDefault();
		});

	$.getScript('/js/jquery.focuslost.js', function()
	{
		$('#menu').focuslost(hideMenu);
	});

	$('#menu .login, #menu .register').click(function(e)
	{
		e.preventDefault();
		hideMenu();
		showPopup($(this).attr('href'));
	});

	$('#menu .logout').click(function(e)
	{
		e.preventDefault();
		$.post($(this).attr('href'), function()
		{
			window.location.reload();
		});
	});
});
