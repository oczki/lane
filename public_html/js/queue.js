function Queue()
{
	var q = this;

	q.jobs = [];
	q.interval = false;
	q.flushing = false;
	q.disabled = false;

	q.push = function(job)
	{
		enableExitConfirmation('Changes were not saved!');
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
		$('#save-info').text('Saving...');

		var url = $('meta[data-job-executor-url]').attr('data-job-executor-url');
		var data = {jobs: q.jobs};
		q.jobs = [];
		$.post(url, data).done(function()
		{
			$('#save-info').text('Saved');

			if (typeof(callback) !== 'undefined')
				callback();

		}).fail(function(xhr)
		{
			$('#save-info').text('Errors!');

			alert("An error occured:\n\n" +
				xhr.responseJSON.error +
				"\n\nPlease reload the page and redo the changes.");

			q.disabled = true;
		}).always(function()
		{
			disableExitConfirmation();
			q.flushing = false;
		});
	}

	q.delayedFlush = function()
	{
		$('#save-info').text('Saving...');

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

var queue = new Queue();
function QueueFactory()
{
	return queue;
}
