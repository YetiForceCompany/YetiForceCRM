<?php
/**
 * Purifier file.
 *
 * @package App
 *
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @copyright YetiForce S.A.
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Purifier basic class.
 */
class Purifier
{
	/** @var string Purify type date in user format. */
	public const DATE_USER_FORMAT = 'DateInUserFormat';

	/** @var string Purify type integer. */
	public const INTEGER = 'Integer';

	/** @var string Purify type standard. */
	public const STANDARD = 'Standard';

	/** @var string Purify type sql. */
	public const SQL = 'Sql';

	/** @var string Purify type text. */
	public const TEXT = 'Text';

	/** @var string Purify type number. */
	public const NUMBER = 'Number';

	/** @var string Purify type html. */
	public const HTML = 'Html';

	/** @var string Purify type boolean. */
	public const BOOL = 'Bool';

	/** @var string Purify type url. */
	public const URL = 'Url';

	/** @var string Purify type Alnum. */
	public const ALNUM = 'Alnum';

	/** @var string Purify type Alnum 2 (A-Za-z0-9\/\+\-). */
	public const ALNUM2 = 'AlnumType2';

	/** @var string Purify type AlnumExtended. */
	public const ALNUM_EXTENDED = 'AlnumExtended';

	/** @var string Purify type Digits. */
	public const DIGITS = 'Digits';

	/** @var string Purify type HTML text parser */
	public const HTML_TEXT_PARSER = 'HtmlTextParser';

	/** @var string Purify type Path. */
	public const PATH = 'Path';

	/**
	 * Default charset.
	 *
	 * @var string
	 */
	public static $defaultCharset;

	/**
	 * Cache for purify instance.
	 *
	 * @var bool|\HTMLPurifier
	 */
	private static $purifyInstanceCache = false;

	/**
	 * Cache for Html purify instance.
	 *
	 * @var bool|\HTMLPurifier
	 */
	private static $purifyHtmlInstanceCache = false;

	/** @var bool|\HTMLPurifier Cache for Html template purify instance. */
	private static $purifyTextParserInstanceCache = false;

	/**
	 * Html events attributes.
	 *
	 * @var string
	 */
	private static $htmlEventAttributes = 'onerror|onblur|onchange|oncontextmenu|onfocus|oninput|oninvalid|onreset|onsearch|onselect|onsubmit|onkeydown|onkeypress|onkeyup|' .
	'onclick|ondblclick|ondrag|ondragend|ondragenter|ondragleave|ondragover|ondragstart|ondrop|onmousedown|onmousemove|onmouseout|onmouseover|onbeforepaste|onresizestart|onactivate|' .
	'onmouseup|onmousewheel|onscroll|onwheel|oncopy|oncut|onpaste|onload|onselectionchange|onabort|onselectstart|ondragdrop|onmouseleave|onmouseenter|onunload|onresize|onmessage|' .
	'onpropertychange|onfilterchange|onstart|onfinish|onbounce|onrowsinserted|onrowsdelete|onrowexit|onrowenter|ondatasetcomplete|ondatasetchanged|ondataavailable|oncellchange|' .
	'onbeforeupdate|onafterupdate|onerrorupdate|onhelp|onbeforeprint|onafterprint|oncontrolselect|onfocusout|onfocusin|ondeactivate|onbeforeeditfocus|onbeforedeactivate|onbeforeactivate|' .
	'onresizeend|onmovestart|onmoveend|onmove|onbeforecopy|onbeforecut|onbeforeunload|onhashchange|onoffline|ononline|onreadystatechange|onstop|onlosecapture';

	/**
	 * Remove unnecessary code list.
	 *
	 * @var string[]
	 */
	private static $removeUnnecessaryCode = [
		'href="javascript:window.history.back();"',
		'href="javascript:void(0);"',
	];

