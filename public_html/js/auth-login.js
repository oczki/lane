$(function()
{
	$('.forgot-form')
		.hide()
		.data('success-callback', function(rawContent)
		{
			var message = $(rawContent).find('.message').text();
			$('.forgot-form').height($('.forgot-form').height());
			$('.forgot-form').slideUp();
			$('.forgot').fadeOut(function()
			{
				alert(message);
			});
		});

	$('.forgot')
		.show()
		.click(function(e)
		{
			$('.forgot-form').slideToggle();
		});
});
