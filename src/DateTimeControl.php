<?php

namespace Nette\Components;

use Nette;
use Nette\Forms\Form;
use Nette\Utils\Html;


class DateTimeControl extends Nette\Forms\Controls\BaseControl
{
	/** @link http://www.w3.org/TR/NOTE-datetime */
	const W3C_DATE_FORMAT = 'yy-mm-dd',
		W3C_TIME_FORMAT = "'T'HH:mm:ss";

	const JS_DATESTRING = 'Y-m-d\TH:i:s';

	/** @var string */
	public static $defaultInvalidMessage = 'Datetime is invalid.';

	/** @var string */
	public static $defaultDateFormat, $defaultTimeFormat;

	/** @var array localization (day names from Sunday) */
	public static $months, $monthsShort, $days, $daysShort;

	protected static $defaultMonths = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August',
		'September', 'October', 'November', 'December');
	protected static $defaultMonthsShort = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct',
		'Nov', 'Dec');
	protected static $defaultDays = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
	protected static $defaultDaysShort = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');

	/** @var string @link http://api.jqueryui.com/datepicker/#utility-formatDate */
	protected $dateFormat = 'yy-mm-dd';

	/** @var string @link http://trentrichardson.com/examples/timepicker/#tp-formatting */
	protected $timeFormat = "HH:mm";

	/** @var string */
	protected $htmlClass = 'datetime';

	/** @var bool */
	protected $ending = FALSE;

	/** @var string */
	protected $phpDatetimeFormat;

	protected $invalidMessage;

	/** @var string */
	protected $emptyValue;

	/** @var array [\DateTime, \DateTime] */
	protected $rangeLimit = array(NULL, NULL);


	public function __construct($label = NULL, $cols = NULL, $invalidMessage = NULL)
	{
		parent::__construct($label);
		$this->control->size = $cols;

		$this->invalidMessage = $invalidMessage === NULL ? static::$defaultInvalidMessage : $invalidMessage;

		if (empty(static::$months)) {
			static::$months = self::$defaultMonths;
		}
		if (empty(static::$monthsShort)) {
			static::$monthsShort = self::$defaultMonthsShort;
		}
		if (empty(static::$days)) {
			static::$days = self::$defaultDays;
		}
		if (empty(static::$daysShort)) {
			static::$daysShort = self::$defaultDaysShort;
		}

		if (static::$defaultDateFormat) {
			$this->dateFormat = static::$defaultDateFormat;
		}
		if (static::$defaultTimeFormat) {
			$this->timeFormat = static::$defaultTimeFormat;
		}
	}


	/**
	 * Generates control's HTML element.
	 * @return Html
	 */
	public function getControl()
	{
		$control = parent::getControl();

		// todo preserve HTML 5 date input
		$control->type = 'text';
		$control->addClass($this->htmlClass);

		if ($this->ending) {
			$control->ending = TRUE;
		}

		list($min, $max) = $this->rangeLimit;
		if ($min) {
			$control->min = $min->format(self::JS_DATESTRING);
		}
		if ($max) {
			$control->max = $max->format(self::JS_DATESTRING);
		}

		$control->data('date-format', $this->dateFormat);
		$control->data('time-format', $this->timeFormat);

		$value = $this->value;
		if ($value instanceof \DateTime) {
			$value = $value->format($this->getPhpDatetimeFormat());
			$value = $this->translateNames($value);
		} elseif ($this->emptyValue && empty($value)) {
			$value = $this->emptyValue;
		}
		$control->value = $value;

		return $control;
	}


	public function setDateFormat($format)
	{
		$this->dateFormat = $format;
		return $this;
	}


	public function setTimeFormat($format)
	{
		$this->timeFormat = $format;
		return $this;
	}


	public function setHtmlClass($class)
	{
		$this->htmlClass = $class;
		return $this;
	}


	/**
	 * Cause that time is default set to 23:59:59.
	 */
	public function setEndingTime($ending = TRUE)
	{
		$this->ending = $ending;
		return $this;
	}


	/**
	 * @todo Range rule under condition does not work.
	 */
	public function addRule($operation, $message = NULL, $arg = NULL)
	{
		switch ($operation) {
			case Form::RANGE:
				$since = empty($arg) ? NULL : array_shift($arg);
				if (!$since instanceof \DateTime) {
					$since = NULL;
				}

				$to = empty($arg) ? NULL : array_shift($arg);
				if (!$to instanceof \DateTime) {
					$to = NULL;
				}

				if ($since && $to && $since > $to) {
					// todo how behaves text field in such situation?
					throw new Nette\InvalidArgumentException("Time 'since' cannot be older than time 'to'.");
				}

				$this->rangeLimit = array($since, $to);
				parent::addRule(get_class() . '::validateTimeRange', $message, $this->rangeLimit);
				return $this;
				break;

			case Form::FILLED:
				return parent::addRule($operation, $message, $arg);
		}

		throw new Nette\InvalidArgumentException("DateTimeControl does not support rule '$operation'.");
	}


	public function setEmptyValue($value)
	{
		$this->emptyValue = (string) $value;
		return $this;
	}


	public function getEmptyValue()
	{
		return $this->emptyValue;
	}


	public function setDefaultValue($value)
	{
		if (empty($value) || $value instanceof \DateTime || $value === $this->emptyValue) {
			return parent::setDefaultValue($value);
		}

		throw new Nette\InvalidArgumentException("Default value must be DateTime instance or NULL or empty value (see method setEmptyValue() ).");
	}


	public function setValue($value)
	{
		if (empty($value)) {
			$this->value = NULL;
		} elseif ($value instanceof \DateTime || $value === $this->emptyValue) {
			$this->value = $value;
		} else {
			try {
				$this->value = $this->parseDatetime($value);
			} catch (Nette\InvalidArgumentException $e) {
				$this->addError($e->getMessage());
				$this->value = $value;
			}
		}

		return $this;
	}


	public function getValue()
	{
		return $this->value === $this->emptyValue ? NULL : $this->value;
	}


	public static function validateTimeRange(Nette\Forms\IControl $control, $range)
	{
		$value = $control->getValue();
		return empty($value) || Nette\Utils\Validators::isInRange($value, $range);
	}


	/**
	 * @param string $dateFormat
	 * @return string
	 */
	public static function convertDateFormat($dateFormat)
	{
		$search = array(
			'd', 'D', 'm', 'M', 'y', 'dd', 'DD', 'mm', 'MM', 'yy',
		);
		$replace = array(
			'j', 'D', 'n', 'M', 'y', 'd',  'l',  'm',  'F',  'Y',
		);
		return static::convertFormat($search, $replace, $dateFormat);
	}


	/**
	 * @param string $timeFormat
	 * @return string
	 */
	public static function convertTimeFormat($timeFormat)
	{
		$search = array(
			'H', 'h', 'HH', 'hh', 'mm', 'ss', 'tt', 'TT',
		);
		$replace = array(
			'G', 'g', 'H',  'h',  'i',  's',  'a',  'A',
		);
		return static::convertFormat($search, $replace, $timeFormat);
	}


	public function parseDate($value)
	{
		$pattern = static::proxyLiterals($this->dateFormat, $proxies, $literals);
		$pattern = static::escapePregMeta($pattern);

		// day
		$search = array('dd', 'd');
		$replace = array('d', '(?<d>\\d{1,2})');
		$pattern = str_replace($search, $replace, $pattern);

		// month
		$search = array('mm', 'm');
		$replace = array('m', '(?<m>\\d{1,2})');
		$pattern = str_replace($search, $replace, $pattern);

		// year
		$search = array('yy', 'y');
		$replace = array('y', '(?<y>\\d{2,4})');
		$pattern = str_replace($search, $replace, $pattern);

		// day / month name
		$pattern = preg_replace(array('/\bD\b/', '/DD/', '/\bM\b/', '/MM/'), '(?<$0>\S+)', $pattern);

		// retrieve literals
		$pattern = str_replace($proxies, $literals, $pattern);

		// parse
		if (!preg_match("#^$pattern#", $value, $m)) {
			throw new Nette\InvalidArgumentException($this->invalidMessage);
		}

		$year = isset($m['y']) ? (int) $m['y'] : 0;
		if ($year > 0 && $year < 100) {
			$year += 2000;
		}

		if (!isset($m['m'])) {
			if (isset($m['M'])) {
				$month = array_search($m['M'], static::$monthsShort);
				$month = $month !== FALSE ? $month +1 : 0;
			} elseif (isset($m['MM'])) {
				$month = array_search($m['MM'], static::$months);
				$month = $month !== FALSE ? $month +1 : 0;
			} else {
				$month = 0;
			}
		} else {
			$month = (int) $m['m'];
		}
		$day = isset($m['d']) ? (int) $m['d'] : 0;

		return array($year, $month, $day);
	}


	public function parseTime($value)
	{
		$pattern = static::proxyLiterals($this->timeFormat, $proxies, $literals);
		$pattern = static::escapePregMeta($pattern);

		// hour 24 format
		$search = array('HH', 'H');
		$replace = array('H', '(?<H>\\d{1,2})');
		$pattern = str_replace($search, $replace, $pattern);

		// hour 12 format
		$search = array('hh', 'h');
		$replace = array('h', '(?<h>\\d{1,2})');
		$pattern = str_replace($search, $replace, $pattern);

		// minute, second
		$pattern = str_replace(array('mm', 'ss'), array('(?<m>\\d{2})', '(?<s>\\d{2})'), $pattern);

		// AM / PM
		$pattern = str_replace(array('tt', 'TT'), array('(?<a>am|pm)', '(?<a>AM|PM)'), $pattern);

		// retrieve literals
		$pattern = str_replace($proxies, $literals, $pattern);

		// parse
		if (!preg_match("#$pattern$#", $value, $m)) {
			throw new Nette\InvalidArgumentException($this->invalidMessage);
		}

		// AM / PM
		$pm = isset($m['a']) ? strtolower($m['a']) === 'pm' : NULL;

		// calculate hour
		if (isset($m['h'])) {
			$hour = (int) $m['h'];
			if ($pm) {
				$hour += 12;
			}
		} elseif (isset($m['H'])) {
			$hour = (int) $m['H'];
		} else {
			$hour = 0;
		}

		$minute = isset($m['m']) ? (int) $m['m'] : 0;
		$second = isset($m['s']) ? (int) $m['s'] : 0;

		return array($hour, $minute, $second);
	}


	protected static function convertFormat(array $search, array $replace, $format)
	{
		// preserve literals
		preg_match_all("/'([^']+)'/", $format, $m);
		$literals = $escapes = $proxies = array();
		foreach ($m[0] as $i => $literal) {
			$literals[] = $literal;
			$literal = $m[1][$i];
			$escaped = '';
			for ($n = 0; $n < mb_strlen($literal); $n++) {
				$escaped .= '\\' . mb_substr($literal, $n, 1);
			}
			$escapes[] = $escaped;
			$proxies[] = "%$i%";
		}
		$format = str_replace($literals, $proxies, $format);

		// rewrite datetime characters to php datetime characters
		foreach ($search as & $expr) {
			$expr = "/\\b$expr\\b/";
		}
		$format = preg_replace($search, $replace, $format);
		$format = str_replace($proxies, $escapes, $format);

		// double single quote to one single quote
		$format = str_replace("''", "'", $format);

		return $format;
	}


	protected static function proxyLiterals($format, & $proxies, & $literals)
	{
		preg_match_all("/'([^']+)'/", $format, $m);
		$quotedLiterals = $literals = $proxies = array();
		foreach ($m[0] as $i => $literal) {
			$quotedLiterals[] = $literal;
			$literals[] = $m[1][$i];
			$proxies[] = "%$i%";
		}
		return str_replace($quotedLiterals, $proxies, $format);
	}


	public static function escapePregMeta($pattern)
	{
		// "#" is used as delimiter, so is escaped too
		$search = array('\\','#','$','^','*','(',')','{','}','[',']','|','?',);
		$replace = array();
		foreach ($search as $meta) {
			$replace[] = "\\$meta";
		}
		return str_replace($search, $replace, $pattern);
	}


	protected function parseDatetime($value)
	{
		list ($year, $month, $day) = $this->parseDate($value);
		if ($this->timeFormat) {
			list ($hour, $minute, $second) = $this->parseTime($value);
		} else {
			$hour = $minute = $second = 0;
		}

		$date = new Nette\Utils\DateTime;
		return $date->setDate($year, $month, $day)
			->setTime($hour, $minute, $second);
	}


	protected function getPhpDatetimeFormat()
	{
		if (empty($this->phpDatetimeFormat)) {
			$format = static::convertDateFormat($this->dateFormat);
			if ($this->timeFormat) {
				$timeFormat = static::convertTimeFormat($this->timeFormat);
				$format = "$format $timeFormat";
			}
			$this->phpDatetimeFormat = $format;
		}
		return $this->phpDatetimeFormat;
	}


	protected function translateNames($value)
	{
		foreach (self::$defaultMonths as $i => $month) {
			if (strpos($value, $month) !== FALSE) {
				$value = str_replace($month, static::$months[$i], $value);
				break;
			}
		}
		foreach (self::$defaultMonthsShort as $i => $month) {
			if (strpos($value, $month) !== FALSE) {
				$value = str_replace($month, static::$monthsShort[$i], $value);
				break;
			}
		}
		foreach (self::$defaultDays as $i => $month) {
			if (strpos($value, $month) !== FALSE) {
				$value = str_replace($month, static::$days[$i], $value);
				break;
			}
		}
		foreach (self::$defaultDaysShort as $i => $month) {
			if (strpos($value, $month) !== FALSE) {
				$value = str_replace($month, static::$daysShort[$i], $value);
				break;
			}
		}
		return $value;
	}
}
