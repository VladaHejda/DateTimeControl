Nette DateTime Form Control
===========================

**including week number choice**

Installation
------------

- link jQuery into your page
```html
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
```

- link jQuery UI:
```html
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/smoothness/jquery-ui.css" />
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/jquery-ui.min.js"></script>
```

- Download and link [jQuery UI Datepicker](http://trentrichardson.com/examples/timepicker/)
```html
<script src="{$basePath}/js/jquery-ui-timepicker-addon.js"></script>
```

- Download [DateTimeControl.php](src/DateTimeControl.php), let it load by autoloading

- Download [datetimepicker.js](src/datetimepicker.js) and link it
```html
<script src="{$basePath}/js/datetimepicker.js"></script>
```

- If you want to localize the calendar, download or create similar localization file as [here](localization).

- add method into your app, e.g. in `BasePresenter::startup()`:
```php
use Nette\Forms;
use Nais\Components\DateTimeControl;

Forms\Container::extensionMethod('addDateTime', function (Forms\Container $form, $name, $label = NULL, $cols = NULL, $invalidMessage = NULL) {
	return $form[$name] = new DateTimeControl($label, $cols, $invalidMessage);
});
```

- use it:
```php
$form->addDateTime('from')
	->setDefaultValue(new \Nette\DateTime('- 1 day'))
	->setTimeFormat(NULL);
```

- and customize:
```php
use Nais\Components\DateTimeControl;

DateTimeControl::$months = array('leden', 'únor', 'březen', 'duben', 'květen', 'červen', 'červenec', 'srpen',
	'září', 'říjen', 'listopad', 'prosinec');
DateTimeControl::$monthsShort = array('led', 'úno', 'bře', 'dub', 'kvě', 'čer', 'čvc', 'srp', 'zář', 'říj',
	'lis', 'pro');
DateTimeControl::$days = array('neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota');
DateTimeControl::$daysShort = array('ne', 'po', 'út', 'st', 'čt', 'pá', 'so');
DateTimeControl::$defaultDateFormat = 'dd. mm. yy';
DateTimeControl::$defaultTimeFormat = '(HH:mm)';
```