	/**
	 * Purify (Cleanup) malicious snippets of code from the input.
	 *
	 * @param string $input
	 * @param bool   $loop  Purify values in the loop
	 *
	 * @return string
	 */
	public static function purify($input, $loop = true)
	{
		if (empty($input)) {
			return $input;
		}
		$value = $input;
		if (!\is_array($input)) {
			$cacheKey = md5($input);
			if (Cache::has('purify', $cacheKey)) {
				return Cache::get('purify', $cacheKey);
			}
		}
		// Initialize the instance if it has not yet done
		if (!static::$purifyInstanceCache) {
			$config = \HTMLPurifier_Config::createDefault();
			$config->set('Core.Encoding', static::$defaultCharset);
			$config->set('Cache.SerializerPermissions', 0775);
			$config->set('Cache.SerializerPath', ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'cache' . \DIRECTORY_SEPARATOR . 'vtlib');
			$config->set('HTML.Allowed', '');
			static::$purifyInstanceCache = new \HTMLPurifier($config);
		}
		if (static::$purifyInstanceCache) {
			// Composite type
			if (\is_array($input)) {
				$value = [];
				foreach ($input as $k => $v) {
					$value[$k] = static::purify($v);
				}
			} elseif (\is_string($input)) {
				static::purifyHtmlEventAttributes($input);
				$value = static::$purifyInstanceCache->purify(static::decodeHtml($input));
				if ($loop) {
					$last = '';
					while ($last !== $value) {
						$last = $value;
						$value = static::purify($value, false);
					}
				}
				Cache::save('purify', $cacheKey, $value, Cache::SHORT);
			}
		}
		return $value;
	}

	/**
	 * Purify HTML (Cleanup) malicious snippets of code from the input.
	 *
	 * @param string $input
	 * @param bool   $loop  Purify values in the loop
	 *
	 * @return string
	 */
	public static function purifyHtml(string $input, $loop = true): string
	{
		if (empty($input)) {
			return $input;
		}
		$value = $input;
		$cacheKey = md5($input);
		if (Cache::has('purifyHtml', $cacheKey)) {
			return Cache::get('purifyHtml', $cacheKey);
		}
		// Initialize the instance if it has not yet done
		if (!static::$purifyHtmlInstanceCache) {
			$config = static::getHtmlConfig();
			static::$purifyHtmlInstanceCache = new \HTMLPurifier($config);
		}
		if (static::$purifyHtmlInstanceCache) {
			$value = static::$purifyHtmlInstanceCache->purify($input);
			$value = static::removeUnnecessaryCode($value);
			if ($loop) {
				$last = '';
				while ($last !== $value) {
					$last = $value;
					$value = static::purifyHtml($value, false);
				}
			}
			static::purifyHtmlEventAttributes(static::decodeHtml($value));
			$value = preg_replace("/(^[\r\n]*|[\r\n]+)[\\s\t]*[\r\n]+/", "\n", $value);
			Cache::save('purifyHtml', $cacheKey, $value, Cache::SHORT);
		}
		return $value;
	}

	/**
	 * Purify HTML (Cleanup) malicious snippets of code from text parser.
	 *
	 * @param string $input
	 * @param bool   $loop  Purify values in the loop
	 *
	 * @return string
	 */
	public static function purifyTextParser($input, $loop = true): string
	{
		if (empty($input)) {
			return $input;
		}
		$cacheKey = md5($input);
		if (Cache::has('purifyTextParser', $cacheKey)) {
			return Cache::get('purifyTextParser', $cacheKey);
		}
		if (!static::$purifyTextParserInstanceCache) {
			$config = static::getHtmlConfig(['directives' => ['HTML.AllowedCommentsRegexp' => '/^(\s+{% |{% )[\s\S]+( %}| %}\s+)$/u']]);
			static::$purifyTextParserInstanceCache = new \HTMLPurifier($config);
		}
		$value = static::$purifyTextParserInstanceCache->purify($input);
		$value = static::removeUnnecessaryCode($value);
		static::purifyHtmlEventAttributes($value);
		if ($loop) {
			$last = '';
			while ($last !== $value) {
				$last = $value;
				$value = static::purifyTextParser($value, false);
			}
		}
		$value = preg_replace("/(^[\r\n]*|[\r\n]+)[\\s\t]*[\r\n]+/", "\n", $value);
		Cache::save('purifyTextParser', $cacheKey, $value, Cache::SHORT);
		return $value;
	}

