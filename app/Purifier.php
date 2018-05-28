<?php

namespace App;

/**
 * Purifier basic class.
 *
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @copyright YetiForce Sp. z o.o
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Purifier
{
	/**
	 * Default charset.
	 *
	 * @var string
	 */
	public static $defaultCharset;

	/**
	 * Cache for purify instance.
	 *
	 * @var \HTMLPurifier|bool
	 */
	private static $purifyInstanceCache = false;

	/**
	 * Cache for Html purify instance.
	 *
	 * @var \HTMLPurifier|bool
	 */
	private static $purifyHtmlInstanceCache = false;

	/**
	 * Error collection class that enables HTML Purifier to report HTML problems back to the user.
	 *
	 * @var bool
	 */
	public static $collectErrors = false;

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
		if (!is_array($input)) {
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
			$config->set('Cache.SerializerPath', ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'vtlib');
			$config->set('HTML.Allowed', '');
			static::$purifyInstanceCache = new \HTMLPurifier($config);
		}
		if (static::$purifyInstanceCache) {
			// Composite type
			if (is_array($input)) {
				$value = [];
				foreach ($input as $k => $v) {
					$value[$k] = static::purify($v);
				}
			} elseif (is_string($input)) {
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
	public static function purifyHtml($input, $loop = true)
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
			if (static::$collectErrors) {
				if (!$config) {
					$config = static::getHtmlConfig();
				}
				echo static::$purifyHtmlInstanceCache->context->get('ErrorCollector')->getHTMLFormatted($config);
			}
			static::purifyHtmlEventAttributes($value);
			if ($loop) {
				$last = '';
				while ($last !== $value) {
					$last = $value;
					$value = static::purifyHtml($value, false);
				}
			}
			$value = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $value);
			Cache::save('purifyHtml', $cacheKey, $value, Cache::SHORT);
		}
		return $value;
	}

	/**
	 * To purify malicious html event attributes.
	 *
	 * @param string $value
	 */
	public static function purifyHtmlEventAttributes($value)
	{
		if (preg_match("#<([^><]+?)([^a-z_\-]on\w*|xmlns)(\s*=\s*[^><]*)([>]*)#i", $value) || preg_match("/\b(" . static::$htmlEventAttributes . ")\s*=/i", $value) || preg_match('@<[^/>][^>]+(expression\(|j\W*a\W*v\W*a|v\W*b\W*s\W*c\W*r|&#|/\*|\*/)[^>]*>@sim', $value)) {
			\App\Log::error('purifyHtmlEventAttributes: ' . $value, 'IllegalValue');
			throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $value, 406);
		}
	}

	/**
	 * Get html config.
	 *
	 * @return \HTMLPurifier_Config
	 */
	public static function getHtmlConfig()
	{
		$config = \HTMLPurifier_Config::createDefault();
		$config->set('Core.Encoding', static::$defaultCharset);
		$config->set('Cache.SerializerPermissions', 0775);
		$config->set('Cache.SerializerPath', ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'vtlib');
		$config->set('HTML.Doctype', 'HTML 4.01 Transitional');
		$config->set('CSS.AllowTricky', true);
		$config->set('CSS.Proprietary', true);
		$config->set('Core.RemoveInvalidImg', true);
		$config->set('HTML.SafeIframe', true);
		$config->set('HTML.SafeEmbed', true);
		$config->set('URI.SafeIframeRegexp', '%^(http:|https:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');
		$config->set('HTML.DefinitionRev', 1);
		$config->set('HTML.TargetBlank', true);
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
		}
		$uri = $config->getDefinition('URI');
		$uri->addFilter(new Extension\HTMLPurifier\Domain(), $config);
		if (static::$collectErrors) {
			$config->set('Core.CollectErrors', true);
		}
		return $config;
	}

	/**
	 * Function to return the valid SQl input.
	 *
	 * @param string $input
	 * @param bool   $skipEmpty Skip the check if string is empty
	 *
	 * @return string|bool
	 */
	public static function purifySql($input, $skipEmpty = true)
	{
		if ((empty($input) && $skipEmpty) || preg_match('/^[_a-zA-Z0-9.,]+$/', $input)) {
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
	 * @param string $type  Data type that is only acceptable
	 *
	 * @return mixed
	 */
	public static function purifyByType($input, $type)
	{
		if (is_array($input)) {
			$value = [];
			foreach ($input as $k => $v) {
				$value[$k] = static::purifyByType($v, $type);
			}
		} else {
			$value = false;
			switch ($type) {
				case 'Standard': // only word
				case 1:
					$value = preg_match('/^[_a-zA-Z]+$/', $input) ? $input : false;
					break;
				case 'Alnum': // word and int
				case 2:
					$value = preg_match('/^[[:alnum:]_]+$/', $input) ? $input : false;
					break;
				case 'DateInUserFormat': // date in user format
					list($y, $m, $d) = Fields\Date::explode($input, User::getCurrentUserModel()->getDetail('date_format'));
					if (checkdate($m, $d, $y) && is_numeric($y) && is_numeric($m) && is_numeric($d)) {
						$value = $input;
					}
					break;
				case 'DateRangeUserFormat': // date range user format
					$v = [];
					foreach (explode(',', $input) as $i) {
						list($y, $m, $d) = Fields\Date::explode($i, User::getCurrentUserModel()->getDetail('date_format'));
						if (checkdate((int) $m, (int) $d, (int) $y) && is_numeric($y) && is_numeric($m) && is_numeric($d)) {
							$v[] = \DateTimeField::convertToDBFormat($i);
						}
					}
					$value = $v;
					break;
				case 'Date': // date in base format yyyy-mm-dd
					list($y, $m, $d) = Fields\Date::explode($input);
					if (checkdate($m, $d, $y) && is_numeric($y) && is_numeric($m) && is_numeric($d)) {
						$value = $input;
					}
					break;
				case 'Bool':
					if (is_bool($input) || strcasecmp('true', (string) $input) === 0) {
						$value = $input;
					}
					break;
				case 'NumberInUserFormat': // number in user format
					$currentUser = User::getCurrentUserModel();
					$input = str_replace([$currentUser->getDetail('currency_grouping_separator'), $currentUser->getDetail('currency_decimal_separator'), ' '], ['', '.', ''], $input);
					if (is_numeric($input)) {
						$value = $input;
					}
					break;
				case 'Integer': // Integer
					if (($input = filter_var($input, FILTER_VALIDATE_INT)) !== false) {
						$value = $input;
					}
					break;
				case 'Color': // colors
					$value = preg_match('/^(#[0-9a-fA-F]{6})$/', $input) ? $input : false;
					break;
				case 'Year': // 2018 etc
					if (is_numeric($input) && (int) $input >= 0 && (int) $input <= 3000 && strlen((string) $input) === 4) {
						$value = (string) $input;
					}
					break;
				case 'Text':
				default:
					$value = self::purify($input);
					break;
			}
			if ($value === false) {
				\App\Log::error('purifyByType: ' . $input, 'IllegalValue');
				throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $input, 406);
			}
		}

		return $value;
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

Purifier::$defaultCharset = (string) \AppConfig::main('default_charset', 'UTF-8');
