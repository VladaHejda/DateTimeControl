$(function() {
	$('input.datetime').each(function(i, el) {
		el = $(el);
		var dateFormat = el.attr('data-date-format');
		var timeFormat = el.attr('data-time-format');
		var settings = {
			dateFormat: dateFormat,
			showWeek: true,
			onClose: function(text, f) {
				if (text.match(/[0-9]/)) {
					var oldText = text;
					var date;
					if(typeof $(this).attr('ending') != 'undefined') {
						date = $.datepicker.parseDate(dateFormat, text);
						if (date.getHours() == 0 && date.getMinutes() == 0 && date.getSeconds() == 0) {
							text = text.replace(
								$.datepicker.formatTime(timeFormat, {hour: 0, minute: 0, second: 0}),
								$.datepicker.formatTime(timeFormat, {hour: 23, minute: 59, second: 59})
							);
						}
					}
					if (text.match(/\bW\b/)) {
						if (typeof date == 'undefined') {
							date = $.datepicker.parseDate(dateFormat, text);
						}
						// todo value should be set onSelect, but datetimepicker will rewrite it after calling this closure
						text = text.replace(/\bW\b/, $.datepicker.iso8601Week(date));
					}
					if (oldText != text) {
						f.input.val(text);
					}
				}
			}
		};
		var min = el.attr('min');
		if (typeof min != 'undefined') {
			settings.minDate = new Date(min);
		}
		var max = el.attr('max');
		if (typeof max != 'undefined') {
			settings.maxDate = new Date(max);
		}
		if (typeof timeFormat != 'undefined') {
			settings.timeFormat = timeFormat;
			el.datetimepicker(settings);
		} else {
			el.datepicker(settings);
		}
	});
});
