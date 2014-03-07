function Queue()
{
	var q = this;

	q.jobs = [];
	q.interval = false;
	q.flushing = false;
	q.disabled = false;

	q.push = function(job)
	{
		q.jobs.push(job);
	}

	q.flush = function(callback)
	{
		if (q.disabled)
		{
			return;
		}

		if (q.flushing)
		{
			window.setTimeout(q.flush, 500);
			return;
		}

		q.flushing = true;
		enableExitConfirmation('Changes were not saved!');
		$('#save-info').text('Saving...');

		var url = '/exec';
		var data = {jobs: q.jobs};
		$.post(url, data, function(rawContent)
		{
			var content = $(rawContent);

			//check for any errors, if errors were found - disable all editing
			if (content.find('.error').length > 0)
			{
				alert("An error occured:\n\n" +
					content.find('.error').text() +
					"\n\nPlease reload the page and redo the changes.");
				q.disabled = true;
			}

			if (typeof(callback) !== 'undefined')
				callback();

			disableExitConfirmation();
			$('#save-info').text('Saved');

			q.flushing = false;
			q.jobs = [];
		});
	}

	q.delayedFlush = function()
	{
		if (q.interval)
			window.clearTimeout(q.interval);

		q.interval = window.setTimeout(q.flush, 1000);
	}
}

function Job(name, args)
{
	this.name = name;
	this.args = args;
}

$(function()
{
	var queue = new Queue();
	var listUniqueId = $('#list-unique-id').val();

	$('#add-row').click(function()
	{
		queue.push(new Job('list-add-row', [listUniqueId]));
		queue.delayedFlush();
	});

	$('#add-new-list, #menu-login, #menu-register').click(function(e)
	{
		e.preventDefault();
		showPopup($(this).attr('href'));
	});

	$('#menu-logout').click(function(e)
	{
		e.preventDefault();
		$.post($(this).attr('href'), function()
		{
			window.location.reload();
		});
	});
});
