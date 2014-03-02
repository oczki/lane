function showMenu()
{
	$('#menu').addClass('active');
}

function hideMenu()
{
	$('#menu').removeClass('active');
}

$(function()
{
	$('#menu-trigger a')
		.bind('mouseenter focus', showMenu)
		.click(function(e)
			{
				e.preventDefault();
			});

	$.getScript('/js/jquery.focuslost.js', function()
	{
		$('#menu').focuslost(hideMenu);
	});

	$('#menu').mouseleave(hideMenu);
});
