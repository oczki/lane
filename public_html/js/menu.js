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
			});

	$.getScript('/js/jquery.focuslost.js', function()
	{
		$('#menu').focuslost(hideMenu);
	});
});