	/**
	 * To purify malicious html event attributes.
	 *
	 * @param string $value
	 */
	public static function purifyHtmlEventAttributes(string $value): void
	{
		if (preg_match('#(<[^><]+?[\x00-\x20"\'])([^a-z_\\-]on\\w*|xmlns)(\\s*=\\s*[^><]*)([><]*)#i', $value, $matches)) {
			\App\Log::error('purifyHtmlEventAttributes: ' . $value, 'IllegalValue');
			throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE|1|' . print_r($matches, true) . "||$value", 406);
		}
		if (preg_match('#<([^><]+?)(' . static::$htmlEventAttributes . ')(\\s*=\\s*[^><]*)([>]*)#i', $value, $matches)) {
			\App\Log::error('purifyHtmlEventAttributes: ' . $value, 'IllegalValue');
			throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE|2|' . print_r($matches, true) . "||$value", 406);
		}
		if (preg_match('#<([^><]+?)javascript:[\w\.]+\(([>]*)#i', $value, $matches)) {
			\App\Log::error('purifyHtmlEventAttributes: ' . $value, 'IllegalValue');
			throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE|3|' . print_r($matches, true) . "||$value", 406);
		}
	}

	/**
	 * Remove unnecessary code.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public static function removeUnnecessaryCode(string $value): string
	{
		foreach (self::$removeUnnecessaryCode as $code) {
			if (false !== stripos($value, $code)) {
				$value = str_ireplace($code, '', $value);
			}
		}
		return $value;
	}

	/**
	 * Get html config.
	 *
	 * @param array $options
	 *
	 * @return \HTMLPurifier_Config
	 */
	public static function getHtmlConfig(array $options = [])
	{
		$config = \HTMLPurifier_Config::createDefault();
		$config->set('Core.Encoding', static::$defaultCharset);
		$config->set('Cache.SerializerPermissions', 0775);
		$config->set('Cache.SerializerPath', ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'cache' . \DIRECTORY_SEPARATOR . 'vtlib');
		$config->set('HTML.Doctype', 'HTML 4.01 Transitional');
		$config->set('CSS.AllowTricky', true);
		$config->set('CSS.Proprietary', true);
		$config->set('CSS.Trusted', true);
		$config->set('Core.RemoveInvalidImg', true);
		$config->set('HTML.SafeIframe', true);
		$config->set('HTML.SafeEmbed', true);
		$config->set('URI.SafeIframeRegexp', '%^(http:|https:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');
		$config->set('HTML.DefinitionRev', 1);
		$config->set('HTML.TargetBlank', true);
		$config->set('Attr.EnableID', true);
		$config->set('CSS.MaxImgLength', null);
		$config->set('URI.AllowedSchemes', [
			'http' => true,
			'https' => true,
			'mailto' => true,
			'ftp' => true,
			'nntp' => true,
			'news' => true,
			'tel' => true,
			'data' => true,
		]);
		foreach ($options['directives'] ?? [] as $key => $value) {
			$config->set($key, $value);
		}
		if ($def = $config->getHTMLDefinition(true)) {
			$def->addElement('section', 'Block', 'Flow', 'Common');
			$def->addElement('nav', 'Block', 'Flow', 'Common');
			$def->addElement('article', 'Block', 'Flow', 'Common');
			$def->addElement('aside', 'Block', 'Flow', 'Common');
			$def->addElement('header', 'Block', 'Flow', 'Common');
			$def->addElement('footer', 'Block', 'Flow', 'Common');
			$def->addElement('address', 'Block', 'Flow', 'Common');
			$def->addElement('hgroup', 'Block', 'Required: h1 | h2 | h3 | h4 | h5 | h6', 'Common');
			$def->addElement('figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common');
			$def->addElement('figcaption', 'Inline', 'Flow', 'Common');
			$def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', [
				'src' => 'URI',
				'type' => 'Text',
				'width' => 'Length',
				'height' => 'Length',
				'poster' => 'URI',
				'preload' => 'Enum#auto,metadata,none',
				'controls' => 'Bool',
			]);
			$def->addElement('audio', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', [
				'src' => 'URI',
				'type' => 'Text',
				'preload' => 'Enum#auto,metadata,none',
				'controls' => 'Bool',
			]);
			$def->addElement('source', 'Block', 'Flow', 'Common', [
				'src' => 'URI',
				'type' => 'Text',
			]);
			$def->addElement('link', 'Block', 'Flow', 'Common', [
				'href' => 'URI',
				'type' => 'Text',
				'rel' => 'Text',
			]);
			$def->addElement('yetiforce', 'Inline', 'Inline', 'Common', [
				'type' => 'Text',
				'crm-id' => 'Length',
				'attachment-id' => 'Length',
			]);
			$def->addElement('s', 'Inline', 'Inline', 'Common');
			$def->addElement('var', 'Inline', 'Inline', 'Common');
			$def->addElement('sub', 'Inline', 'Inline', 'Common');
			$def->addElement('sup', 'Inline', 'Inline', 'Common');
			$def->addElement('mark', 'Inline', 'Inline', 'Common');
			$def->addElement('wbr', 'Inline', 'Empty', 'Core');
			$def->addElement('ins', 'Block', 'Flow', 'Common', ['cite' => 'URI', 'datetime' => 'CDATA']);
			$def->addElement('del', 'Block', 'Flow', 'Common', ['cite' => 'URI', 'datetime' => 'CDATA']);
			// TinyMCE
			$def->addAttribute('img', 'data-mce-src', 'Text');
			$def->addAttribute('img', 'data-mce-json', 'Text');
			// Others
			$def->addAttribute('iframe', 'allowfullscreen', 'Bool');
			$def->addAttribute('table', 'height', 'Text');
			$def->addAttribute('td', 'border', 'Text');
			$def->addAttribute('th', 'border', 'Text');
			$def->addAttribute('tr', 'width', 'Text');
			$def->addAttribute('tr', 'height', 'Text');
			$def->addAttribute('tr', 'border', 'Text');
			$def->addAttribute('a', 'data-id', 'Text');
			$def->addAttribute('a', 'data-module', 'Text');
		}
		if ($uriDef = $config->getURIDefinition()) {
			$uriDef->addFilter(new Extension\HTMLPurifier\Domain(), $config);
		}
		return $config;
	}

	/**
	 * Function to return the valid SQl input.
	 *
	 * @param string $input
	 * @param bool   $skipEmpty Skip the check if string is empty
	 *
	 * @return bool|string
	 */
	public static function purifySql($input, $skipEmpty = true)
	{
		if ((empty($input) && $skipEmpty) || Validator::sql($input)) {
			return $input;
		}
		\App\Log::error('purifySql: ' . $input, 'IllegalValue');
		throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $input, 406);
	}

	/**
	 * Purify by data type.
	 *
	 * Type list:
	 * Standard - only words
	 * 1 - only words
	 * Alnum - word and int
	 * 2 - word and int
	 *
	 * @param mixed  $input
	 * @param string $type    Data type that is only acceptable
	 * @param mixed  $convert
	 *
	 * @return mixed
	 */
	public static function purifyByType($input, $type, $convert = false)
	{
		if (\is_array($input)) {
			$value = [];
			foreach ($input as $k => $v) {
				$value[$k] = static::purifyByType($v, $type);
			}
		} else {
			$value = null;
			switch ($type) {
				case 'Standard': // only word
				case 1:
					$value = Validator::standard($input) ? $input : null;
					break;
				case 'Alnum': // word and int
				case 2:
					$value = Validator::alnum($input) ? $input : null;
					break;
				case 'AlnumExtended':
					$value = preg_match('/^[\sA-Za-z0-9\,\_\.\=\-]+$/', $input) ? $input : null;
					break;
				case 'AlnumType2':
					$value = preg_match('/^[\sA-Za-z0-9\/\+\-]+$/', $input) ? $input : null;
					break;
				case 'DateInUserFormat': // date in user format
					if (!$input) {
						return '';
					}
					$value = Validator::dateInUserFormat($input) ? ($convert ? Fields\Date::formatToDB($input) : $input) : null;
					break;
				case 'TimeInUserFormat':
					$value = Validator::timeInUserFormat($input) ? ($convert ? Fields\Time::formatToDB($input) : $input) : null;
					break;
				case 'DateRangeUserFormat': // date range user format
					$dateFormat = User::getCurrentUserModel()->getDetail('date_format');
					$v = [];
					foreach (explode(',', $input) as $i) {
						if (!Validator::dateInUserFormat($i)) {
							$v = [];
							break;
						}
						[$y, $m, $d] = Fields\Date::explode($i, $dateFormat);
						if (checkdate((int) $m, (int) $d, (int) $y) && is_numeric($y) && is_numeric($m) && is_numeric($d)) {
							$v[] = \DateTimeField::convertToDBFormat($i);
						}
					}
					if ($v) {
						$value = $v;
					}
					break;
				case 'DateTimeInIsoFormat': // date in base format yyyy-mm-dd
					$value = Validator::dateTimeInIsoFormat($input) ? date('Y-m-d H:i:s', strtotime($input)) : null;
					break;
				case 'Bool':
					$value = self::bool($input);
					break;
				case 'NumberInUserFormat': // number in user format
					$input = Fields\Double::formatToDb($rawInput = $input);
					if (is_numeric($input) && Fields\Double::formatToDisplay($input, false) === Fields\Double::truncateZeros($rawInput)) {
						$value = $input;
					}
					break;
				case 'Number':
					$dbFormat = Fields\Double::formatToDb($input);
					if (is_numeric($dbFormat) && Fields\Double::formatToDisplay($dbFormat, false) === Fields\Double::truncateZeros($input)) {
						$value = $input;
					}
					break;
				case 'Double':
					if (false !== ($input = filter_var($input, FILTER_VALIDATE_FLOAT))) {
						$value = $input;
					}
					break;
				case 'Phone':
					$value = preg_match('/^[\s0-9+\-()]+$/', $input) ? $input : null;
					break;
				case 'Email':
					if (!$input) {
						return '';
					}
					$value = Validator::email($input) ? $input : null;
					break;
				case 'Html':
					$value = self::purifyHtml($input);
					break;
				case 'Integer': // Integer
					if (false !== ($input = filter_var($input, FILTER_VALIDATE_INT))) {
						$value = $input;
					}
					break;
				case 'Digits': // Digits - eg. 000523
					if (false !== ($input = filter_var($input, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[0-9]+$/']]))) {
						$value = $input;
					}
					break;
				case 'Color': // colors
					$value = preg_match('/^(#[0-9a-fA-F]{6})$/', $input) ? $input : null;
					break;
				case 'Year': // 2018 etc
					if (is_numeric($input) && (int) $input >= 0 && (int) $input <= 3000 && 4 === \strlen((string) $input)) {
						$value = (string) $input;
					}
					break;
				case 'Version':
					$value = preg_match('/^[\.0-9]+$/', $input) ? $input : null;
					break;
				case self::PATH:
					$value = Validator::path($input) && Validator::path(static::purify($input)) ? $input : null;
					break;
				case 'Url':
					if (!$input) {
						return '';
					}
					$value = Validator::url($input) ? $input : null;
					break;
				case 'MailId':
					$value = preg_match('/^[\sA-Za-z0-9\<\>\_\[\.\]\=\-\+\@\$\!\#\%\&\'\*\+\/\?\^\_\`\{\|\}\~\-\"\:\(\)]+$/', $input) ? $input : null;
					break;
				case 'ClassName':
					$value = preg_match('/^[a-z\\\_]+$/i', $input) ? $input : null;
					break;
				case self::SQL:
					$value = $input && Validator::sql($input) ? $input : null;
					break;
				case self::HTML_TEXT_PARSER:
					$value = self::purifyTextParser($input);
					break;
				case 'Text':
					$value = self::purify($input);
					break;
				default:
					if (method_exists('App\Validator', $type)) {
						if (Validator::{$type}($input)) {
							$value = $input;
						}
					} else {
						$value = self::purify($input);
					}
					break;
			}
			if (null === $value) {
				\App\Log::error('purifyByType: ' . $input, 'IllegalValue');
				throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $input, 406);
			}
		}
		return $value;
	}

	/**
	 * Function to convert the given value to bool.
	 *
	 * @param int|string $value
	 *
	 * @return bool|null
	 */
	public static function bool($value)
	{
		return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
	}

	/**
	 * Function to convert the given string to html.
	 *
	 * @param string $string
	 * @param bool   $encode
	 *
	 * @return string
	 */
	public static function encodeHtml($string)
	{
		return htmlspecialchars($string, ENT_QUOTES, static::$defaultCharset);
	}

	/**
	 * Function to decode html.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function decodeHtml($string)
	{
		return html_entity_decode($string, ENT_QUOTES, static::$defaultCharset);
	}
}

Purifier::$defaultCharset = (string) \App\Config::main('default_charset', 'UTF-8');
