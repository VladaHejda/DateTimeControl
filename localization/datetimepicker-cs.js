$(function() {
	$.datepicker.regional['cs'] = {
		currentText: 'nyní',
		closeText: 'zavřít',
		prevText: 'dříve',
		nextText: 'později',
		timeText: 'čas',
		timeOnlyTitle: 'zvol čas',
		hourText: 'hodina',
		minuteText: 'minuta',
		secondText: 'vteřina',
		millisecText: 'milisekunda',
		microsecText: 'mikrosekunda',
		timezoneText: 'časová zóna',
		monthNames: ['leden', 'únor', 'březen', 'duben', 'květen', 'červen', 'červenec', 'srpen',
			'září', 'říjen', 'listopad', 'prosinec'],
		monthNamesShort: ['led', 'úno', 'bře', 'dub', 'kvě', 'čer', 'čvc', 'srp', 'zář', 'říj', 'lis', 'pro'],
		dayNames: ['neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota'],
		dayNamesShort: ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'],
		dayNamesMin: ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'],
		weekHeader: 'Týd',
		firstDay: 1
	};
	$.datepicker.setDefaults($.datepicker.regional['cs']);
});
