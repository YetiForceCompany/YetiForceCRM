<?php
namespace App;

/**
 * Purifier basic class
 * @package YetiForce.App
 * @license licenses/License.html
 * @copyright YetiForce Sp. z o.o.
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Purifier
{

	/**
	 * For optimization - default_charset can be either upper / lower case.
	 * @var bool 
	 */
	public static $UTF8;

	/**
	 * Default charset
	 * @var string 
	 */
	public static $defaultCharset;

	/**
	 * Cache for purify instance
	 * @var \HTMLPurifier|bool
	 */
	private static $purifyInstanceCache = false;

	/**
	 * Cache for Html purify instance
	 * @var \HTMLPurifier|bool
	 */
	private static $purifyHtmlInstanceCache = false;

	/**
	 * Purify (Cleanup) malicious snippets of code from the input
	 * @param string $input
	 * @param boolean $ignore Skip cleaning of the input
	 * @return string
	 */
	public static function purify($input, $ignore = false)
	{
		if (empty($input)) {
			return $input;
		}
		$value = $input;
		if (!is_array($input)) {
			$cacheKey = md5($input);
			if (Cache::has('purify', $cacheKey)) {
				$value = Cache::get('purify', $cacheKey);
				$ignore = true; //to escape cleaning up again
			}
		}
		if (!$ignore) {
			// Initialize the instance if it has not yet done
			if (!static::$purifyInstanceCache) {
				$config = \HTMLPurifier_Config::createDefault();
				$config->set('Core.Encoding', static::$defaultCharset);
				$config->set('Cache.SerializerPath', ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'vtlib');
				$config->set('HTML.Allowed', '');
				static::$purifyInstanceCache = new \HTMLPurifier($config);
			}
			if (static::$purifyInstanceCache) {
				// Composite type
				if (is_array($input)) {
					$value = [];
					foreach ($input as $k => $v) {
						$value[$k] = static::purify($v, $ignore);
					}
				} elseif (is_string($input)) {
					$value = static::$purifyInstanceCache->purify(static::decodeHtml($input));
					$value = static::purifyHtmlEventAttributes(static::decodeHtml($value));
					Cache::save('purify', $cacheKey, $value, Cache::SHORT);
				}
			}
		}
		return $value;
	}

	/**
	 * To purify malicious html event attributes
	 * @param string $value
	 * @return string
	 */
	public static function purifyHtmlEventAttributes($value)
	{
		return preg_replace("#<([^><]+?)([^a-z_\-]on\w*|xmlns)(\s*=\s*[^><]*)([>]*)#i", "<\\1\\4", $value);
	}

	/**
	 * Purify HTML (Cleanup) malicious snippets of code from the input
	 *
	 * @param string $input
	 * @param boolean $ignore Skip cleaning of the input
	 * @return string
	 */
	public static function purifyHtml($input, $ignore = false)
	{
		if (empty($input)) {
			return $input;
		}
		$value = $input;
		$cacheKey = md5($input);
		if (Cache::has('purifyHtml', $cacheKey)) {
			$value = Cache::get('purifyHtml', $cacheKey);
			$ignore = true; //to escape cleaning up again
		}
		if (!$ignore) {
			// Initialize the instance if it has not yet done
			if (!static::$purifyHtmlInstanceCache) {
				$allowed = [
					'img[src|alt|title|width|height|style|data-mce-src|data-mce-json|class]',
					'figure', 'figcaption',
					'video[src|type|width|height|poster|preload|controls|style|class]', 'source[src|type]',
					'audio[src|type|preload|controls|class]',
					'a[href|target|class]',
					'iframe[width|height|src|frameborder|allowfullscreen|class]',
					'strong', 'b', 'i', 'u', 'em', 'br', 'font',
					'h1[style|class]', 'h2[style|class]', 'h3[style|class]', 'h4[style|class]', 'h5[style|class]', 'h6[style|class]',
					'p[style|class]', 'div[style|class]', 'center', 'address[style]',
					'span[style|class]', 'pre[style]',
					'ul', 'ol', 'li',
					'table[width|height|border|style|class]', 'th[width|height|border|style|class]',
					'tr[width|height|border|style|class]', 'td[width|height|border|style|class]',
					'hr',
				];
				$config = \HTMLPurifier_Config::createDefault();
				$config->set('Core.Encoding', static::$defaultCharset);
				$config->set('Cache.SerializerPath', ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'vtlib');
				$config->set('HTML.Doctype', 'HTML 4.01 Transitional');
				$config->set('CSS.AllowTricky', true);
				$config->set('CSS.Proprietary', true);
				$config->set('HTML.SafeIframe', true);
				$config->set('HTML.SafeEmbed', true);
				$config->set('URI.SafeIframeRegexp', '%^(http:|https:)?//(www.youtube(?:-nocookie)?.com/embed/|player.vimeo.com/video/)%');
				$config->set('HTML.Allowed', implode(',', $allowed));
				$config->set('HTML.DefinitionRev', 1);
				if ($def = $config->getHTMLDefinition(true)) {
					// http://developers.whatwg.org/sections.html
					$def->addElement('section', 'Block', 'Flow', 'Common');
					$def->addElement('nav', 'Block', 'Flow', 'Common');
					$def->addElement('article', 'Block', 'Flow', 'Common');
					$def->addElement('aside', 'Block', 'Flow', 'Common');
					$def->addElement('header', 'Block', 'Flow', 'Common');
					$def->addElement('footer', 'Block', 'Flow', 'Common');
					// Content model actually excludes several tags, not modelled here
					$def->addElement('address', 'Block', 'Flow', 'Common');
					$def->addElement('hgroup', 'Block', 'Required: h1 | h2 | h3 | h4 | h5 | h6', 'Common');
					// http://developers.whatwg.org/grouping-content.html
					$def->addElement('figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common');
					$def->addElement('figcaption', 'Inline', 'Flow', 'Common');
					// http://developers.whatwg.org/the-video-element.html#the-video-element
					$def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
						'src' => 'URI',
						'type' => 'Text',
						'width' => 'Length',
						'height' => 'Length',
						'poster' => 'URI',
						'preload' => 'Enum#auto,metadata,none',
						'controls' => 'Bool',
					));
					$def->addElement('audio', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
						'src' => 'URI',
						'type' => 'Text',
						'preload' => 'Enum#auto,metadata,none',
						'controls' => 'Bool',
					));
					$def->addElement('source', 'Block', 'Flow', 'Common', array(
						'src' => 'URI',
						'type' => 'Text',
					));
					// http://developers.whatwg.org/text-level-semantics.html
					$def->addElement('s', 'Inline', 'Inline', 'Common');
					$def->addElement('var', 'Inline', 'Inline', 'Common');
					$def->addElement('sub', 'Inline', 'Inline', 'Common');
					$def->addElement('sup', 'Inline', 'Inline', 'Common');
					$def->addElement('mark', 'Inline', 'Inline', 'Common');
					$def->addElement('wbr', 'Inline', 'Empty', 'Core');
					// http://developers.whatwg.org/edits.html
					$def->addElement('ins', 'Block', 'Flow', 'Common', array('cite' => 'URI', 'datetime' => 'CDATA'));
					$def->addElement('del', 'Block', 'Flow', 'Common', array('cite' => 'URI', 'datetime' => 'CDATA'));
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
				static::$purifyHtmlInstanceCache = new \HTMLPurifier($config);
			}
			if (static::$purifyHtmlInstanceCache) {
				$value = static::$purifyHtmlInstanceCache->purify(static::decodeHtml($input));
				$value = static::purifyHtmlEventAttributes(static::decodeHtml($value));
				Cache::save('purifyHtml', $cacheKey, $value, Cache::SHORT);
			}
		}
		return $value;
	}

	/**
	 * Function to return the valid SQl input.
	 * @param string $string
	 * @param boolean $skipEmpty Skip the check if string is empty.
	 * @return string|boolean
	 */
	public static function purifySql($string, $skipEmpty = true)
	{
		if ((empty($string) && $skipEmpty) || preg_match("/^[_a-zA-Z0-9.,]+$/", $string)) {
			return $string;
		}
		return false;
	}

	/**
	 * Function to convert the given string to html
	 * @param string $string
	 * @param boolean $encode
	 * @return string
	 */
	public static function toHtml($string)
	{
		if (static::$UTF8) {
			$value = htmlentities($string, ENT_QUOTES, static::$defaultCharset);
		} else {
			$value = str_replace(['<', '>', '"'], ['&lt;', '&gt;', '&quot;'], $string);
		}
		return $value;
	}

	/**
	 * Function to decode html
	 * @param string $string
	 * @return string
	 */
	public static function decodeHtml($string)
	{
		if (static::$UTF8) {
			$value = html_entity_decode($string, ENT_QUOTES, static::$defaultCharset);
		} else {
			$value = str_replace(['&lt;', '&gt;', '&quot;'], ['<', '>', '"'], $string);
		}
		return $value;
	}
}

Purifier::$defaultCharset = \AppConfig::main('default_charset', 'UTF-8');
Purifier::$UTF8 = (strtoupper(Purifier::$defaultCharset) === 'UTF-8');
