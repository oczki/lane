function showMenu()
{
	$('#menu-trigger, #menu').addClass('active');
	repositionMenu();
}

function hideMenu()
{
	$('#menu-trigger, #menu').removeClass('active');
}

function toggleMenu()
{
	if (!$('#menu').hasClass('active'))
		showMenu();
	else
		hideMenu();
}

function repositionMenu()
{
	$('#menu').position({
		collision: 'fill',
		of: '#menu-trigger',
		my: 'right top',
		at: 'right bottom'});
}

$(function()
{
	$(window).resize(function()
	{
		if ($('#menu').is(':visible'))
			repositionMenu();
	});
	$('html').click(function()
	{
		hideMenu();
	});
	$('#menu, #menu-trigger').click(function(e)
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

	$('#top-menu .add-list, #top-menu .list-settings').click(function(e)
	{
		e.preventDefault();
		showPopup($(this).attr('href'));
	});

	$('#menu .import, #menu .account-settings, #menu .login, #menu .register').click(function(e)
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
