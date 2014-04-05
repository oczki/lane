$(function()
{
	//files through AJAX
	var form = $('#list-import form');
	form.data('serializer', function()
	{
		return new FormData(form.get(0));
	});
});
